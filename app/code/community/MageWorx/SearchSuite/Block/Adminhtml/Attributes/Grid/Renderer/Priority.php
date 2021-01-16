<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Attributes_Grid_Renderer_Priority extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $value = 0;
        if ($row->getIsSearchable()) {
            $value = $row->getQuickSearchPriority();
        }
        $checked = '';
        $rowId = str_replace('quick_search_priority_', '', $this->getColumn()->getId());
        if ($rowId == $value) {
            $checked = 'checked="checked"';
        }
        $html = '<input type="radio" name="quick_search_priority[' . $row->getAttributeCode() . ']" value="' . $rowId . '" ' . $checked . '/>';
        return $html;
    }

}
