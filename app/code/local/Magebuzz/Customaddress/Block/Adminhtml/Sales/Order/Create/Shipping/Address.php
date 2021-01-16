<?php
class Magebuzz_Customaddress_Block_Adminhtml_Sales_Order_Create_Shipping_Address extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Address {
	protected function _prepareForm() {
		parent::_prepareForm();
		
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