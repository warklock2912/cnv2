<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_MainController extends Mage_Core_Controller_Front_Action
{
    protected function _getHistory(){
        $ret = NULL;
        $historyId = $this->getRequest()->id;
        $key = $this->getRequest()->key;
        
        $history = Mage::getModel('amfollowup/history')->load($historyId);
        
        if ($history->getId() && $history->getPublicKey() == $key){
            $ret = $history;
        }
        
        return $ret;
    }
    
    public function urlAction(){
        
        $history = $this->_getHistory();
        
        $target = $this->getRequest()->target;
        
        if ($history && $target){
        
            $target = base64_decode($target);
            
            $link = Mage::getModel("amfollowup/link");
            $link->setData(array(
                "customer_id" => $history->getCustomerId(),
                "history_id" => $history->getId(),
                "link" => $target,
                "created_at" => date("Y-m-d H:i:s", time())
            ));
            $link->save();
            
            $this->_loginCustomer($history);
            $params = $this->getRequest()->getParams();
            unset($params['target']);
            
            foreach($params as $key => $val){
                $target .= (strpos($target, "?") !== FALSE ? "&" : "?") . $key . '='.$val;
            }
            
            Mage::app()->getFrontController()->getResponse()->setRedirect($target);
        } else {
            $this->_customRedirect("/");
        }   
    }
    
    public function unsubscribeAction()
    {
        $history = $this->_getHistory();
        if ($history){
            
            $history->unsubscribe();
            
            Mage::getSingleton('catalog/session')->addSuccess(Mage::helper('amfollowup')->__('You have been unsubscribed'));
        }
        
        $this->_customRedirect('checkout/cart');
    }
    
    protected function _customRedirect($path, $args = array()){
        $url = Mage::getUrl($path, $args);
        
        if (isset($_SERVER['QUERY_STRING']))
            $url .= "?".$_SERVER['QUERY_STRING'];
        
        $this->_redirectUrl($url);
    }
    
    protected function _loginCustomer($history){
        $s = Mage::getSingleton('customer/session');
        if ($s->isLoggedIn()){
            if ($history->getCustomerId() != $s->getCustomerId()){
                $s->logout();
            }                   
        }
        // customer. login
        if ($history->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());
            if ($customer->getId())
                $s->setCustomerAsLoggedIn($customer);
        }
    }
       
}

?>