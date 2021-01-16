<?php

class Tigren_Kpayment_Block_Qrcode_Redirect extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getKbankQrInformation()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getModel('checkout/session');
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        $publicKey = $kHelper->getPublicKey();
        $uiJavascriptUrl = $kHelper->getUIJavascriptUrlQR();
        $order = $checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $currency = $order->getStoreCurrencyCode();
        $amount = $kHelper->amountFormat($order->getStoreCurrencyCode(), $order->getGrandTotal());

        // Advance Payment Options
        $paymentAdditionalInformation = $payment->getAdditionalInformation();
        Mage::log($paymentAdditionalInformation, null, 'hichic.log', true);
        $kbankOrderId = $paymentAdditionalInformation['kbank_order_id'] ?: '';

        return array($publicKey, $uiJavascriptUrl, $kbankOrderId, $currency, $amount);
    }
}