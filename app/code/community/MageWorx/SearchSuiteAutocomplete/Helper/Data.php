<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Helper_Data extends MageWorx_SearchSuite_Helper_Data {

    public function showPopup() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/autocomplete/show_popup');
    }

    public function getDelay() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/autocomplete/search_delay');
    }

    public function getMoreResultsUrl() {
        return Mage::helper('catalogsearch')->getResultUrl(Mage::helper('catalogsearch')->getQueryText(Mage::helper('catalogsearch')->getQuery()->getQueryText()));
    }

    public function getPopupFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/autocomplete/show_fields'));
    }

    public function getSuggestResultsNumber() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/autocomplete/suggest_results_number');
    }

    public function getProductResultsNumber() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/autocomplete/product_results_number');
    }

    public function getProductResultFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/autocomplete/product_result_fields'));
    }

    public function getProductImageSize() {
        $size = Mage::getStoreConfig('mageworx_searchsuite/autocomplete/product_image_size');
        $size = explode('x', trim($size));
        return $size;
    }

    public function isShowProductResultsGroupedByCategories() {
        return Mage::getStoreConfig('mageworx_searchsuite/autocomplete/show_product_results_grouped_by_categories');
    }

    public function getSummaryHtml($product, $templateType = 'short', $displayIfNoReviews = false) {
        return $this->getLayout()->createBlock('review/helper')->getSummaryHtml($product, $templateType, $displayIfNoReviews);
    }

    public function getCmspageFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/autocomplete/cmspage_fields'));
    }

    public function getCategoryFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/autocomplete/category_fields'));
    }

    public function isCategorySearchEnabled() {
        return $this->isCategoryIndexEnabled() && in_array('category', $this->getPopupFields());
    }

    public function isCmspageSearchEnabled() {
        return $this->isCmspageIndexEnabled() && in_array('cmspage', $this->getPopupFields());
    }

    public function getAnimation() {
        return Mage::getStoreConfig('mageworx_searchsuite/autocomplete/animation');
    }

    public function getCategoryThumbnailSize($parse = true) {
        if (!$parse) {
            return Mage::getStoreConfig('mageworx_searchsuite/autocomplete/category_thumbnail_size');
        }
        $size = explode('x', Mage::getStoreConfig('mageworx_searchsuite/autocomplete/category_thumbnail_size'));
        $w = 80;
        $h = 80;
        if (count($size) == 2) {
            $w = intval($size[0]);
            $h = intval($size[1]);
        } else if (count($size) == 1) {
            $w = $h = intval($size[0]);
        }
        return array('w' => $w, 'h' => $h);
    }

    public function isHighlightingEnabled() {
        if (Mage::app()->getStore()->isAdmin()) {
            return 0;
        }
        return (int) Mage::getStoreConfigFlag('mageworx_searchsuite/autocomplete/highlighting_enabled');
    }

    public function getCss() {
        if (Mage::app()->getStore()->isAdmin()) {
            return '';
        }
        $css = '<style>';
        $json = json_decode(Mage::getStoreConfig('mageworx_searchsuite/autocomplete/customizer'));
        if ($json) {
            foreach ($json as $className => $class) {
                $css.='#searchautocomplete-search-1 .' . $className . '{';
                foreach ($class as $name => $prop) {
                    if ($name == 'color') {
                        $css.='color:#' . $prop . ';';
                    } else if ($name == 'bgcolor') {
                        $css.='background-color:#' . $prop . ';';
                    } else if ($name == 'font') {
                        $css.='font:' . $prop . ';';
                    } else if ($name == 'fontsize') {
                        $css.='font-size:' . $prop . ';';
                    } else if ($name == 'italic') {
                        $css.='font-style:' . $prop . ';';
                    }
                }
                $css.='} ';
            }
        }
        $css.='</style>';
        return $css;
    }

}
