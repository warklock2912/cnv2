<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Submit_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amcustomform/submit/view.phtml');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getSubmit()
    {
        /** @var Amasty_Customform_Model_Form_Submit $submit */
        $submit = Mage::registry('amcustomform_current_submit');

        return $submit;
    }
    public function getHeaderText()
    {
        return $this->__('Billing Agreement #%s', 'Submit');
    }


    public function getCustomerLink(){
        $link = '';
        $submit = $this->getSubmit();
        $customerId = $submit->getCustomerId();
        if($customerId){
            $customer = Mage::getModel('customer/customer')->load($customerId);
        }
        if($customer->getId()){
            $link = "<a href='".$this->getUrl('*/customer/edit', array('id' => $customer->getId()))."'>".$customer->getFirstname()." ".$customer->getLastname()."</a>";
        }
        return $link;
    }

    protected function _isAllowed($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed($action);
    }
}