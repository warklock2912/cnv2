<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Basic extends Varien_Object
    {
        protected $_rule;
        
        const LAST_EXECUTED_PATH = 'amfollowup/common/last_executed';
        protected static $_actualGap = 172800; //2 days
        protected static $_lastExecuted = null;
        protected static $_currentExecution  = null;
        protected static $_collections = array();
        
        public function __construct($rule)
        {
            $this->_rule = $rule;
        }
        
        protected function _validateStore($storeId){
            $storesIds = $this->_rule->getStores();
            $arrStores = explode(',', $storesIds);

            return empty($storesIds) || in_array($storeId, $arrStores);
        }

        protected function _validateCustomer($customerEmail){
            $ret = true;
            $segments = $this->_rule->getSegments();
            
            if (Mage::getConfig()->getNode('modules/Amasty_Segments/active') == 'true' &&
                    !empty($segments)){
                
                $arrSegments = explode(',', $segments);
                
                $collection = Mage::getModel("amsegments/index")
                        ->getCollection()
                        ->addResultSegmentsData($arrSegments);
                
                $collection->addFieldToFilter('customer.customer_email', array('eq' => $customerEmail));
                $ret = $collection->getSize() != 0;
            }
        
            return $ret;
            
        }

        protected function _validateCustomerGroup($customerGroupId){
            $customerGroupsIds = $this->_rule->getCustGroups();
            $arrCustomerGroups = explode(',', $customerGroupsIds);
            return empty($customerGroupsIds) || in_array($customerGroupId, $arrCustomerGroups);
        }
        
        protected function _validateBasic($storeId, $customerEmail, $customerGroupId){
            return $this->_rule->getIsActive() == 1 &&
                $this->_validateStore($storeId) && 
                $this->_validateCustomer($customerEmail) &&
                $this->_validateCustomerGroup($customerGroupId);
        }
        
        function validate($object){
            return true;
        }
        
        function date($timestamp){
            return date('Y-m-d H:i:s', $timestamp);
        }
        
        protected function _getCollectionKey(){
            return $this->_rule->getId() . "_" .$this->_rule->getStartEventType();
        }
            
        function getCollection(){
            if (!isset(self::$_collections[$this->_getCollectionKey()])){
                $this->setCollection();
            }
            return self::$_collections[$this->_getCollectionKey()];
        }
        
        function setCollection(){
            self::$_collections[$this->_getCollectionKey()] = $this->_initCollection();
        }
        
        protected function _initCollection(){
            return null;
        }
        
        static function clear(){
            self::$_lastExecuted = null;
            self::$_currentExecution  = null;
            self::$_collections = array();
        }
        
        static function getLastExecuted(){
            if (self::$_lastExecuted === null){
                self::$_lastExecuted = (string) Mage::getStoreConfig(self::LAST_EXECUTED_PATH);
                if (empty(self::$_lastExecuted)){
                    self::$_lastExecuted = time() - self::$_actualGap;
                }
                self::$_currentExecution = time();
                
                Mage::getConfig()->saveConfig(self::LAST_EXECUTED_PATH, self::$_currentExecution);
                Mage::getConfig()->cleanCache();
            }
            return self::$_lastExecuted;
        }
        
        static function getCurrentExecution(){
            return self::$_currentExecution ? self::$_currentExecution : time();
        }
        
        function getEmail($schedule, $history, $vars = array()){
            $templateId = $schedule->getEmailTemplateId();
            
            $ret = array(
                'body' => '',
                'subject' => ''
            );

            $storeId = $history->getStoreId();

            $variables = array_merge(array(
                'urlmanager' => Mage::getModel('amfollowup/urlmanager')->init($history),
                'formatmanager' => Mage::getModel('amfollowup/formatmanager')->init($vars),
                'store' => Mage::app()->getStore($storeId)
            ), $vars);
            
            $emailTemplate = Mage::getModel('core/email_template');
            $emailTemplate->setDesignConfig(array(
                'area' => 'frontend', 
                'store' => $storeId
            ));

            if (is_numeric($templateId)) {
                $emailTemplate->load($templateId);
            } else {
                $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
                $emailTemplate->loadDefault($templateId, $localeCode);
            }

            if (!$emailTemplate->getId()) {
                throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid transactional email code: ' . $templateId));
            }

            $ret['body'] = $emailTemplate->getProcessedTemplate($variables, true);
            $ret['subject'] = $emailTemplate->getProcessedTemplateSubject($variables);

            return $ret;
        }
    }
?>