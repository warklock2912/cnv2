<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Adminhtml_Amreports_DataController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('report/amreports');
        $this->_addContent($this->getLayout()->createBlock('amreports/adminhtml_data'));
        $this->_title('Report Management');
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amreports/data')->load($id);

        $reportType = $this->getRequest()->getParam('report_type');
        if ($reportType) {
            $model->setData('report_type',$reportType);
            $model->setData('report_name_full', Mage::helper('amreports')->getReportName($reportType) );
        }

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amreports')->__('Item does not exist'));
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $data['orderStatuses'] = explode(',',$data['orderStatuses']);
            $model->setData($data);
        }
        $this->_prepareForEdit($model);
        Mage::register('amreports_data', $model);
        $this->loadLayout();
        $this->_setActiveMenu('report/amreports');
        $this->_addContent( $this->getLayout()->createBlock('amreports/adminhtml_data_edit') );
        $this->_title('Edit Report');
        $this->renderLayout();
    }

    public function saveAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amreports/data');
        $data   = $this->getRequest()->getPost();
        if ($data) {
            $this->_prepareForSave($model, $data);
            $model->setId($id);
            try {
                if (!isset($data['newReport'])) {
                    $model->save();
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if (isset($data['newReport'])) {
                    $this->_redirect('*/*/edit', array('report_type' => $data['report_type']));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amreports')->__('Unable to find an item to save'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amreports')->__('Please select items(s)'));
        }
        else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('amreports/data')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('amreports/data');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amreports')->__('Item has been deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _prepareForSave($model, $data)
    {
        $data = array_filter($data);
        $reportProcessor = Mage::getSingleton(
            'amreports_reports/' . $data['report_type']
        );
        if (isset($data['StoreSelect']) && is_array($data['StoreSelect'])) {
            $data['StoreSelect'] = implode(',',$data['StoreSelect']);
        } else {
            $data['StoreSelect'] = '';
        }
        if (isset($data['OrderStatus']) && count( $data['OrderStatus'] )>0) {
            $data['OrderStatus'] = implode(',',$data['OrderStatus']);
        }
        $resultData = array();
        $allowedFields = $reportProcessor->getReportFields();
        foreach ($data as $key=>$value) {
            if ( in_array($key, $allowedFields)) {
                $resultData[$key] = $value;
            }

        }
        $model->setData('forms_data', serialize($resultData) );
        isset($data['report_type']) ? $model->setData('report_type', $data['report_type'] ) :'';
        isset($data['name']) ? $model->setData('name', $data['name'] ) :'';
        if (isset($data['json_answer'])) {
            $model->setData('json_answer', $data['json_answer'] );
        }
        $model->setData('update_date', Mage::getModel('core/date')->date('Y-m-d H:i:s') );
        $model->setData('report_name_full', Mage::helper('amreports')->getReportName( $data['report_type'] ));
    }

    protected function _prepareForEdit($model)
    {
        $formsData = $model->getData('forms_data');
        if (!$formsData) return false;
        $formsData = unserialize($formsData);
        foreach ($formsData as $name=>$data) {
            switch ($name) {
                case 'OrderStatus':
                    $data = explode(',', $data);
                    $model->setData('show_order_statuses', '1');
                    break;
                case 'StoreSelect':
                    $data = explode(',', $data);
                    break;
            }
            $model->setData($name, $data);
        }
        $model->setData('report_name_full', Mage::helper('amreports')->getReportName( $model->getData('report_type') ));
    }
}