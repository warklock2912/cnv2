<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Adminhtml_Amfollowup_RuleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('promo/amfollowup/rule');
        $this->_title($this->__('Rules'));
        $this->_addContent($this->getLayout()->createBlock('amfollowup/adminhtml_rule')); 
        $this->renderLayout();
    }
    
    public function newAction() 
    {
        $this->editAction();
    }
    
    public function deleteAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amfollowup/rule')->load($id);

        if ($model->getId()) {
            $model->delete();
            $msg = Mage::helper('amfollowup')->__('Rule has been successfully deleted');
                
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            $this->_redirect('*/*/');
        }
    }
	
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amfollowup/rule')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfollowup')->__('Record does not exist'));
            $this->_redirect('*/*/');
        } else {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->addData($data);
            }
            else 
            {
                $this->prepareForEdit($model);
            }

            $this->loadLayout();

            $this->_setActiveMenu('promo/amfollowup/rule');
            
            $this->_setActiveMenu('promo/amfollowup/rule');
            $this->_title($this->__('Edit'));

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(1);
            $head->setCanLoadRulesJs(1);
            
            $editBlock = $this->getLayout()->createBlock('amfollowup/adminhtml_rule_edit');
            $tabsBlock = $this->getLayout()->createBlock('amfollowup/adminhtml_rule_edit_tabs');
            
            $editBlock->setModel($model);
            $tabsBlock->setModel($model);
            
            
            $this->_addContent($editBlock);
            $this->_addLeft($tabsBlock);

            $this->renderLayout();
        }
    }
    
    protected function prepareForEdit($model)
    {
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('amfollowup/rule'))
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
    
    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amfollowup/rule');
        $data = $this->getRequest()->getPost();
        
        if ($data) {
		    
            if (!empty($data['customer_date_event'])) {
                $data = array_merge($data, $this->_filterDates($data, array('customer_date_event')));
            }

            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            
            unset($data['rule']);
            $model->setData($data);  // common fields
            $model->loadPost($data); // rules

            $model->setId($id);
            try {
                $this->prepareForSave($model);
                
                $model->save();

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amfollowup')->__('Rule has been successfully saved');
                
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
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfollowup')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
	
    }
    
    public function prepareForSave($model)
    {
        $fields = array('stores', 'cust_groups', 'methods', 'cancel_event_type', 'segments');
        foreach ($fields as $f){
            // convert data from array to string
            $val = $model->getData($f);
            $model->setData($f, '');
            if (is_array($val)){
                // need commas to simplify sql query
                $model->setData($f, ',' . implode(',', $val) . ',');    
            } 
        }
        
        return true;
    }
    
    public function testCustomerRuleAction(){
        
        $customerId = $this->getRequest()->getParam('id');
        $ruleId = $this->getRequest()->getParam('rule_id');
                
        $rule = Mage::getModel('amfollowup/rule')->load($ruleId);
        $customer = Mage::getModel('customer/customer')->load($customerId);
        
        if ($rule->getId() && $customer->getId()){
            $recipient = Mage::getStoreConfig("amfollowup/test/recipient");
            $schedule = Mage::getModel('amfollowup/schedule');
            
            $event = $rule->getStartEvent();
            
            $historyItems = $schedule->createCustomerHistory($rule, $event, $customer);
            
            foreach ($historyItems as $history)
                $history->processItem($rule, $recipient);
        }
        
    }
    
    public function testOrderRuleAction()
    {

        $quote = null;
        $orderId = $this->getRequest()->getParam('id');
        $ruleId = $this->getRequest()->getParam('rule_id');
                
        $rule = Mage::getModel('amfollowup/rule')->load($ruleId);
        $order = Mage::getModel('sales/order')->load($orderId);

        if (version_compare(Mage::getVersion(), '1.5', '>')) {
            $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($order->getQuoteId());
        } else {
            $collection = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id', $order->getQuoteId());
            $items = $collection->getItems();
            $quote = end($items);
        }

        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        if ($rule->getId() && $order->getId() && $quote->getId()){

            $recipient = Mage::getStoreConfig("amfollowup/test/recipient");
            
            $schedule = Mage::getModel('amfollowup/schedule');

            $event = $rule->getStartEvent();

            $historyItems = $schedule->createOrderHistory($rule, $event, $order, $quote, $customer);

            foreach ($historyItems as $history){
                $history->processItem($rule, $recipient);
            }
        }
        
    }
    
    public function customerGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('amfollowup/adminhtml_rule_edit_tab_test_customer')
                ->toHtml()
        );
    }
    
    public function orderGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('amfollowup/adminhtml_rule_edit_tab_test_order')
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amfollowup');
    }
}
?>