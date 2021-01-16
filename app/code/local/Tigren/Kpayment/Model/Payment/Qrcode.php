<?php

class Tigren_Kpayment_Model_Payment_Qrcode extends Mage_Payment_Model_Method_Abstract {

    protected $_code  = 'kpayment_qrcode';

    protected $_formBlockType = 'kpayment/form_qrcode';

    protected $_infoBlockType = 'kpayment/info_qrcode';

//    protected $_isGateway       = true;
//
//    protected $_canAuthorize    = true;
//
//    protected $_canCapture      = true;
//
//    protected $_canOrder        = true;

    protected $_isInitializeNeeded = true;
    protected $_canReviewPayment   = true;
//    public function isAutoCapture()
//    {
//        return $this->getConfigData('payment_action') === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
//    }
//
//    public function is3Dsecure()
//    {
//        return $this->getConfigData('secure_support');
//    }
//
//    public function capture(Varien_Object $payment, $amount)
//    {
//        return $this;
//    }


    public function initialize($paymentAction, $stateObject)
    {
        $initialize = parent::initialize($paymentAction, $stateObject);
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        $currency = $order->getStoreCurrencyCode();
        $amount = $kHelper->amountFormat($order->getStoreCurrencyCode(), $order->getGrandTotal());

        $additionalData = array(
            'mid' => $kHelper->getMerchantIdQR(),
            'tid' => $kHelper->getTerminalIdQR()
        );

        $params = array(
            'amount' => $amount,
            'currency' => $currency,
            'description' => 'test charge',
            'source_type' => 'qr',
            'reference_order' => $order->getIncrementId(),
            'additional_data' => $additionalData
        );

        /** @var Tigren_Kpayment_Model_Order $orderCreate **/
        $orderCreate = Mage::getModel('kpayment/order');

        $createOrder = $orderCreate->createKpaymentQrOrder($params);
        $createOrder = json_decode($createOrder);
        $payment->setAdditionalInformation('kbank_order_id', $createOrder->id);
        Mage::log($createOrder, null, 'hihi.log', true);
        return $initialize;
    }

    public function assignData($data)
    {
//
//        if ($data->getCustomFieldOne())
//        {
//            $info->setCustomFieldOne($data->getCustomFieldOne());
//        }
//
//        if ($data->getCustomFieldTwo())
//        {
//            $info->setCustomFieldTwo($data->getCustomFieldTwo());
//        }

        return $this;
    }

    public function validate()
    {
        parent::validate();
//        $info = $this->getInfoInstance();
//
//        if (!$info->getCustomFieldOne())
//        {
//            $errorCode = 'invalid_data';
//            $errorMsg = $this->_getHelper()->__("CustomFieldOne is a required field.\n");
//        }
//
//        if (!$info->getCustomFieldTwo())
//        {
//            $errorCode = 'invalid_data';
//            $errorMsg .= $this->_getHelper()->__('CustomFieldTwo is a required field.');
//        }
//
//        if ($errorMsg)
//        {
//            Mage::throwException($errorMsg);
//        }

        return $this;
    }

    public function isAvailable($quote = null)
    {
        return true;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('kpayment/qrcode_redirect/index', array('_secure' => true));
    }
}