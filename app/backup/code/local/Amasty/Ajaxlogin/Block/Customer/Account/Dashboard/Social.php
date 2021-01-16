<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Customer_Account_Dashboard_Social extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate("amasty/amajaxlogin/customer/account/dashboard/social.phtml");
        parent::_construct();
    }
}