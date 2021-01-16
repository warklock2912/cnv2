<?php
class Magebuzz_Customaddress_Block_Adminhtml_System_Config_Button extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected function _construct() {
		parent::_construct();
		$this->setTemplate('customaddress/system/config/button.phtml');
	}
	
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		return $this->_toHtml();
	}
	
	public function getAjaxCheckUrl() {
			return Mage::helper('adminhtml')->getUrl('customaddress/adminhtml_data/import');
	}

	/**
	 * Generate button html
	 *
	 * @return string
	 */
	public function getButtonHtml() {
		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
			'id'        => 'customaddress_button',
			'label'     => $this->helper('adminhtml')->__('Import Thai Address Data'),
			'onclick'   => 'javascript:import_thai_address(); return false;'
		));

		return $button->toHtml();
	}	
}