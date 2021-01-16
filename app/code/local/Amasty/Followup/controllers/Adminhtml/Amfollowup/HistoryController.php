<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Adminhtml_Amfollowup_HistoryController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::getModel('amfollowup/schedule')->run();
        
        $this->loadLayout(); 

        $this->_setActiveMenu('promo/amfollowup/history');
        $this->_title($this->__('History'));
            
        $this->_addContent($this->getLayout()->createBlock('amfollowup/adminhtml_history')); 
            $this->renderLayout();

    }
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'history.csv';
        $grid       = $this->getLayout()->createBlock('amfollowup/adminhtml_history_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'history.xml';
        $grid       = $this->getLayout()->createBlock('amfollowup/adminhtml_history_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amfollowup');
    }
}
?>