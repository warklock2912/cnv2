<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Block_Adminhtml_Countingdown_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
  protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('countingdown_form', array('legend'=>Mage::helper('countingdown')->__('Item information')));
	 
		$fieldset->addField('title', 'text', array(
			'label'     => Mage::helper('countingdown')->__('Title'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'title',
		));

		$fieldset->addField('filename', 'file', array(
			'label'     => Mage::helper('countingdown')->__('File'),
			'required'  => false,
			'name'      => 'filename',
		));
	
		$fieldset->addField('status', 'select', array(
			'label'     => Mage::helper('countingdown')->__('Status'),
			'name'      => 'status',
			'values'    => array(
				array(
					'value'     => 1,
					'label'     => Mage::helper('countingdown')->__('Enabled'),
				),
				array(
					'value'     => 2,
					'label'     => Mage::helper('countingdown')->__('Disabled'),
				),
			),
		));
	 
		$fieldset->addField('content', 'editor', array(
			'name'      => 'content',
			'label'     => Mage::helper('countingdown')->__('Content'),
			'title'     => Mage::helper('countingdown')->__('Content'),
			'style'     => 'width:700px; height:500px;',
			'wysiwyg'   => false,
			'required'  => true,
		));
	 
		if (Mage::getSingleton('adminhtml/session')->getCountingdownData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getCountingdownData());
			Mage::getSingleton('adminhtml/session')->setCountingdownData(null);
		} elseif ( Mage::registry('countingdown_data') ) {
			$form->setValues(Mage::registry('countingdown_data')->getData());
		}
		return parent::_prepareForm();
  }
}