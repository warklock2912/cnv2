<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Helper_Facebook extends Mage_Core_Helper_Abstract
{
    
    public function getUrl()
    {
        $url = Mage::getUrl('amajaxlogin/facebook/index');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    } 
    
    public function getIframeUrl()
    {
        $url = Mage::getUrl('amajaxlogin/facebook/iframe');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getBlockHtml()
    {
       $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_facebook', 'amajaxlogin_facebook')
                             ->setTemplate('amasty/amajaxlogin/social/facebook.phtml');
       return $block->tohtml();
    }
    
    public function isEnable()
    {
        return Mage::getStoreConfig('amajaxlogin/facebook/enable');    
    }
    
    public function getAppId()
    {
        return Mage::getStoreConfig('amajaxlogin/facebook/app_id');    
    }
    
    public function getSecretId()
    {
        return Mage::getStoreConfig('amajaxlogin/facebook/app_secret');    
    }
    
   
    
}
