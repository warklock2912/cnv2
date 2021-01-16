<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_IndexController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {			
		$this->loadLayout();     
		$this->renderLayout();
  }
	
	public function testAction() {
		$text = 'ace#000';

        $this->getResponse()->setBody(urlencode($text));
	}
}