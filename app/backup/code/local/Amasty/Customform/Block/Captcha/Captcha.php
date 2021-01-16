<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Captcha_Captcha extends Mage_Captcha_Block_Captcha_Zend
{
    protected $_template = 'amcustomform/captcha/zend.phtml';
    /**
     * Returns captcha model
     *
     * @return Mage_Captcha_Model_Abstract
     */
    public function getCaptchaModel()
    {
        return Mage::helper('amcustomform/captcha')->getCaptcha($this->getFormId());
    }

    public function getRefreshUrl()
    {
        return Mage::getUrl(
            //Mage::app()->getStore()->isAdmin() ? 'adminhtml/refresh/refresh' : 'captcha/refresh',
            'amcustomform/refresh',
            array('_secure' => Mage::app()->getStore()->isCurrentlySecure())
        );
    }
}