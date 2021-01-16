<?php

class Tigren_Member_SearchController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function formAction()
    {

        $this->loadLayout();
        $this->renderLayout();
    }
    public function check_emailAction()
    {
        $email_search = $this->getRequest()->getParam('email_search');
        // $email_search = 'test008@test.com';
        $customer_search = Mage::getModel("customer/customer")->getCollection()
                       ->addAttributeToFilter('email', $email_search)
                       ->getFirstItem();
        if($customer_search->getData()){
            $customer = Mage::getModel('customer/customer')->load($customer_search->getEntityId());
            $customerArray = $customer->getData();
            $customerArray['point_balance'] = $this->getPoint($customer->getId());
            Mage::getSingleton('customer/session')->setSearchmember($customerArray);
            $data_send = 1;
        }else{
            $data_send = 0;
        }
        $this->getResponse()->setBody(json_encode($data_send));


    }
    public function check_member_idAction()
    {
        $vip_member_id = $this->getRequest()->getParam('mid_search');
        // $vip_member_id = '0018061300005';
        $customer_search = Mage::getModel("customer/customer")->getCollection()
                       // ->addAttributeToFilter('vip_member_id', $vip_member_id)-load();
                       ->addFieldToFilter('vip_member_id', $vip_member_id)->getFirstItem()
                       ;
       // echo '<pre>',print_r($customer_search->getData(),1),'</pre>'; die;
        if($customer_search->getData()){
            $customer = Mage::getModel('customer/customer')->load($customer_search->getEntityId());
            $customerArray = $customer->getData();
            $customerArray['point_balance'] = $this->getPoint($customer->getId());
            Mage::getSingleton('customer/session')->setSearchmember($customerArray);
            $data_send = 1;
        }else{
            $data_send = 0;
        }
        $this->getResponse()->setBody(json_encode($data_send));


    }
    public function check_mobileAction()
    {
    	$moblie_search = $this->getRequest()->getParam('moblie_search');
    	// $vip_member_id = '0018061300005';
        $customer_search = Mage::getModel("customer/customer")->getCollection()
                       // ->addAttributeToFilter('vip_member_id', $vip_member_id)-load();
                       ->addFieldToFilter('telephone', $moblie_search)->getFirstItem();
                       ;
       // echo '<pre>',print_r($customer_search->getData(),1),'</pre>';
        if($customer_search->getData()){
	        $customer = Mage::getModel('customer/customer')->load($customer_search->getEntityId());
            $customerArray = $customer->getData();
            $customerArray['point_balance'] = $this->getPoint($customer->getId());
	        Mage::getSingleton('customer/session')->setSearchmember($customerArray);
	        $data_send = 1;
        }else{
        	$data_send = 0;
        }
        $this->getResponse()->setBody(json_encode($data_send));


    }

    private function getPoint($customerId){
    // $customerId = 30846;
    $readpoint = Mage::getSingleton('core/resource')->getConnection('core_read');
    $point = $readpoint->fetchOne("select point_balance from rewardpoints_customer WHERE customer_id=:customer_id;", ['customer_id' => $customerId]);
    return $point ?: 0;
    }

}