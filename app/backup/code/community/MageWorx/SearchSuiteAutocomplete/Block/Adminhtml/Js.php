<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Block_Adminhtml_Js extends Mage_Adminhtml_Block_Template {

    public function _construct() {
        parent::_construct();
        $this->setTemplate('mageworx/searchsuiteautocomplete/js.phtml');
    }

    public function getTranslate() {
        $helper = Mage::helper('mageworx_searchsuiteautocomplete');
        return array(
            'color' => $helper->__('Color'),
            'bgcolor' => $helper->__('Background Color'),
            'font' => $helper->__('Font'),
            'fontsize' => $helper->__('Font Size'),
            'apply' => $helper->__('Apply'),
            'apply-comment' => $helper->__('Please save config in order to apply the changes.'),
            'cancel' => $helper->__('Cancel'),
            'clear' => $helper->__('Clear'),
            'italic' => $helper->__('Italic'),
            'bold' => $helper->__('Bold'),
            'search-header' => $helper->__('The Block Header'),
            'search-container-suggest' => $helper->__('Suggested Search Results'),
            's_suggest' => $helper->__('Suggested Search Result'),
            'search-suggest' => $helper->__('Text Of Suggested Search '),
            's_item' => $helper->__('Search Result'),
            's_icon' => $helper->__('Preview'),
            's_item_name' => $helper->__('Search Result Header'),
            's_name' => $helper->__('Name Of Search Result'),
            'ratings' => $helper->__('Ratings'),
            'rating-box' => $helper->__('Rating Box'),
            's_sku' => $helper->__('SKU'),
            's_description' => $helper->__('Description Of Search Result'),
            'price-box' => $helper->__('Price Box'),
            'price' => $helper->__('Price'),
            's_details' => $helper->__('Information About The Search Result'),
            's_category' => $helper->__('Category Search Result'),
            'resultbox-b' => $helper->__('Block After Search Results'),
            'search-more' => $helper->__('Block Search More'),
            'search-container' => $helper->__('Search Results'),
        );
    }

    public function getFont() {
        return array(
            'Arial' => 'Arial, serif',
            'Calibri' => 'calibri, serif',
            'Comic Sans' => "'Comic Sans MS', 'comic sans ms', 'comic sans', serif",
            'Courier' => 'courier, serif',
            'Georgia' => 'georgia, serif',
            'Helvetica' => 'Helvetica, serif',
            'Monospace' => 'monospace, monospace',
            'Times New Roman' => "'Times New Roman', times, serif",
            'Verdana' => 'verdana, serif'
        );
    }

    public function getFontsize() {
        return array(
            '8pt' => '8pt',
            '9pt' => '9pt',
            '10pt' => '10pt',
            '11pt' => '11pt',
            '12pt' => '12pt',
            '14pt' => '14pt',
            '16pt' => '16pt'
        );
    }

    public function jsonEncode($data) {
        return json_encode($data, JSON_HEX_QUOT | JSON_HEX_APOS);
    }

}
