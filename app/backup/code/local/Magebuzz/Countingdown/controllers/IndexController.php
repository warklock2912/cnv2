<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Countingdown_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Upcoming'));
        $this->renderLayout();
    }

}
