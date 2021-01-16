<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Renderer_Boolean
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp = Mage::helper('amcustomerattr');
        return $row->getData($this->getColumn()->getIndex()) ? $hlp->__('Yes')
            : $hlp->__('No');
    }
}