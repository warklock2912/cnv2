<?php

	/*
	* Copyright (c) 2015 www.magebuzz.com
	*/

	class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
	{
		protected function _prepareForm()
		{
			$form = new Varien_Data_Form();
			$form->setHtmlIdPrefix('bannerads_');
			$this->setForm($form);
			$id = $this->getRequest()->getParam('id');
			$store_id = '';
			if ($id != '') {
				if (!Mage::app()->isSingleStoreMode()) {
					$storeList = Mage::getSingleton('bannerads/banneradsstore')->getCollection()->AddFieldToFilter('block_id', $id)->getData();
					foreach ($storeList as $store) {
						$store_id[] = $store['store_id'];
					}
				} else {
					$store_id = Mage::app()->getStore(TRUE)->getId();
				}

				Mage::registry('bannerads_data')->setData('store_id', $store_id);
			}
			$fieldset = $form->addFieldset('bannerads_form', array('legend' => Mage::helper('bannerads')->__('Block information')));
			$fieldset->addField('block_title', 'text', array('label' => Mage::helper('bannerads')->__('Title'), 'class' => 'required-entry', 'required' => TRUE, 'name' => 'block_title',));

			if (!Mage::app()->isSingleStoreMode()) {
				$field = $fieldset->addField('store_id', 'multiselect', array('name' => 'stores[]', 'label' => Mage::helper('bannerads')->__('Store View'), 'title' => Mage::helper('bannerads')->__('Store View'), 'required' => TRUE, 'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),));
				$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
				$field->setRenderer($renderer);
			} else {
				$fieldset->addField('store_id', 'hidden', array('name' => 'stores[]',));
				//$model->setStoreId();
			}

			$customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
			$found = FALSE;

			foreach ($customerGroups as $group) {
				if ($group['value'] == 0) {
					$found = TRUE;
				}
			}
			if (!$found) {
				array_unshift($customerGroups, array('value' => 0, 'label' => Mage::helper('bannerads')->__('NOT LOGGED IN')));
			}

			$fieldset->addField('customer_group_ids', 'multiselect', array('name' => 'customer_group_ids[]', 'label' => Mage::helper('bannerads')->__('Customer Groups'), 'title' => Mage::helper('bannerads')->__('Customer Groups'), 'required' => TRUE, 'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray(),));

			$fieldset->addField('block_position', 'select', array('name' => 'block_position', 'label' => Mage::helper('bannerads')->__('Block Position'), 'title' => Mage::helper('bannerads')->__('Block Position'), 'values' => Mage::helper('bannerads')->getPositionOptionsArray(), 'required' => TRUE,));

			$fieldset->addField('block_max_width', 'text', array('label' => Mage::helper('bannerads')->__('Block max width'), 'name' => 'block_max_width',));
			$fieldset->addField('from_date', 'date', array('label' => Mage::helper('bannerads')->__('From Date'), 'name' => 'from_date', 'format' => 'M/d/yyyy H:mm', 'time' => TRUE, 'image' => $this->getSkinUrl('images/grid-cal.gif'),));

			$fieldset->addField('to_date', 'date', array('label' => Mage::helper('bannerads')->__('To Date'), 'name' => 'to_date', 'format' => 'M/d/yyyy H:mm', 'time' => TRUE, 'image' => $this->getSkinUrl('images/grid-cal.gif'),));

			$displayType = Magebuzz_Bannerads_Model_Displaytype::getOptionArray();
			$fieldset->addField('display_type', 'select', array('label' => Mage::helper('bannerads')->__('Display Type'), 'name' => 'display_type', 'values' => $displayType));

			$fieldset->addField('status', 'select', array('label' => Mage::helper('bannerads')->__('Status'), 'name' => 'status', 'values' => array(array('value' => 1, 'label' => Mage::helper('bannerads')->__('Enabled'),),

			 array('value' => 2, 'label' => Mage::helper('bannerads')->__('Disabled'),),),));

			$fieldset->addField('sort_order', 'text', array('label' => Mage::helper('bannerads')->__('Sort Order'), 'name' => 'sort_order',));

			if (Mage::getSingleton('adminhtml/session')->getBanneradsData()) {
				$form->setValues(Mage::getSingleton('adminhtml/session')->getBanneradsData());
				Mage::getSingleton('adminhtml/session')->setBanneradsData(null);
			} elseif (Mage::registry('bannerads_data')) {
				$form->setValues(Mage::registry('bannerads_data')->getData());

			}
			return parent::_prepareForm();
		}
	}
