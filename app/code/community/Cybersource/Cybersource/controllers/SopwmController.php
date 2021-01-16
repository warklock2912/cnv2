<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_SopwmController extends Mage_Core_Controller_Front_Action
{
    /**
     * Cybersource request object
     * @var array
     */
    private $_cyberResponse = null;

    /**
     * used to hold orders based on the status of the payment
     * @var bool
     */
    private $_holdOrder = false;

    /**
     * 
     * Ajax action method for generating Sign key and other fields
     * 
     */
    public function loadSignedFieldsAction()
    {
        $result = array(
            'isValid' => false,
            'message' => Mage::helper('cybersourcesop')->__('Something went wrong. Try again later.')
        );

        if (!Mage::app()->getRequest()->isPost() || !$this->_validateFormKey() || $this->_expireAjax()) {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($result));
            return;
        }

        $token = $this->getRequest()->getPost('token');
        $tokenize = $this->getRequest()->getPost('tokenize', false);
        $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
        $requestBuilder = Mage::getModel('cybersourcesop/sopwm_requestBuilder');

        try {
            if ($paymentMethod == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc::CODE) {

                if ($this->isValidToken($token)) {
                    Mage::register('token', $token, true);
                }

                $formFields = $requestBuilder->getCcFields();

                //Collect totals & save quote
                $this->getOnepage()->getQuote()->collectTotals()->save();

                //Create order from quote
                // $service = Mage::getModel('sales/service_quote', $this->getOnepage()->getQuote());
                // $service->submitAll();
                $this->getOnepage()->saveOrder();
                //
                $csPaymentAction = Mage::helper('cybersourcesop')->getPaymentActionName($tokenize);
                $transactionType = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCyberPaymentAction($csPaymentAction);
                $formFields['transaction_type'] = $transactionType;
            } else {
                $formFields = $requestBuilder->getEcheckFields();
            }

            $formFields['signature'] = Mage::helper('cybersourcesop/security')->sign($formFields, $this->getSecretKey());

            $result['formFields'] = $formFields;
            $result['isValid'] = true;
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            Mage::helper('cybersourcesop')->log('failed to build form fields: ' . $e->getMessage(), true);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
	}
	
	/**
     * Validate ajax request 
     *
     * @return bool
     */
    private function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
//            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            return true;
        }        
        return false;
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    private function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Handle the response from Cybersource
     *
     */
    public function receiptAction()
    {
        $this->_cyberResponse = $this->getRequest()->getPost();

        Mage::helper('cybersourcesop')->log($this->_cyberResponse);
        $status = $this->_cyberResponse['reason_code'];
        Mage::getSingleton('checkout/session')->unsetData('ccCid');
        Mage::getSingleton('checkout/session')->unsetData('ccNumber');
        try {
            if (! Mage::helper('cybersourcesop/security')->validateResponse($this->getSecretKey(), $this->_cyberResponse)) {
                throw new Exception('CyberSource signature is invalid.');
            }

            if (! in_array($status, Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSuccessCodes())) {
                Mage::helper('cybersourcesop')->log('CyberSource error code: ' . $status, true);
                throw new Exception('Unable to complete your payment.');
            }

            // processing card transaction
            if ($this->_cyberResponse['req_payment_method'] == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::PAY_METHOD_CARD) {
                if (Mage::helper('cybersource_core')->getAvsActive()) {
                    $this->verifyAvs();
                }
                $this->verifyCvn();
                $this->verify3dSecureFullPass();
                $this->processToken();
            }

            // $this->getOnepage()->getQuote()->collectTotals();

            Mage::register('cyber_response', $this->_cyberResponse, true);
            $this->_captureAction();
            // $this->getOnepage()->saveOrder();
            Mage::unregister('cyber_response');

            if ($this->_holdOrder) {
                $this->holdOrder($this->getOnepage()->getCheckout()->getLastRealOrder());
            }

        } catch (Exception $e) {
            $this->_errorAction($e);
            return $this;
        }

        if ($status == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $reviewMessage = Mage::helper('cybersource_core')->getDmReviewMessage()
                ? Mage::helper('cybersource_core')->getDmReviewMessage()
                : "Your order is currently under review.";

            $this->getOnepage()->getCheckout()->addSuccess($reviewMessage);
        }

        $this->getOnepage()->getQuote()->setIsActive(0)->save();
        $this->_redirect('checkout/onepage/success');

        return $this;
    }

    /**
     * Retrieves the key
     * @return mixed
     */
    private function getSecretKey()
    {
        $sysConfig = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();
        if (Mage::helper('cybersourcesop')->isMobile()) {
            $secretKey = $sysConfig['mobile_merchant_secret_key'];
        } else {
            $secretKey = $sysConfig['secret_key'];
        }
        return $secretKey;
    }

    /**
     * @return $this
     */
    private function processToken()
    {
        if (! $customerId = $this->getCustomer()->getId()) {
            return $this;
        }

        if (! isset($this->_cyberResponse['payment_token'])) {
            return $this;
        }

        $tokenId = $this->_cyberResponse['payment_token'];

        $token = Mage::getModel('cybersourcesop/token')->load($tokenId,'token_id');
        if (! $token->getId()) {
            $token->setTokenId($tokenId)
                ->setCcNumber($this->_cyberResponse['req_card_number'])
                ->setCcExpiration($this->_cyberResponse['req_card_expiry_date'])
                ->setCustomerId($customerId)
                ->setCcType($this->_cyberResponse['req_card_type'])
                ->setMerchantRef($this->_cyberResponse['req_reference_number'])
                ->save();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function verifyAvs()
    {
        $action = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forceavs');

        $successCodes = explode(',',str_replace(' ','', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forceavs_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getAvsSuccessVals();

        if (! isset($this->_cyberResponse['auth_avs_code'])) {
            return $this;
        }

        if (in_array($this->_cyberResponse['auth_avs_code'], $successCodes)) {
            return $this;
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            throw new Exception(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getAVSErrorCode($this->_cyberResponse['auth_avs_code']));
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD) {
            $this->_holdOrder = true;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function verifyCvn()
    {
        $action = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forcecvn');

        $successCodes = explode(',',str_replace(' ','', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forcecvn_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCvnSuccessVals();

        if (! isset($this->_cyberResponse['auth_cv_result'])) {
            return $this;
        }

        if (in_array($this->_cyberResponse['auth_cv_result'], $successCodes)) {
            return $this;
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            throw new Exception(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCVNErrorCode($this->_cyberResponse['auth_cv_result']));
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD) {
            $this->_holdOrder = true;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    private function verify3dSecureFullPass()
    {
        if (! Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('force3dsecure')) {
            return $this;
        }

        if (! isset($this->_cyberResponse['payer_authentication_eci'])) {
            throw new Exception('3DSecure verification is required.');
        }

        if (! in_array($this->_cyberResponse['payer_authentication_eci'], Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::get3dSecureSuccessVals())) {
            throw new Exception('3DSecure verification failed.');
        }
    }


    protected function _captureAction(){
        $session = $this->getOnepage()->getCheckout();
        if (! $orderid = $this->_cyberResponse['req_reference_number']) {
            $orderid = $session->getLastRealOrderId();
        }

        if ($orderid) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
            $payment = $order->getPayment();
            $payment->setAmountAuthorized($order->getTotalDue());
            $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
            $payment->capture(null);

            $order->save();
        }
    }
    /**
     * Cybersource General Error Action
     *
     * Called when a request back from cybersource has the error decision
     * Tries to retrieve the inital quote, cancels the order and redirects back to checkout
     * Sets a more useful error message to the customer based on the cybersource response
     *
     * @param Exception|null $e
     * @return Cybersource_Cybersource_SopwmController
     */
    private function _errorAction(Exception $e = null)
    {
        $session = $this->getOnepage()->getCheckout();

        if (! $orderid = $this->_cyberResponse['req_reference_number']) {
            $orderid = $session->getLastRealOrderId();
        }

        if ($orderid) {
            //attemptes to cancel the order and restore the quote so customer can try again
            $this->_cancelOrderAndRestoreQuote($orderid);
        }

        if (! $e) {
            $errorCode = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getErrorCode($this->_cyberResponse['reason_code']);
            $message = Mage::helper('cybersourcesop')->__("There was an error submitting your payment. %s", $errorCode);
        } else {
            $message = $e->getMessage();
        }

        Mage::helper('cybersourcesop')->log($message, true);

        $session->addError($message);
        $session->unsLastRealOrderId();

        $this->_redirectUrl(Mage::getUrl('checkout-order-failure'));

        return $this;
    }

    /**
     * Cancel the order id and restore the quote to the users session
     *
     * @param mixed $orderidin
     * @return Cybersource_Cybersource_SopwmController
     */

    private function _cancelOrderAndRestoreQuote($orderidin)
    {
        $session = $this->getOnepage()->getCheckout();

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderidin);

        if ($order->getId()) {
            try {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation(Mage::helper('cybersourcesop')->__('Unable to complete payment.'))->save();
                }

                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                //Return quote
                if ($quote->getId()) {
                    $quote->setIsActive(1)->unsReservedOrderId()->save();
                    $session->replaceQuote($quote);
                }
                Mage::helper('cybersourcesop')->log('Retrieved quote succesfully from order: ' . $orderidin);
            } catch (Exception $e) {
                //set the error message
                Mage::helper('cybersourcesop')->log("Error restoring quote:" . $e->getMessage());
            }
        } else {
            //we have no information available so just log and display error
            Mage::helper('cybersourcesop')->log("Error restoring quote: last order id:". $orderidin);
        }

        return $this;
    }

    private function holdOrder($order)
    {
        if ($order->getId() && $order->canHold()) {
            $order->hold()->save();
        }
    }

    /**
     * Token action method
     */
    public function tokenAction()
    {
        if (! Mage::getSingleton('customer/session')->authenticate($this)) {
            return $this;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save default token action
     */
    public function saveDefaultTokenAction()
    {
        $session = Mage::getSingleton('core/session');

        if (! $checkboxes = $this->getRequest()->getParam('checkbox')) {
            $session->addError(Mage::helper('cybersourcesop')->__("Select the token to update."));
            $this->_redirect('cybersource/sopwm/token');

            return $this;
        }

        foreach ($checkboxes as $id => $state) {

            if ($state != 'on') {
                continue;
            }

            try {
                /** @var $token Cybersource_Cybersource_Model_SOPWebMobile_Token */
                $token = Mage::getModel('cybersourcesop/token')->load($id);
                if (!$token->getId() || $token->getCustomerId() != $this->getCustomer()->getId()) {
                    throw new Exception(Mage::helper('cybersourcesop')->__('You are not allowed to take this action.'));
                }

                $token->setAsDefault();

            } catch (Exception $e) {
                $session->addError(Mage::helper('cybersourcesop')->__("An error occurred while updating your default credit card token."));
                Mage::helper('cybersourcesop')->log('Token Save Default Error: ' . $e->getMessage());
                break;
            }
        }

        $session->addSuccess(Mage::helper('cybersourcesop')->__("Default credit card token updated successfully."));
        $this->_redirect('cybersource/sopwm/token');
    }

    /**
     * Deletes token action
     */
    public function deleteAction()
    {
        $session = Mage::getSingleton('core/session');
        $params = $this->_request->getParams();

        //Get Token.
        $token = Mage::getModel('cybersourcesop/token')->getTokenValue($params['token_id']);
        if (!$this->isValidToken($token->getTokenId())) {
            $session->addError(Mage::helper('cybersourcesop')->__('You are not allowed to take this action.'));
            $this->_redirect('cybersource/sopwm/token');

            return $this;
        }

        $tokenId = $token->getTokenId();
        $merchantRef = $token->getMerchantRef();
        $result = Mage::getModel('cybersourcesop/token')->createDeleteRequest($tokenId, $merchantRef);

        if ($result) {
            $session->addSuccess(Mage::helper('cybersourcesop')->__("Saved Card sucessfully deleted."));
        } else {
            $session->addError(Mage::helper('cybersourcesop')->__("There was an error deleting your Saved Card."));
        }
        $this->_redirect('cybersource/sopwm/token');
    }

    private function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    private function isValidToken($tokenId)
    {
        if (!$tokenId) {
            return false;
        }

        $tokenModel = Mage::getModel('cybersourcesop/token')->load($tokenId,'token_id');
        if (!$tokenModel->getId() || $tokenModel->getCustomerId() != $this->getCustomer()->getId()) {
            return false;
        }

        return true;
    }
}
