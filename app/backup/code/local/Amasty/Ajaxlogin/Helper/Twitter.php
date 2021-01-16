<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Helper_Twitter extends Mage_Core_Helper_Abstract
{
    
    public function getUrl()
    {
        $url = Mage::getUrl('amajaxlogin/twitter/index');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getIframeUrl()
    {
        $url = Mage::getUrl('amajaxlogin/twitter/iframe');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    } 
    
    public function getTwitterHtml()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_twitter', 'amajaxlogin_twitter')
                             ->setTemplate('amasty/amajaxlogin/social/twitter.phtml');
        return $block->toHtml();
    }
    
    public function getBlockHtml()
    {
       $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_twitter', 'amajaxlogin_twitter')
                ->setTemplate('amasty/amajaxlogin/social/twitter.phtml');
       return $block->tohtml();
    }
    
    public function isEnable()
    {
        return Mage::getStoreConfig('amajaxlogin/twitter/enable');    
    }
    
    public function getAppId()
    {
        return Mage::getStoreConfig('amajaxlogin/twitter/app_id');    
    }
    
    public function getSecretId()
    {
        return Mage::getStoreConfig('amajaxlogin/twitter/app_secret');    
    }
    
}
