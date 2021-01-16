<?php

class Crystal_BlockSlide_Block_Adminhtml_Blockslide_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm()
	{

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('blockslide_form', array(
				'legend' => Mage::helper('blockslide')->__('Slide information'))
		);


		$fieldset->addField('image', 'image', array(
			'label' => Mage::helper('blockslide')->__('Image'),
			'required' => true,
			'name' => 'image',
		));
		$fieldset->addField('position', 'text', array(
			'label' => Mage::helper('blockslide')->__('Position'),
			'required' => true,
			'name' => 'position',
		));
		$fieldset->addField('url', 'text', array(
			'label' => Mage::helper('blockslide')->__('URL'),
			'name' => 'url',
		));
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('blockslide')->__('Status'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'status',
			'values' => array(
				array('value' => 1, 'label' => Mage::helper('blockslide')->__('Enable'),),
				array('value' => 0, 'label' => Mage::helper('blockslide')->__('Disable'),),
			)
		));
		$data = Mage::registry('blockslide_data')->getData();
		if (isset($data['image']) && $data['image'] != '') {
			$data['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'blockslide/images/' . $data['image'];
		}


		if (Mage::getSingleton('adminhtml/session')->getBlockslideData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getBlockslideData());
			Mage::getSingleton('adminhtml/session')->setBlockslideData(null);
		} elseif (Mage::registry('blockslide_data')) {
			$form->setValues($data);
		}
		return parent::_prepareForm();
	}
}