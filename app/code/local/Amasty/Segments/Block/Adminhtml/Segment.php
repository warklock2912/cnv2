<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */   
class Amasty_Segments_Block_Adminhtml_Segment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $indexer = Mage::getSingleton('index/indexer')
                ->getProcessByCode('amsegemnts_indexer');
        
        $endedAt = strtotime($indexer->getEndedAt());
        
        if (!$endedAt || $endedAt - time() > 60 * 60 * 24){
            $indexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
        
        $this->_controller = 'adminhtml_segment';
        $this->_blockGroup = 'amsegments';
        $this->_headerText = Mage::helper('amsegments')->__('Segments');
        $this->_addButtonLabel = Mage::helper('amsegments')->__('Add Segment');
        parent::__construct();
    }
}