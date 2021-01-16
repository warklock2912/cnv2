<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Social_Google extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getGUrl()
    {
        return Mage::helper('amajaxlogin/google')->getUrl();
    }
    
    public function getUrlParaml() 
    {
        $url = 'https://accounts.google.com/o/oauth2/auth?';

        $params = array(
            'redirect_uri'  => Mage::helper('amajaxlogin/google')->getUrl(),
            'response_type' => 'code',
            'client_id'     => Mage::helper('amajaxlogin/google')->getAppId(),
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
        );

        return  $url . urldecode(http_build_query($params));    
    }
    
    public function getGoogleConnectUrl()
    {
        $url = Mage::getUrl('amajaxlogin/google/login');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
}