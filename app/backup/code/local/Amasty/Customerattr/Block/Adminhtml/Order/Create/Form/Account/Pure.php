<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Orderattr/active'))
{
    class Amasty_Customerattr_Block_Adminhtml_Order_Create_Form_Account_Pure extends  Amasty_Orderattr_Block_Adminhtml_Order_Create_Form_Account{}
}
else
{
    class Amasty_Customerattr_Block_Adminhtml_Order_Create_Form_Account_Pure extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Account {}
}