<?php

class Tigren_Kpayment_Block_Credit_Credit extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getKbankCreditInformation()
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        $publicKey = $kHelper->getPublicKey();
        $inlineJavascriptUrl = $kHelper->getInlineJavascriptUrlCredit();
        $quote = Mage::getModel('checkout/session')->getQuote();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $amount = $kHelper->amountFormat($currency, $quote->getData('grand_total'));

        return array($publicKey, $inlineJavascriptUrl, $currency, $amount);
    }
}