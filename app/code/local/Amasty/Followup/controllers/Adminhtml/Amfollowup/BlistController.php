<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */  
class Amasty_Followup_Adminhtml_Amfollowup_BlistController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('promo/amfollowup/blacklist');
        $this->_title($this->__('Black List'));
        $this->_addContent($this->getLayout()->createBlock('amfollowup/adminhtml_blist'));         
        $this->renderLayout();
    }

    public function newAction() 
    {
        $this->editAction(); 
    }
    
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amfollowup/blist')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfollowup')->__('Item does not exist'));
            $this->_redirect('*/*/');
            return;
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        Mage::register('amfollowup_blist', $model);

        $this->loadLayout();
        
        $this->_setActiveMenu('customer/amfollowup');
        $this->_addContent($this->getLayout()->createBlock('amfollowup/adminhtml_blist_edit'));
        
        $this->renderLayout();
    }

    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amfollowup/blist');
        $data   = $this->getRequest()->getPost();
        if ($data) {
            $data['created_at'] = date("Y-m-d H:i:s", time());
            $model->setData($data)->setId($id);
            
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                $msg = Mage::helper('amfollowup')->__('Item has been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);

                $this->_redirect('*/*/');
               
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }    
                        
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfollowup')->__('Unable to find an item to save'));
        $this->_redirect('*/*/');
    } 
        
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfollowup')->__('Please select items(s)'));
        } 
        else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('amfollowup/blist')->load($id);
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
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('amfollowup/blist');
                 
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amfollowup')->__('Item has been deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amfollowup');
    }
}