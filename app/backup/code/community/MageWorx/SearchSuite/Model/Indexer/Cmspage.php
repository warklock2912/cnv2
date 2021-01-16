<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Indexer_Cmspage extends MageWorx_SearchSuite_Model_Indexer_Abstract {

    const EVENT_MATCH_RESULT_KEY = 'searchsuite_cms_page_match_result';

    public function _construct() {
        parent::_construct();
        $this->_isVisible = Mage::helper('mageworx_searchsuite')->isCmspageIndexEnabled();
    }

    public function getName() {
        return Mage::helper('mageworx_searchsuite')->__('Cms Page Search Index');
    }

    public function getDescription() {
        return Mage::helper('mageworx_searchsuite')->__('Rebuild cms pages search index');
    }

    protected function _getResource() {
        return Mage::getResourceSingleton('mageworx_searchsuite/indexer_cmspage');
    }

    protected function _getIndexer() {
        return Mage::getSingleton('mageworx_searchsuite/fulltext_cmspage');
    }

    public function matchEvent(Mage_Index_Model_Event $event) {

        if (!$this->isVisible()) {
            return false;
        }
        $data = $event->getNewData();

        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == 'cms_page_search') {
            $result = $event->getDataObject()->hasDataChanges() || ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE);
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);
        return $result;
    }

    protected function _registerEvent(Mage_Index_Model_Event $event) {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();

        if ($entity == 'cms_page_search') {
            $eventType = $event->getType();
            if ($eventType == Mage_Index_Model_Event::TYPE_SAVE) {
                $page = $event->getDataObject();
                $event->addNewData('cms_page_update_id', $page->getPageId());
            } else if ($eventType == Mage_Index_Model_Event::TYPE_DELETE) {
                $page = $event->getDataObject();
                $event->addNewData('cms_page_delete_id', $page->getPageId());
            }
        }
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event) {
        $data = $event->getNewData();
        if ($data['cms_page_update_id']) {
            $this->reindex(null, $data['cms_page_update_id']);
        }
        if ($data['cms_page_delete_id']) {
            $this->remove(null, $data['cms_page_delete_id']);
        }
    }

}

?>
