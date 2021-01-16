<?php

class Omise_Gateway_TestController extends Mage_Core_Controller_Front_Action
{
	public function testAction(){
		$token = Mage::getModel('omise_gateway/token');
		$customer = Mage::getSingleton('customer/session');
		
		$customer_id = $customer->getCustomerId();
            $lasttoken = $token->load($customer_id,'customer_id');
        $this->getResponse()->setBody($customer->getCustomerId());
	}

}