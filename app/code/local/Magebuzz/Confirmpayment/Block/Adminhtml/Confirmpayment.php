<?php

class Magebuzz_ConfirmPayment_Block_Adminhtml_Confirmpayment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_confirmpayment';
		$this->_blockGroup = 'confirmpayment';
		$this->_headerText = Mage::helper('confirmpayment')->__('Submitted Data');
		parent::__construct();
                $this->_removeButton('add');
	}
}