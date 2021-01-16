<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc extends Mage_Payment_Model_Method_Cc
{
    const CODE = 'cybersourcesop';
    protected $_canOrder = true;
    /**
     * Payment method code
     * @access protected
     * @var string
     */
	protected $_code = self::CODE;
    /**
     * @access protected
     * @var string
     */
	protected $_formBlockType = 'cybersourcesop/form_pay';

    /**
     * @access protected
     * @var string
     */
	protected $_infoBlockType = 'cybersourcesop/info_pay';

    /**
     *
     * @access protected
     * @var bool
     */
    protected $_isGateway = true;

    /**
     *
     * @access protected
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Used to check if multi-shipping is enabled
     * @access protected
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * Used to authorize the payment
     * @access protected
     * @var bool
     */
	protected $_canAuthorize = true;

    /**
     * Used to capture the payment
     * @access protected
     * @var bool
     */
	protected $_canCapture = true;

    /**
     * Used during refund
     * @access protected
     * @var bool
     */
	protected $_canRefund = true;

    /**
     * Used to refund partial capture
     * @access protected
     * @var bool
     */
	protected $_canRefundInvoicePartial = true;

    /**
     * Used to void transaction
     * @access protected
     * @var bool
     */
	protected $_canVoid = true;

    /**
     * Used to invoice the order
     * @access protected
     * @var bool
     */
	protected $_canCancelInvoice = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;


    protected $_isInitializeNeeded      = true;
	/**
	 * Redirect to the post page
	 *
	 * @return string|boolean
	 */
	public function getOrderPlaceRedirectUrl()
	{
	    return false;
	}

    /**
     * Validates the payment method/card
     * @return $this
     */
    public function validate()
	{
		return $this;
	}

	/**
	 * Check if the request is from the admin area
	 *
	 * @return boolean
	 */
	private function _isAdmin()
	{
		if (Mage::app()->getStore()->isAdmin()) {
			return true;
		}

		if (Mage::getDesign()->getArea() == 'adminhtml') {
			return true;
		}

		return false;
	}


    public function initialize($paymentAction, $stateObject)
    {
        $paymentInfo = $this->getInfoInstance();
        $order = $paymentInfo->getOrder();
        $payment = $order->getPayment();
        $payment->_order($order->getBaseTotalDue());
    }
	/**
	 * Overridden for admin area SOAP calls
	 *
	 * @see Mage_Payment_Model_Method_Abstract::authorize()
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     */
    public function authorize(Varien_Object $payment, $amount)
	{
		if ($this->_isAdmin()) {
			//call soap API
			Mage::getModel('cybersourcesop/soapapi_auth')->process($payment, $amount);
			return $this;
		}

		if (! $response = Mage::registry('cyber_response')) {
            return $this;
        }

        $this->processCyberResponse($payment, $response);

		$payment->setAdditionalInformation('authTransactionID', $response['transaction_id']);
        $payment->setAdditionalInformation('authRequestID', $response['transaction_id']);
        $payment->setAdditionalInformation('authRequestToken', $response['request_token']);

		return $this;
	}

    /**
     * Overridden for admin area SOAP calls
     *
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
	{
		if ($this->_isAdmin()) {
			//call soap API
			Mage::getModel('cybersourcesop/soapapi_capture')->process($payment, $amount);
			return $this;
		}

		if (! $response = Mage::registry('cyber_response')) {
		    throw new Exception('CyberSource response is empty.');
        }

        $this->processCyberResponse($payment, $response);

		$payment->setAdditionalInformation('captureTransactionID', $response['transaction_id']);
        $payment->setAdditionalInformation('captureRequestID', $response['transaction_id']);
        $payment->setAdditionalInformation('captureRequestToken', $response['request_token']);

		return $this;
	}

    /**
     * Process the refunds
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
	{
		Mage::getModel('cybersourcesop/soapapi_refund')->process($payment, $amount);
	 	return $this;
	}

    /**
     * Void payment abstract method
     *
     * @param Varien_Object $payment
     *
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc
     */
	public function void(Varien_Object $payment)
    {
		Mage::getModel('cybersourcesop/soapapi_void')->process($payment);
		return $this;
	}

    public function canVoid(Varien_Object $payment)
    {
        $paymentInfo = $payment->getAdditionalInformation();
        return !isset($paymentInfo['captureTransactionID'])
            && !isset($paymentInfo['reversalRequestID']);
    }

    public function getConfigPaymentAction()
    {
        $paymentAction = Mage::helper('cybersourcesop')->getPaymentActionName(false);
        $csPaymentAction = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCyberPaymentAction($paymentAction);
        if (
            $csPaymentAction == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_AUTHORIZE
            || $csPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE
        ) {
            return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
        }

        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * @param $payment
     * @param $response
     * @return $this
     */
    private function processCyberResponse(&$payment, $response)
    {
        $payment
            ->setIsTransactionClosed(0)
            ->setCcApproval(true)
            ->setLastTransId($response['transaction_id'])
            ->setCcTransId($response['transaction_id'])
            ->setTransactionId($response['transaction_id'])
            ->setCcLast4(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::retrieveCardNum($response['req_card_number']))
            ->setCcType(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCyberCCs($response['req_card_type']));

        if ($response['reason_code'] == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $payment->setIsTransactionPending(1);
            $payment->setIsFraudDetected(1);
        }

        if (isset($response['payer_authentication_xid'])) {
            $payment->setAdditionalInformation('cybersourcesop_auth_xid', $response['payer_authentication_xid']);
        }

        if (isset($response['payer_authentication_proof_xml'])) {
            $payment->setAdditionalInformation('cybersourcesop_proof_xml', $response['payer_authentication_proof_xml']);
        }

        if (isset($response['payer_authentication_eci'])) {
            $payment->setAdditionalInformation('cybersourcesop_eci', $response['payer_authentication_eci']);
        }

        if (isset($response['payer_authentication_cavv'])) {
            $payment->setAdditionalInformation('cybersourcesop_cavv', $response['payer_authentication_cavv']);
        }

        if (isset($response['auth_avs_code'])) {
            $payment->setAdditionalInformation('cc_avs_status', $response['auth_avs_code']);
        }

        if (isset($response['auth_cv_result'])) {
            $payment->setAdditionalInformation('cc_cid_status', $response['auth_cv_result']);
        }

        return $this;
    }
}
