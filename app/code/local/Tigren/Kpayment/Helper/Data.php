<?php

class Tigren_Kpayment_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getConfigData($code, $field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        $path = 'payment/'.$code.'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

    public function getPublicKey()
    {
        return $this->getConfigData('kpayment', 'public_key');
    }

    public function getSecretKey()
    {
        return $this->getConfigData('kpayment', 'secret_key');
    }

    public function amountFormat($currency, $amount)
    {
        return number_format($amount, 2, '.', '');
    }

    public function logAPI($message, $code)
    {
        Mage::log($message, Zend_Log::DEBUG, 'kpayment_'. $code .'_debug_' . Mage::getModel('core/date')->gmtDate('Y-m-d') . '.log', true);
        return true;
    }

    public function getResource()
    {
         return Mage::getSingleton('core/resource');
    }

    public function writeAdapter()
    {
         return $this->getResource()->getConnection('core_write');
    }

    public function readAdapter()
    {
         return $this->getResource()->getConnection('core_read');
    }



    public function getMerchantIdCredit()
    {
        return $this->getConfigData('kpayment_credit', 'merchant_id');
    }

    public function getTerminalIdCredit()
    {
        return $this->getConfigData('kpayment_credit', 'terminal_id');
    }

    public function getInlineJavascriptUrlCredit()
    {
        return $this->getConfigData('kpayment_credit', 'inline_javascript_url');
    }

    public function getIsPrivateCredit()
    {
        return $this->getConfigData('kpayment_credit', 'is_private');
    }

    public function getOrderStatusNewCredit()
    {
        return $this->getConfigData('kpayment_credit', 'order_status');
    }

    public function getOrderStatusCancelCredit()
    {
        return $this->getConfigData('kpayment_credit', 'order_status_cancel');
    }

    public function getOrderStatusPaidCredit()
    {
        return $this->getConfigData('kpayment_credit', 'order_status_paid');
    }

    public function getCreateAutoInvoiceCredit()
    {
        return $this->getConfigData('kpayment_credit', 'create_auto_invoice');
    }

    public function getPeriodTimeCredit()
    {
        return $this->getConfigData('kpayment_credit', 'period_time');


    }public function getUrlCheckoutRedirectCredit()
    {
        return $this->getConfigData('kpayment_credit', 'url_checkout_redirect');
    }



    public function getMerchantIdQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'merchant_id');
    }

    public function getTerminalIdQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'terminal_id');
    }

    public function getUIJavascriptUrlQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'ui_javascript_url');
    }

    public function getIsPrivateQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'is_private');
    }

    public function getOrderStatusNewQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'order_status');
    }

    public function getOrderStatusCancelQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'order_status_cancel');
    }

    public function getOrderStatusPaidQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'order_status_paid');
    }

    public function getCreateAutoInvoiceQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'create_auto_invoice');
    }

    public function getPeriodTimeQR()
    {
        return $this->getConfigData('kpayment_qrcode', 'period_time');
    }

}
