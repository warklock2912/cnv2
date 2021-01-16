<?php
class Magebuzz_Customaddress_Block_Adminhtml_Sales_Order_Create_Billing_Address extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Address {	
	protected function _prepareForm() {
		//$this->setJsVariablePrefix('billingAddress');
		parent::_prepareForm();
			
		// $this->_form->addFieldNameSuffix('order[billing_address]');
		// $this->_form->setHtmlNamePrefix('order[billing_address]');
		// $this->_form->setHtmlIdPrefix('order-billing_address_');
		
		$cityElement = $this->_form->getElement('city');
    $cityElement->setRequired(true);

    if ($cityElement) {
      $cityElement->addClass('select');
      $cityElement->setRenderer($this->getLayout()->createBlock('customaddress/adminhtml_customer_edit_renderer_city'));
    }
		
		$cityElement = $this->_form->getElement('city_id');
    if ($cityElement) {
      $cityElement->setNoDisplay(true);
    }
		
		$subdistrictElement = $this->_form->getElement('subdistrict');
    if ($subdistrictElement) {
      $subdistrictElement->setRequired(true);
			$subdistrictElement->setRenderer($this->getLayout()->createBlock('customaddress/adminhtml_customer_edit_renderer_subdistrict'));
    }

    $subdistrictElement = $this->_form->getElement('subdistrict_id');
    if ($subdistrictElement) {
      $subdistrictElement->setNoDisplay(true);
    }

		return $this;
	}
}