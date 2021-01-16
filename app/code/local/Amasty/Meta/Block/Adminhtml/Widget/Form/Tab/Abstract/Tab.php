<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
abstract class Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Tab extends Mage_Adminhtml_Block_Widget_Form
{
	protected $_prefix = '';
	protected $_title = '';
	protected $_fieldsetId = '';

	protected abstract function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldset);

	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$model = Mage::registry('ammeta_config');

		$fieldSet = $form->addFieldset(
			$this->_fieldsetId,
			array('legend' => $this->_title)
		);

		$this->_addFieldsToFieldset($fieldSet);

		//set form values
		$form->setValues($model->getData());

		return parent::_prepareForm();
	}
}