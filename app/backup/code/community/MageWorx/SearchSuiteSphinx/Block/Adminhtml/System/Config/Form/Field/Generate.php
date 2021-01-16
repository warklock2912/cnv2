<?php
/**
 * MageWorx
 * Search Suite Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SearchSuiteSphinx_Block_Adminhtml_System_Config_Form_Field_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->getLayout()->createBlock('core/template')
            ->setBlockId('generate-config-btn')
            ->setTemplate('mageworx/searchsuitesphinx/generate-config-btn.phtml')
            ->toHtml();

        return $html;
    }

}
