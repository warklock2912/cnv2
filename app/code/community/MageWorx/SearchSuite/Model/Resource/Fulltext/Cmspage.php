<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Fulltext_Cmspage extends MageWorx_SearchSuite_Model_Resource_Fulltext_Abstract {

    public function _construct() {
        $this->_init('mageworx_searchsuite/cmspage_fulltext', 'page_id');
    }

    protected function _rebuildStoreIndex($storeId, $pageIds = null) {

        $this->cleanIndex($storeId, $pageIds);

        $contentFilter = Mage::getModel('mageworx_searchsuite/cms_content_filter');
        $designSettings = $contentFilter->getDesignSettings();
        $designSettings->setArea('frontend');
        $designSettings->setStore($storeId);

        $lastPageId = 0;

        $useWidgets = version_compare(Mage::getVersion(), '1.4.0', '>');


        while (true) {
            $pages = $this->_getSearchablePages($storeId, $pageIds, $lastPageId);
            if (!$pages) {
                break;
            }
            // index cms
            $pageIndexes = array();
            foreach ($pages as $pageData) {

                if ($useWidgets) {
                    $pageData['content'] = preg_replace_callback('@{{.*?}}@si', 'MageWorx_SearchSuite_Helper_Data::loadWidget', $pageData['content']);
                }

                $lastPageId = $pageData['page_id'];
                if (!isset($pageData['page_id'])) {
                    continue;
                }

                $index = array();
                if (isset($pageData['title'])) {
                    $index[] = $pageData['title'];
                }

                if (isset($pageData['content'])) {
                    $html = "";
                    try {
                        $html = $contentFilter->process($pageData['content']);
                    } catch (Exception $e) {
                        Mage::log($pageData);
                        Mage::log($e);
                        continue;
                    }

                    $searchString = array('@&lt;script.*?&gt;.*?&lt;/script&gt;@si', '@&lt;style.*?&gt;.*?&lt;/style&gt;@si');
                    $replaceString = array('', '');
                    $html = trim(preg_replace($searchString, $replaceString, $html));
                    $html = preg_replace("#\s+#si", " ", trim(strip_tags($html)));
                    $index[] = html_entity_decode($html, ENT_QUOTES, "UTF-8");
                }
                $pageIndexes[$pageData['page_id']] = join(' ', $index);
            }
            $this->_saveIndexes($storeId, $pageIndexes);
        }

        return $this;
    }

    protected function _getSearchablePages($storeId, $pageIds = null, $lastPageId = 0, $limit = 100) {

        $filterPages = Mage::helper('mageworx_searchsuite')->getFilterCmsPages($storeId);
        if (is_null($filterPages))
            $filterPages = 'no-route,enable-cookies';
        $filterPages = explode(',', $filterPages);

        $select = $this->_getReadAdapter()->select()->
                        from(array('p' => $this->getTable('cms/page')), array('page_id', 'title', 'identifier', 'content'))->
                        joinInner(
                                array('store' => $this->getTable('cms/page_store')), $this->_getReadAdapter()->quoteInto('store.page_id=p.page_id AND (store.store_id=? OR store.store_id=0)', $storeId), array()
                        )->where('p.identifier NOT IN(?)', $filterPages);


        if ($pageIds != null) {
            $select->where('p.page_id IN(?)', $pageIds);
        }

        $select->where('p.is_active');
        $select->where('p.page_id>?', $lastPageId)->
                limit($limit)->
                order('p.page_id');

        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function prepareResult($object, $queryText, $query) {
        if (!$query->getIsCmspageProcessed()) {
            $this->_performSearch('page_id', $this->getTable('mageworx_searchsuite/cmspage_result'), $queryText, $query);
            $query->setIsCmspageProcessed(1);
            $query->save();
        }

        return $this;
    }

    public function rebuildIndex($storeId = null, $ids = null) {
        $this->resetSearchResults('is_cmspage_processed', $this->getTable('mageworx_searchsuite/cmspage_result'));
        parent::rebuildIndex($storeId, $ids);
    }

}
