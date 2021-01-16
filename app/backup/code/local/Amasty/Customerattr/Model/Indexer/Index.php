<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_Indexer_Index extends Mage_Index_Model_Indexer_Abstract
{

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName() {
        return Mage::helper('amcustomerattr')->__('Amasty Customer Attributes');
    }
    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription() {
        return Mage::helper('amcustomerattr')->__('Index Customer Attributes for guests');
    }
    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {

    }

    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return false;
    }
    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }

    public function reindexAll()
    {
        try {
            Mage::helper('amcustomerattr/guest')->update();
        } catch (Exception $e) {
            throw $e;
        }
    }
}