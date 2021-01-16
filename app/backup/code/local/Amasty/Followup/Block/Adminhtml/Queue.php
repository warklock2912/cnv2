<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */   
class Amasty_Followup_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $scheduleCollection = Mage::getModel("cron/schedule")->getCollection()
                ->addFieldToFilter('job_code', array('eq' => 'amfollowup_history'));
        
        $scheduleCollection->getSelect()->order("schedule_id desc");
        $scheduleCollection->getSelect()->limit(1);
        
        
        
        $format = Mage::app()->getLocale()->getDateTimeFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
        );
        
        $now = Mage::app()->getLocale()
                ->date(
                    time(),
                    Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT. ' HH:mm:ss'))
            ->toString($format);
        
        $this->_controller = 'adminhtml_Queue';
        $this->_blockGroup = 'amfollowup';
        $this->_headerText = Mage::helper('amfollowup')->__('Queue'). ': '.$now;
        
        if ($scheduleCollection->getSize() > 0){
            
            foreach($scheduleCollection as $schedule) {
                $lastExecute = Mage::app()->getLocale()
                    ->date(
                        $schedule->getScheduledAt(),
                        Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT. ' HH:mm:ss'))
                ->toString($format);

                $this->_headerText .= '<div style="font-size: 9px;">'.Mage::helper('amfollowup')->__('Cron Scheduled'). ': ' . $lastExecute.'</div>';
            }
            
        } else {
            
        }
        
        $this->_addButton('refresh', array(
            'label'     =>  Mage::helper('amfollowup')->__('Refresh'),
            'onclick'   => 'document.location.href = \'' . Mage::helper("adminhtml")->getUrl('*/*/run') . '\'',
        ));
        
        parent::__construct();
        
        
        
        $this->_removeButton('add');
    }
    
}