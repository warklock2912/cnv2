<?php

class Tigren_Kpayment_Model_Order extends Tigren_Kpayment_Model_Kpayment
{

    const ENDPOINT = 'order';

    public function createKpaymentQrOrder($data)
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        try {
            $isPrivate = $kHelper->getIsPrivateQR();
            return $this->create($data, $this->getUrl('kpayment_qrcode'), $isPrivate);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    private function getUrl($id = '')
    {
        $this->_apiBaseUrl = $this->_helper->getConfigData($id,'api_base_url');
        return $this->_apiBaseUrl . self::ENDPOINT;
    }

}