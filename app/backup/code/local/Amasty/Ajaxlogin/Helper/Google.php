<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Helper_Google extends Mage_Core_Helper_Abstract
{
    
    public function getUrl()
    {
        $url = Mage::getUrl('amajaxlogin/google/index');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getIframeUrl()
    {
        $url = Mage::getUrl('amajaxlogin/google/iframe');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getGoogleHtml()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_google', 'amajaxlogin_google')
                             ->setTemplate('amasty/amajaxlogin/social/google.phtml');
        return $block->toHtml();
    }
 
     public function getBlockHtml()
    {
       $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_google', 'amajaxlogin_social_google')
                             ->setTemplate('amasty/amajaxlogin/social/google.phtml');
       return $block->tohtml();
    }
    
    public function isEnable()
    {
        return Mage::getStoreConfig('amajaxlogin/google/enable');    
    }
    
    public function getAppId()
    {
        return Mage::getStoreConfig('amajaxlogin/google/app_id');    
    }
    
    public function getSecretId()
    {
        return Mage::getStoreConfig('amajaxlogin/google/app_secret');    
    }
    
}
