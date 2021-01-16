<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_Subdistrict_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
  protected $_regionOptions = null; 
  protected $_cityOptions = null; 
	
	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		$cities = $this->getThaiCities();
		
		$fieldSet = $form->addFieldset('customaddress_form', array('legend'=>Mage::helper('customaddress')->__('City Information')));
	 
		$fieldSet->addField('city_id', 'select', 
			array(
				'label'    => Mage::helper('customaddress')->__('City'),
				'name'     => 'city_id',
				'required' => true,
				'values'   => $cities
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
		
		$fieldSet->addField('zipcode', 'text', 
			array(
				'label'    => Mage::helper('customaddress')->__('Zip Code'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'zipcode',
			)
		);
	 
		if (Mage::getSingleton('adminhtml/session')->getSubdistrictData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getSubdistrictData());
			Mage::getSingleton('adminhtml/session')->setSubdistrictData(null);
		} elseif ( Mage::registry('subdistrict_data') ) {
			$form->setValues(Mage::registry('subdistrict_data')->getData());
		}
		return parent::_prepareForm();
  }
	
	public function getThaiRegions() {
		if ($this->_regionOptions == null) {
			$this->_regionOptions[] = array('value' => '', 'label' => '');
			$regions = Mage::getSingleton('customaddress/region')->getCollection()
				->addFieldToFilter('country_id', 'TH');
			foreach ($regions as $region) {
				$this->_regionOptions[] = array(
					'value' => $region->getRegionId(), 
					'label' => $region->getDefaultName()
				);
			}
		}
		return $this->_regionOptions;		
	}
	
	public function getThaiCities() {
		if ($this->_cityOptions == null) {
			$this->_cityOptions[] = array('value' => '', 'label' => '');
			$regions = Mage::getSingleton('customaddress/city')->getCollection();
				//->addFieldToFilter('country_id', 'TH');
			foreach ($regions as $region) {
				$this->_cityOptions[] = array(
					'value' => $region->getCityId(), 
					'label' => $region->getDefaultName()
				);
			}
		}
		return $this->_cityOptions;		
	}
}