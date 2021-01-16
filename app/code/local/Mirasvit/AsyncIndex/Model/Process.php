<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_asyncindex
 * @version   1.1.13
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



/**
 * Переопределяем модель Mage_Index_Model_Process
 *
 * @category Mirasvit
 * @package  Mirasvit_AsyncIndex
 */
class Mirasvit_AsyncIndex_Model_Process extends Mage_Index_Model_Process
{
    const STATUS_WAIT = 'wait';

    public function getStatusesOptions()
    {
        return array(
            self::STATUS_PENDING         => Mage::helper('index')->__('Ready'),
            self::STATUS_RUNNING         => Mage::helper('index')->__('Processing'),
            self::STATUS_REQUIRE_REINDEX => Mage::helper('index')->__('Reindex Required'),
            self::STATUS_WAIT            => Mage::helper('index')->__('Wait (in queue)'),
        );
    }

    /**
     * Reindex all data what this process responsible is
     *
     */
    public function reindexAll($force = false)
    {
        if (!$force && Mage::getStoreConfig('asyncindex/general/full_reindex')) {
            $this->changeStatus(Mirasvit_AsyncIndex_Model_Process::STATUS_WAIT);
        } else {
            return parent::reindexAll();
        }
    }

    public function getUnprocessedEventsCollection()
    {
        $eventsCollection = Mage::getResourceModel('index/event_collection');
        $eventsCollection->addProcessFilter($this, self::EVENT_STATUS_NEW);

        return $eventsCollection;
    }

    /**
     * Reindex all data what this process responsible is
     * Check and using depends processes
     *
     * @return Mage_Index_Model_Process
     */
    public function reindexEverything($force = false)
    {
        parent::reindexEverything();

        if ($force) {
            return $this->reindexAll(true);
        }
    }

    /**
     * Переопределяем функцию
     * Нам не нужно каждый раз помечать индекс как reindex_required
     */
    public function register(Mage_Index_Model_Event $event)
    {
        if ($this->matchEvent($event)) {
            $this->_setEventNamespace($event);
            $this->getIndexer()->register($event);
            $event->addProcessId($this->getId());
            $this->_resetEventNamespace($event);
            // if ($this->getMode() == self::MODE_MANUAL) {
            //     $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
            // }
        }

        return $this;
    }

    public function fastProcessEvent(Mage_Index_Model_Event $event)
    {
        if (!$this->matchEvent($event) && !$event) {
            return $this;
        }

        if (!in_array($this->getId(), Mage::getSingleton('asyncindex/config')->getIgnoredIndexes())) {
            $this->_setEventNamespace($event);

            $this->getIndexer()->processEvent($event);

            $event->resetData();
            $this->_resetEventNamespace($event);
        }

        $event->addProcessId($this->getId(), self::EVENT_STATUS_DONE);

        return $this;
    }
}