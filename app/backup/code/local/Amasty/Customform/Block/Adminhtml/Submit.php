<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Submit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomform';
        $this->_controller = 'adminhtml_submit';
        $this->_headerText = $this->__('Submitted Form Data');

        parent::__construct();
    }
}