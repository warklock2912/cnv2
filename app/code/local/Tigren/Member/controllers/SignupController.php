<?php

class Tigren_Member_SignupController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function formAction()
    {
       

        $this->loadLayout();
        $this->renderLayout();
    }
}