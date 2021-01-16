<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Customer_Form_Login extends Mage_Customer_Block_Form_Login
{
    protected function _prepareLayout()
    {
        //$this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Customer Login'));
        return Mage_Core_Block_Template::_prepareLayout();
    }
    
    public function getFacebookHtml(){
        return $this->getLayout()->createBlock('amajaxlogin/social_facebook', 'social_facebook')->toHtml();
    }
}