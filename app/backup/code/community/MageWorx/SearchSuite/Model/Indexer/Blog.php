<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Indexer_Blog extends MageWorx_SearchSuite_Model_Indexer_Abstract {

    const EVENT_MATCH_RESULT_KEY = 'searchsuite_blog_match_result';

    public function getName() {
        return Mage::helper('mageworx_searchsuite')->__('Blog Search Index');
    }

    public function getDescription() {
        return Mage::helper('mageworx_searchsuite')->__('Rebuild blog fulltext search index');
    }

    protected function _getResource() {
        return Mage::getResourceSingleton('mageworx_searchsuite/indexer_blog');
    }

    protected function _getIndexer() {
        return Mage::getSingleton('mageworx_searchsuite/fulltext_blog');
    }

    public function matchEvent(Mage_Index_Model_Event $event) {
        // not supported!
        return false;

        if (!$this->isVisible()) {
            return false;
        }
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == 'blog_search') {
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

        if ($entity == 'blog_search') {
            $eventType = $event->getType();
            if ($eventType == Mage_Index_Model_Event::TYPE_SAVE) {
                $page = $event->getObject();
                $event->addNewData('blog_index_update_id', $page->getId());
            } else if ($eventType == Mage_Index_Model_Event::TYPE_DELETE) {
                $page = $event->getObject();
                $event->addNewData('blog_index_delete_id', $page->getId());
            }
        }
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event) {
        $data = $event->getNewData();
        if ($data['blog_index_update_id']) {
            $this->reindex(null, $data['blog_index_update_id']);
        }
        if ($data['blog_index_delete_id']) {
            $this->remove(null, $data['blog_index_delete_id']);
        }
    }

}
