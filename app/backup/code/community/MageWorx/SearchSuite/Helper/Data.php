<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_forDeleteSpecialChars = array('+', '>', '<', '(', ')', '~', '*', ':', '"', '&', '\'', '/', '\\');
    protected $_forReplaceSpecialChars = array('-', '.');
    protected $_engine = null;
    protected $_js = array();

    public static function loadWidget($match) {
        $widget = str_replace(array('{{widget', '}}'), '', $match[0]);
        $html = Mage::getModel('widget/template_filter')->widgetDirective(array($match[0], 'widget', $widget));
        return $html;
    }

    public function isCmspageIndexEnabled() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/indexes/cmspage_enable');
    }

    public function isCategoryIndexEnabled() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/indexes/category_enable');
    }

    public function getFilterCmsPages($storeId = null) {
        return Mage::getStoreConfig('mageworx_searchsuite/indexes/cmspage_filter', $storeId);
    }

    public function getSearchAccuracy() {
        return Mage::getStoreConfig('mageworx_searchsuite/main/search_accuracy');
    }

    public function getPriorityMultiplier() {
        return Mage::getStoreConfig('mageworx_searchsuite/main/priority_multiplier');
    }

    public function isSearchByAttributes() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/main/search_by_attributes');
    }

    public function isSearchByCategories() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/main/search_by_categories');
    }

    public function isCmspageSearchEnabled() {
        return $this->isCmspageIndexEnabled() && Mage::getStoreConfigFlag('mageworx_searchsuite/main/cmspage_enable');
    }

    public function isCategorySearchEnabled() {
        return $this->isCategoryIndexEnabled() && Mage::getStoreConfigFlag('mageworx_searchsuite/main/category_enable');
    }

    public function getSearchType($storeId = null) {
        return Mage::getStoreConfig(Mage_CatalogSearch_Model_Fulltext::XML_PATH_CATALOG_SEARCH_TYPE, $storeId);
    }

    public function getDefaultSearchText() {
        return Mage::getStoreConfig('mageworx_searchsuite/main/default_search_text');
    }

    public function getCmspageFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/main/cmspage_fields'));
    }

    public function getCategoryFields() {
        return explode(',', Mage::getStoreConfig('mageworx_searchsuite/main/category_fields'));
    }

    public function isHighlightingEnabled() {
        return (int) Mage::getStoreConfigFlag('mageworx_searchsuite/main/highlighting_enabled');
    }

    public function isHighlighProduct() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/main/highlight_product');
    }

    public function isRedirectToProduct() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/main/product_redirect');
    }

    public function getSearchFieldLength() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/main/searchfield_length');
    }

    public function isDidYouMean() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/main/did_you_mean');
    }

    public function showDidYouMeanResults() {
        return $this->isDidYouMean() && Mage::getStoreConfigFlag('mageworx_searchsuite/main/did_you_mean_results');
    }

    public function isRelatedSearches() {
        return Mage::getStoreConfigFlag('mageworx_searchsuite/main/related_searches');
    }

    public function getCategoryThumbnailSize($parse = true) {
        if (!$parse) {
            return Mage::getStoreConfig('mageworx_searchsuite/main/category_thumbnail_size');
        }
        $size = explode('x', Mage::getStoreConfig('mageworx_searchsuite/main/category_thumbnail_size'));
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

    public function getCategoryThumbnailImageUrl($category, string $size = null) {
        if (!$size) {
            $size = $this->getCategoryThumbnailSize(false);
        }
        $url = '';
        if ($category->getThumbnail()) {
            $basePath = Mage::getBaseDir('media');
            $nt = 'catalog' . DS . 'category' . DS . 'cache' . DS . 'thumbnail' . DS . $size . DS . $category->getThumbnail();
            if (file_exists($np = $basePath . DS . $nt)) {
                $url = Mage::getBaseUrl('media') . str_replace('\\', '/', $nt);
            } else {
                if (file_exists($op = $basePath . DS . 'catalog' . DS . 'category' . DS . $category->getThumbnail())) {
                    $size = $this->getCategoryThumbnailSize(true);
                    $this->_resizeImg($op, $np, $size['w'], $size['h']);
                    $url = Mage::getBaseUrl('media') . str_replace('\\', '/', $nt);
                }
            }
        }
        return $url;
    }

    public function _resizeImg($originalPath, $newPath, $width, $height) {
        $image = new Varien_Image($originalPath);
        $image->constrainOnly(true);
        $image->keepAspectRatio(false);
        $image->keepFrame(false);
        $image->resize($width, $height);
        $image->save($newPath);
    }

    public function prepareValue($str) {
        $prvalue = str_replace($this->_forDeleteSpecialChars, ' ', $str);
        $prvalue = str_replace($this->_forReplaceSpecialChars, '_', $prvalue);

        return $prvalue;
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    public function escapeLikeValue($value, $options = array()) {
        $value = str_replace('\\', '\\\\', $value);

        $from = array();
        $to = array();
        if (empty($options['allow_symbol_mask'])) {
            $from[] = '_';
            $to[] = '\_';
        }
        if (empty($options['allow_string_mask'])) {
            $from[] = '%';
            $to[] = '\%';
        }
        if ($from) {
            $value = str_replace($from, $to, $value);
        }

        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%' . $value . '%';
                    break;
                case 'start':
                    $value = $value . '%';
                    break;
                case 'end':
                    $value = '%' . $value;
                    break;
            }
        }
        return $value;
    }

    public function getSearchParameter() {
        $param = Mage::app()->getRequest()->getParam('a');
        if ($param && !empty($param) && $param != 'all') {
            return $param;
        }
        return false;
    }

    public function getSearchCategory() {
        $param = Mage::app()->getRequest()->getParam('cat');
        if ($param && !empty($param) && $param != 'all') {
            return $param;
        }
        return false;
    }

    public function getCmspageSearchResults() {
        $result = null;
        if ($this->isCmspageSearchEnabled() && Mage::helper('catalogsearch')->getQuery() && !Mage::helper('catalogsearch')->isMinQueryLength()) {
            $result = Mage::getResourceModel('mageworx_searchsuite/fulltext_cmspage_collection')
                    ->addSearchFilter(Mage::helper('catalogsearch')->getQuery());
        }
        return $result;
    }

    public function getCategorySearchResults() {
        $result = null;
        if ($this->isCategorySearchEnabled() && Mage::helper('catalogsearch')->getQuery() && !Mage::helper('catalogsearch')->isMinQueryLength()) {
            $result = Mage::getResourceModel('mageworx_searchsuite/fulltext_category_collection')
                    ->addSearchFilter(Mage::helper('catalogsearch')->getQuery());
            foreach ($this->getCategoryFields() as $field) {
                $result->addAttributeToSelect($field);
            }
        }
        return $result;
    }

    public function setSearchQuery($queryId, $queryText) {
        $data = $this->getSearchTransition();
        $data['query'] = (int) $queryId;
        $data['query_text'] = $queryText;
        Mage::getSingleton('core/session')->setData('mageworx_searchsuite', $data);
    }

    public function setSearchTransition($productId = null) {
        $data = $this->getSearchTransition();
        if (isset($data['query']) && !is_null($productId)) {
            $query = strtolower(Mage::helper('catalogsearch')->getQueryParamName() . '=' . $data['query_text']);
            if (strpos(Mage::helper('mageworx_searchsuite')->getHttpRefferer(), 'catalogsearch/result') > 0 && strpos($this->getHttpRefferer(), $query) > 0) {
                $data[$productId] = $data['query'];
                Mage::getSingleton('core/session')->setData('mageworx_searchsuite', $data);
                return $data['query'];
            }
        }
        return null;
    }

    public function getSearchTransition() {
        $data = Mage::getSingleton('core/session')->getData('mageworx_searchsuite');
        if (!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    public function getHttpRefferer() {
        return strtolower(Mage::helper('core/http')->getHttpReferer(true));
    }

    public function sanitizeContent($page) {
        $processor = Mage::getModel('mageworx_searchsuite/cms_content_filter');

        if (is_object($page)) {
            $text = ($page->getContent()) ? $page->getContent() : $page->getPostContent();
        } else {
            $text = strval($page);
        }

        $text = preg_replace_callback('@{{.*?}}@si', array($this, 'loadWidget'), $text);
        //$text = preg_replace_callback('@{{.*?}}@si','MageWorx_SearchAutocomplete_Helper_Data::loadWidget', $text);        

        if (is_object($page)) {
            $designSettings = $processor->getDesignSettings();
            $designSettings->setArea('frontend');
            $arStoreId = $page->getStoreId();
            if (is_array($arStoreId) && count($arStoreId) > 0)
                $storeId = $arStoreId[0];
            else
                $storeId = intval($arStoreId);
            $designSettings->setStore($storeId);
            $text = $processor->process($text);
        }


        $search = array('@&lt;script.*?&gt;.*?&lt;/script&gt;@si', '@&lt;style.*?&gt;.*?&lt;/style&gt;@si');
        $replace = array('', '');
        $text = trim(strip_tags(preg_replace($search, $replace, $text)));
        $result = $this->limitText($text, 10);
        if ($result != '' && strlen($text) > strlen($result))
            $result .= '...';
        return $result;
        //return $this->highlightText($result);
    }

    public function limitText($str, $limit) {
        $queryText = Mage::helper('catalogsearch')->getQueryText();
        $str = preg_replace('/[ ]{2,}/', ' ', $str);
        $words = explode(' ', $str);
        $count = count($words);
        if ($count > $limit) {
            $offset = 0;
            foreach ($words as $key => $word) {
                if (preg_match('/(' . $queryText . ')/is', $word)) {
                    $offset = $key;
                    break;
                }
            }
            if ($offset + $limit / 2 > $count) {
                $str = '...' . implode(' ', array_slice($words, $count - $limit));
            } else if ($offset - $limit / 2 < 0) {
                $str = implode(' ', array_slice($words, 0, $limit)) . '...';
            } else {
                $str = '...' . implode(' ', array_slice($words, $offset - $limit / 2, $limit)) . '...';
            }
        }
        return $this->highlightText($str);
    }

    public function highlightText($str, $query = null) {
        if (!$this->isHighlightingEnabled())
            return $str;
        if (is_null($query)) {
            $q = Mage::helper('catalogsearch')->getEscapedQueryText();
            $q = preg_quote($q, '/');
        } else {
            $q = $query;
        }
        return preg_replace('/(' . $q . ')(?:(?![^>]*(?:".*>))|(?=[^>]*(?:<a\s)))/is', '<span class="highlight">\\1</span>', $str);
    }

    public function registerEngine($key, $name) {
        if (is_array(Mage::registry('searchsuite_engines'))) {
            $data = array_merge(Mage::registry('searchsuite_engines'), array(array('value' => $key, 'label' => $name)));
            Mage::unregister('searchsuite_engines');
            Mage::register('searchsuite_engines', $data);
        } else {
            Mage::register('searchsuite_engines', array(array('value' => $key, 'label' => $name)));
        }
    }

    public function getEngineName($storeId = null) {
        return Mage::getStoreConfig('mageworx_searchsuite/main/engine', $storeId);
    }

    public function getEngine($storeId = null) {
        $engine = $this->getEngineName($storeId);
        if ($engine && $engine != 'xsearch') {
            $engine = 'mageworx_searchsuite' . $engine . '/catalogSearch_fulltext_engine';
            if (Mage::getConfig()->getResourceModelClassName($engine)) {
                $this->_engine = Mage::getResourceSingleton($engine);
            }
        }
        if (!$this->_engine) {
            $this->_engine = Mage::getResourceSingleton('mageworx_searchsuite/catalogSearch_fulltext_engine');
        }
        return $this->_engine;
    }

    public function getQuickSearchCategories() {
        $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('use_in_quicksearch', array('eq' => '1'))
                ->setOrder('path');
        $c = $categories->getItems();
        usort($c, array($this, 'cmpCategoriesPath'));
        return $c;
    }

    public static function cmpCategoriesPath($a, $b) {
        $_a = explode('/', $a->getPath());
        $_b = explode('/', $b->getPath());
        $level = count($_a);
        if ($level > count($_b)) {
            $level = count($_b);
        }
        $val1 = (int) $_a[$level - 1];
        $val2 = (int) $_b[$level - 1];
        if ($val1 > $val2) {
            return 1;
        } else if ($val1 < $val2) {
            return -1;
        } else {
            if (count($_a) > count($_b)) {
                return 1;
            } else if (count($_a) < count($_b)) {
                return -1;
            } else {
                return 0;
            }
        }
    }

    public function addFooterJs($src) {
        $this->_js[$src] = $src;
    }

    public function getFooterJsHtml() {
        $js = "";
        if (count($this->_js)) {
            foreach ($this->_js as $src) {
                $js.='<script type="text/javascript" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/carnival/default/js/' . $src . '"></script>';
            }
        }
        return $js;
    }

    public function IsMergeJs() {
        return Mage::getStoreConfigFlag('dev/js/merge_files');
    }
    
    public function formatKeyword($keyword){      
        return ucfirst(strtolower(strip_tags($keyword)));
    }

}
