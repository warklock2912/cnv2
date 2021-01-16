<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Model_Captcha extends Mage_Captcha_Model_Zend
{

    public function getBlockName()
    {
        return 'amcustomform/captcha_captcha';
    }

    protected function _getHelper()
    {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('amcustomform/captcha');
        }
        return $this->_helper;
    }
    public function isRequired($login = null)
    {
        if (!$this->_isEnabled()) {
            return false;
        }

        return ($this->_isShowAlways() || $this->_isOverLimitAttempts($login)
            || $this->getSession()->getData($this->_getFormIdKey('show_captcha'))
        );
    }

    protected function _getFormIdKey($key)
    {
        return $this->_formId . '_' . $key;
    }
}