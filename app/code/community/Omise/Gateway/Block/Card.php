<?php

class Omise_Gateway_Block_Card extends Mage_Payment_Block_Form
{

    public function getCustomerApiId(){
        $customerApiId = '';
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if($customer->getCustomerApiId()){
            $customerApiId = $customer->getCustomerApiId();
        }
        return $customerApiId;
    }

    public function getCardList($id){
        $cardList = Mage::helper('ruffle/omise')->getListCardCustomerOmise($id);
        return $cardList;
    }

    public function getDefaultCard($id){
        $customerInfo = Mage::helper('ruffle/omise')->getCustomerOmise($id)->getValue();
        $defaultCard = $customerInfo['default_card'];
        return $defaultCard;
    }

    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

}
