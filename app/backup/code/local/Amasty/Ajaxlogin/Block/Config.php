<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Config extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amajaxlogin/config.phtml');
    }
    
    public function getSendUrl()
    {
        $url = $this->getUrl('amajaxlogin/ajaxlogin/index');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
}