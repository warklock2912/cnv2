<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSphinx_Block_Adminhtml_System_Config_Form_Field_Check extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->getLayout()->createBlock('core/template')
            ->setBlockId('check-availability-btn')
            ->setTemplate('mageworx/searchsuitesphinx/check-availability-btn.phtml')
            ->toHtml();

        return $html;
    }

}
