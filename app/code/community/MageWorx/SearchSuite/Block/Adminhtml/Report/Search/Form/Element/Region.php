<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Report_Search_Form_Element_Region extends Varien_Data_Form_Element_Abstract {

    /**
     * Retrieve search query model instance
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getSearchQuery() {
        return Mage::registry('current_catalog_search');
    }

    /**
     * Get tracking collection for curent query
     * @return MageWorx_SearchSuite_Model_Mysql4_Tracking_Purchase_Collection
     */
    public function getCollection() {
        $collection = Mage::getModel('mageworx_searchsuite/tracking_region')->getCollection()
                ->setQueryFilter($this->getSearchQuery());
        return $collection;
    }

    public function getElementHtml() {
        $collection = $this->getCollection();
        $html = array();
        $geoip = null;
        if (Mage::getConfig()->getModuleConfig('MageWorx_GeoIP')->is('active', true)) {
            $geoip = Mage::helper('mageworx_geoip');
        }
        $model = Mage::registry('current_catalog_search');
        $popularity = $model->getData('popularity');
        if ($collection->count() > 0) {
            foreach ($collection as $item) {
                if ($geoip) {
                    $str = '<img alt="' . $item->getCountry() . '" title="' . $item->getCountry() . '" src="' . $geoip->getFlagPath($item->getCountry()) . '"/> ';
                    $countryModel = Mage::getSingleton('directory/country')->loadByCode($item->getCountry());
                    $str.=$countryModel->getName();
                    $str.= ' (' . round($item->getNumUses() / $popularity * 100, 3) . '%)';
                    $html[] = $str;
                } else {
                    $html[] = $item->getCountry();
                }
            }
        } else {
            $html[] = Mage::helper('mageworx_searchsuite')->__('n/a');
        }

        return implode(', ' . PHP_EOL, $html);
    }

}
