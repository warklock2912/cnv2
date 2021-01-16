<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Block_Adminhtml_System_Config_Form_Field_Customizer extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $helper = Mage::helper('mageworx_searchsuiteautocomplete');
        $element->setType('hidden');
        $autocomplete = Mage::getSingleton('core/layout')->createBlock('mageworx_searchsuiteautocomplete/autocomplete')->setNameInLayout('autocomplete');
        $skinUrl = Mage::getDesign()->getSkinBaseUrl(array('_area' => 'frontend', '_package' => 'carnival', '_theme' => 'default')) . 'css/mageworx/searchsuiteautocomplete/searchsuiteautocomplete.css';
        $suggestData = Mage::getResourceModel('catalogsearch/query_collection');
        $suggestData->getSelect()->limit(($helper->getSuggestResultsNumber() < 10 && $helper->getSuggestResultsNumber() > 0) ? $helper->getSuggestResultsNumber() : 3);
        $autocomplete->setSuggestData($suggestData);
        $attr = array('name', 'price');
        $fields = $helper->getProductResultFields();
        if (in_array('description', $fields)) {
            $attr[] = 'description';
        }
        if (in_array('short_description', $fields)) {
            $attr[] = 'short_description';
        }
        if (in_array('product_image', $fields)) {
            $attr[] = 'image';
        }
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->addAttributeToSelect($attr);
        $products->setOrder('relevance', 'desc');
        $products->getSelect()->limit(($helper->getProductResultsNumber() > 0 && $helper->getProductResultsNumber() < 10) ? $helper->getProductResultsNumber() : 10);
        $autocomplete->setProducts($products);

        $cmsPage = Mage::getResourceModel('cms/page_collection');
        $cmsPage->addFieldToFilter('identifier', array('nin' => explode(',', Mage::helper('mageworx_searchsuite')->getFilterCmsPages())));
        $cmsPage->getSelect()->limit(3);
        $autocomplete->setCmsPages($cmsPage);

        $categories = Mage::getResourceModel('catalog/category_collection');
        $categories->addFieldToFilter('path', array('neq' => '1'))
                ->addAttributeToFilter('parent_id', array('neq' => '0'))
                ->addIsActiveFilter();
        $categories->getSelect()->limit(5);
        foreach ($helper->getCategoryFields() as $field) {
            $categories->addAttributeToSelect($field);
        }
        $autocomplete->setCategories($categories);

        Mage::getDesign()->setArea('frontend');
        $html = $autocomplete->toHtml();
        Mage::getDesign()->setArea('adminhtml');

        return $html . Mage::getSingleton('core/layout')->createBlock('mageworx_searchsuiteautocomplete/adminhtml_js')->toHtml()
                . $element->getElementHtml()
                . '<link rel="stylesheet" type="text/css" href="' . $skinUrl . '" />';
    }

}
