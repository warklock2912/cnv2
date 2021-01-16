<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Attributes_Grid_Renderer_Search extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $checked = $row->getIsAttributesSearch() ? 'checked="checked"' : '';
        $html = '<input type="checkbox" name="is_attributes_search[' . $row->getAttributeCode() . ']" ' . $checked . '/>';
        return $html;
    }

}