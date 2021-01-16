<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Social_Facebook extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amajaxlogin/social/facebook/iftame.phtml');
    }
    
    public function getFbUrl()
    {
        return Mage::helper('amajaxlogin/facebook')->getUrl();
    }
    
    public function getUrlParaml() 
    {
        $url = 'https://www.facebook.com/dialog/oauth?';
        $params = array(
            'client_id'     => Mage::helper('amajaxlogin/facebook')->getAppId(),
            'redirect_uri'  => Mage::helper('amajaxlogin/facebook')->getUrl(),
            'response_type' => 'code',
            'scope'         => 'email,user_birthday'
        );
        
        return  $url . urldecode(http_build_query($params));    
    }

    public function getFacebookLoginUrl()
    {
        return Mage::helper('amajaxlogin/facebook')->getUrl();
    }
    
}
