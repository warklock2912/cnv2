<?php

class Tigren_Kpayment_Credit_CallbackController extends Mage_Core_Controller_Front_Action
{

    public function indexAction() {
        $requestParams = $this->getRequest()->getParams();
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');

        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getModel('checkout/session');
        /** @var Tigren_Kpayment_Model_Credit_Reference $reference **/

        $redirectSuccessUrl = $kHelper->getUrlCheckoutRedirectCredit() . '/success';
        $redirectFailureUrl = $kHelper->getUrlCheckoutRedirectCredit() . '/failure';

        if (!empty($requestParams['objectId'])) {
            $objectId = $requestParams['objectId'];
            $response = $this->callInquiryAPI($objectId);
            if($response) {
                $order = Mage::getModel("sales/order")->loadByIncrementId($response['reference_order']);
            }
        }
        else {
            $order = $checkoutSession->getLastRealOrder();
            $objectId = $order->getIncrementId();
        }

        $store = $order->getStore();

        if (!$order || !$order->getId()) {
            return $this->_redirectUrl($redirectFailureUrl);
        }

        $payment = $order->getPayment();

        $paymentMethod = $payment->getMethod();
        if ($paymentMethod == 'kpayment_credit') {

            $response = $this->callInquiryAPI($objectId);

            $responseMessage = "- **** Reviced response from Kpayment KpaymentCode Callback<br>";

            if ($response && is_array($response)) {
                foreach ($response as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $childKey => $childValue) {
                            $responseMessage .= $key . '[' . $childKey . ']' . ' = ' . $childValue . '<br>';
                        }
                    } else {
                        $responseMessage .= $key . ' = ' . $value . '<br>';
                    }
                }
            } else {
                foreach ($requestParams as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $childKey => $childValue) {
                            $responseMessage .= $key . '[' . $childKey . ']' . ' = ' . $childValue . '<br>';
                        }
                    } else {
                        $responseMessage .= $key . ' = ' . $value . '<br>';
                    }
                }
            }

            if (!empty($response['status']) && $response['status'] === 'success') {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                $order->addStatusToHistory($kHelper->getOrderStatusPaidCredit(), $responseMessage);
                $order->save();
                if ($kHelper->getCreateAutoInvoiceCredit()) {
                    if ($order->canInvoice()) {
                        $invoice = $order->prepareInvoice();
                        $invoice->register();
                        $payment = $order->getPayment();
                        $payment->setCreatedInvoice($invoice)
                            ->setIsTransactionClosed(false)
                            ->setIsTransactionPending(true)
                            ->addTransaction(
                                Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                                $invoice,
                                false,
                                $this->__('Authorizing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                            );
                        $order->addRelatedObject($invoice);
                    }
                }

                $this->_updateInquiryStatus($response['id'], $order->getIncrementId(), $response['status']);

                return $this->_redirectUrl($redirectSuccessUrl);
            }
            elseif(!empty($response['status']) && $response['status'] === 'pending') {
                $history = $order->addStatusHistoryComment($responseMessage);
                $history->setIsVisibleOnFront(false);
                $history->setIsCustomerNotified(false);
                $history->save();

                return $this->_redirectUrl($redirectSuccessUrl);
            }
            else {
                $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                $order->setState($state)
                    ->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $responseMessage)
                    ->save();

                return $this->_redirectUrl($redirectSuccessUrl);

            }
        }
    }

    private function getUrl($id = '')
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        return $kHelper->getConfigData($id,'api_base_url');
    }

    protected function _updateInquiryStatus($chargeId, $orderIncrement, $inquiryStatus = 'default')
    {
        try{
            /** @var Tigren_Kpayment_Helper_Data $kHelper **/
            $kHelper = Mage::helper('kpayment');

            $write = $kHelper->writeAdapter();

            $query = "UPDATE kpayment_payment_reference SET inquiry_status = :inquiryStatus, inquiry_date = NOW() WHERE charge_id = :chargeId AND order_increment = :orderIncrement;";
            $write->query($query, array(
                'chargeId' => $chargeId,
                'orderIncrement' => $orderIncrement,
                'inquiryStatus' => $inquiryStatus
            ));
        }
        catch(Exception $e){
            return array(
                'code' => '500',
                'message' => $e->getMessage()
            );
        }
        return array(
            'code' => '200',
            'message' => 'success'
        );
    }

    protected function callInquiryAPI($objectId) {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');

        $headerPrivate = array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $kHelper->getSecretKey()
        );

        $url = $this->getUrl('kpayment_credit') . '/charge/' . $objectId;
        $kHelper->logAPI('[CALLBACK REQUEST Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI($url, 'credit');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerPrivate);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        $kHelper->logAPI('[CALLBACK RESPONSE Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI(array($response), 'credit');
        $kHelper->logAPI('===== END =====', 'credit');

        return $response;
    }
}