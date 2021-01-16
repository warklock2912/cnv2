<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Urlmanager extends Varien_Object
    {
        protected $_history;
        protected $_rule;
        
        protected $_googleAnalyticsParams = array(
            'utm_source', 'utm_medium', 'utm_term', 
            'utm_content', 'utm_campaign'
        );
        
        function init($history){
            $this->_history = $history;
            $this->_rule = Mage::getModel("amfollowup/rule")->load($history->getRuleId());
            return $this;
        }
        
        protected function getParams($params = array()){
            $params["id"] = $this->_history->getId();
            $params["key"] = $this->_history->getPublicKey();
            
            foreach($this->_googleAnalyticsParams as $param){
                $val = $this->_rule->getData($param);
                if (!empty($val)){
                    $params[$param] = $val;
                }
            }
            return $params;
        }
        public function unsubscribe(){
            return $this->get(Mage::getUrl('amfollowupfront/main/unsubscribe',
                $this->getParams()
            ));
        }
        
        public function get($target){
            return Mage::getUrl('amfollowupfront/main/url', $this->getParams(array(
                'target' => urlencode(base64_encode($target)),
            )));
        }
        
        public function mageUrl($url){
            return $this->get(Mage::getUrl($url));
        }
        
        public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false){
            return Mage::helper("core")->formatDate($date, $format, $showTime);
        }
    }
?>