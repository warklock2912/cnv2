<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Adminhtml_Amsegments_SegmentController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('customer/amsegments/segment');
        $this->_title($this->__('Segments'));
        $this->_addContent($this->getLayout()->createBlock('amsegments/adminhtml_segment')); 
        $this->renderLayout();
    }
    
    public function newAction() 
    {
        $this->editAction();
    }
    
    public function deleteAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amsegments/segment')->load($id);

        if ($model->getId()) {
            $model->delete();
            $msg = Mage::helper('amsegments')->__('Segment has been successfully deleted');
                
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            $this->_redirect('*/*/');
        }
    }
	
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amsegments/segment')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amsegments')->__('Record does not exist'));
            $this->_redirect('*/*/');
        } else {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }
            else 
            {
                $this->prepareForEdit($model);
            }
            
            $this->loadLayout();

            $this->_setActiveMenu('customer/amsegments/segment');
            
            $this->_title($this->__('Edit'));

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(1);
            $head->setCanLoadRulesJs(1);
            
            $editBlock = $this->getLayout()->createBlock('amsegments/adminhtml_segment_edit');
            $tabsBlock = $this->getLayout()->createBlock('amsegments/adminhtml_segment_edit_tabs');
            
            $editBlock->setModel($model);
            $tabsBlock->setModel($model);
            
            $this->_addContent($editBlock);
            $this->_addLeft($tabsBlock);

            $this->renderLayout();
        }
    }
    
    function prepareForEdit($model){
        $model->getConditions()->setJsFormObject('segment_conditions_fieldset');
    }
    
    function prepareForSave($model){
        
    }
    
    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amsegments/segment');
        $data = $this->getRequest()->getPost();
        
        if ($data) {
            
		    
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            
            unset($data['rule']);
            
            if (!$id){
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            if (isset($data['website_ids']) && is_array($data['website_ids'])){
                $data['website_ids'] = implode(",", $data['website_ids']);
            } else {
                $data['website_ids'] = "0";
            }

            $model->setData($data);  // common fields
            $model->loadPost($data); // rules

            $model->setId($id);
            try {
                $this->prepareForSave($model);
                
                $model->save();
                
                $model = Mage::getModel('amsegments/segment')->load($model->getId());
                
                $model->process();
                
//                exit(1);

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amsegments')->__('Rule has been successfully saved');
                
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                if ($this->getRequest()->getParam('continue')){
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*');
                }
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }	
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amsegments')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
	
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $segment = Mage::getModel('amsegments/segment');
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule($segment)
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
    
    public function customerGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $segment = Mage::getModel('amsegments/segment')->load($id);
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('amsegments/adminhtml_segment_edit_tab_customers')
                ->setModel($segment)
                ->toHtml()
        );
    }
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $id = $this->getRequest()->getParam('id');
        $segment = Mage::getModel('amsegments/segment')->load($id);
        
        $fileName   = $segment->getName() . ' segment.csv';
        $grid       = $this->getLayout()->createBlock('amsegments/adminhtml_segment_edit_tab_customers');
        $grid->setModel($segment);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $id = $this->getRequest()->getParam('id');
        $segment = Mage::getModel('amsegments/segment')->load($id);
        
        $fileName   = $segment->getName() . ' segment.xml';
        $grid       = $this->getLayout()->createBlock('amsegments/adminhtml_segment_edit_tab_customers');
        $grid->setModel($segment);
        
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
    public function reindexAction(){
        $process = Mage::getSingleton('index/indexer')->getProcessByCode("amsegemnts_indexer");
        $process->reindexEverything();
        $msg = Mage::helper('amsegments')->__('Customers Segmentation index was rebuilt.');        
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        $this->_redirect('*/*/');
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/amsegments');
    }
    
}
?>