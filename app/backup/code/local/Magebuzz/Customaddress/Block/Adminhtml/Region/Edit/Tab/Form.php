<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_Region_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
  protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$countries = Mage::getSingleton('directory/country')->getCollection()
			->loadData()->toOptionArray(false);
		$id = $this->getRequest()->getParam('region_id');

		$fieldSet = $form->addFieldset('region_form', array('legend' => Mage::helper('customaddress')->__('State information')));
		
		// $fieldSet->addField('country_id', 'select', 
			// array(
				// 'label'    => Mage::helper('customaddress')->__('Country'),
				// 'name'     => 'country_id',
				// 'required' => true,
				// 'values'   => $countries
			// )
		// );
		
		$fieldSet->addField('country_id', 'text', 
			array(
				'label'    => Mage::helper('customaddress')->__('Country'),
				'name'     => 'country_id',
				'required' => true,
				'readonly' => true
			)
		);

		$fieldSet->addField('code', 'text', 
			array(
				'label'    => Mage::helper('customaddress')->__('Code'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'code',
			)
		);
		$fieldSet->addField('default_name', 'text', 
			array(
				'label'    => Mage::helper('customaddress')->__('Default Name'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'default_name',
			)
		);
		
		$locales = Mage::helper('customaddress')->getLocales();
		foreach ($locales as $locale) {
			$fieldSet{$locale} = $form->addFieldset('customaddress_form_' . $locale, array('legend' => Mage::helper('customaddress')->__('Locale ' . $locale)));
			$fieldSet{$locale}->addField(
				'name_'.$locale, 'text', 
				array(
					'label' => Mage::helper('customaddress')->__('Name'),
					'name'  => 'name_'.$locale,
				)
			);
		}
		
		if (Mage::getSingleton('adminhtml/session')->getRegionData()) {
			$data = Mage::getSingleton('adminhtml/session')->getRegionData(); 
			if (!isset($data['country_id'])) $data['country_id'] = 'TH';
			$form->setValues($data);
			Mage::getSingleton('adminhtml/session')->setRegionData(null);
		} elseif (Mage::registry('region_data')) {
			$data = Mage::registry('region_data')->getData(); 
			if (!isset($data['country_id'])) $data['country_id'] = 'TH';
			$form->setValues($data);
		}
		
		if ($id) {
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');
			$regionName = $resource->getTableName('directory/country_region_name');
			$select = $read->select()->from(array('region'=>$regionName))->where('region.region_id=?', $id);
			$data =$read->fetchAll($select);
			foreach($data as $row) {
				$form->addValues(array('name_'.$row['locale']=> $row['name']));
			}
		}
		return parent::_prepareForm();

	}
}