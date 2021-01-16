<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Adminhtml_Amreports_ReportsController extends Mage_Adminhtml_Controller_Action
{
    public function ajaxAction()
    {
        $action = $this->getRequest()->getParam('action');
        switch($action) {
            case 'getReport':
                $this->getResponse()->setBody($this->getReport());
                break;
            case 'getTranslate':
                $this->getResponse()->setBody($this->getTranslate());
                break;
        }
        exit;
    }

    private function getReport()
    {
        $model = Mage::getModel('amreports/reports');
        $records =  $model->getRecords( $this->getRequest()->getParam('report_type'), $this->getRequest()->getParams() );
        return Mage::helper('core')->jsonEncode($records);
    }

    private function getTranslate()
    {
        $hlp = Mage::helper('amreports/translator');
        $translateArray = $hlp->loadModuleTranslation('Amasty_Reports');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($translateArray));
    }
}