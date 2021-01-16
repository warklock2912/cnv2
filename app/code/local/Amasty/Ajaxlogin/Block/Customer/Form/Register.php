<?php
class Amasty_Ajaxlogin_Block_Customer_Form_Register extends Mage_Customer_Block_Form_Register{
  protected function _prepareLayout()
  {
    //$this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Customer Login'));
    return Mage_Core_Block_Template::_prepareLayout();
  }
}