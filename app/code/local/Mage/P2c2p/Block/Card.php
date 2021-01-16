<?php

class Mage_P2c2p_Block_Card extends Mage_Payment_Block_Form
{

    public function getCustomerId(){
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        return $customerId;
    }

    public function getCardList($id){
        $cardList = Mage::helper('p2c2p')->getListCardCustomer($id);
        return $cardList;
    }

    public function getDefaultCard($id){
        $defaultCard =Mage::helper('p2c2p')->getDefaultCardCustomer($id);
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
