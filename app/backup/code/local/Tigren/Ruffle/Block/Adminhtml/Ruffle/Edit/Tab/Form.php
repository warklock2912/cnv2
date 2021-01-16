<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
  	protected function _prepareForm() {
  		$model = Mage::registry('current_ruffle');
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('ruffle_form', array('legend'=>Mage::helper('ruffle')->__('Prize Information')));
	 
		$fieldset->addField('title', 'text', array(
			'label'     => Mage::helper('ruffle')->__('Title'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'title',
		));
	
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('start_date', 'date', array(
            'name'   => 'start_date',
            'required' => true,
            'label'  => Mage::helper('ruffle')->__('Start Date'),
            'title'  => Mage::helper('ruffle')->__('Start Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('end_date', 'date', array(
            'name'   => 'end_date',
            'required' => true,
            'label'  => Mage::helper('ruffle')->__('End Date'),
            'title'  => Mage::helper('ruffle')->__('End Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('announce_date', 'date', array(
            'name'   => 'announce_date',
            'required' => true,
            'label'  => Mage::helper('ruffle')->__('Announce Date'),
            'title'  => Mage::helper('ruffle')->__('Announce Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('announce_type', 'select', array(
            'label'     => Mage::helper('ruffle')->__('Announce Type'),
            'title'     => Mage::helper('ruffle')->__('Announce Type'),
            'name'      => 'announce_type',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('ruffle')->__('Online'),
                '0' => Mage::helper('ruffle')->__('Offline'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('announce_type', '1');
        }
      $fieldset->addField('available_day', 'text', array(
        'label'     => Mage::helper('ruffle')->__('Available Days can Buy'),
        'required'  => false,
        'name'      => 'available_day',
        'after_element_html' => '<p>If Null or 0 will unlimit day can buy</p>',
      ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('ruffle')->__('Status'),
            'title'     => Mage::helper('ruffle')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('ruffle')->__('Active'),
                '0' => Mage::helper('ruffle')->__('Inactive'),
            ),
        ));

        $fieldset->addField('is_all', 'select', array(
            'label'     => Mage::helper('ruffle')->__('Set Member for raffle'),
            'title'     => Mage::helper('ruffle')->__('Set Member for raffle'),
            'name'      => 'is_all',
            'required' => true,
            'options'    => array(
                '0' => Mage::helper('ruffle')->__('Member Or VIP Group'),
                '1' => Mage::helper('ruffle')->__('All Group'),
            ),
        ));
        $fieldset->addField('email_join_th', 'textarea', array(
            'label'     => Mage::helper('ruffle')->__('Footer Email After Join TH'),
            'required'  => false,
            'name'      => 'email_join_th',
            // 'after_element_html' => '<p>If Null or 0 will unlimit day can buy</p>',
        ));
        $fieldset->addField('email_join_en', 'textarea', array(
            'label'     => Mage::helper('ruffle')->__('Footer Email After Join EN'),
            'required'  => false,
            'name'      => 'email_join_en',
            // 'after_element_html' => '<p>If Null or 0 will unlimit day can buy</p>',
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
            $model->setData('is_all', '1');
        }
		
		if (Mage::getSingleton('adminhtml/session')->getRuffleData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getRuffleData());
			Mage::getSingleton('adminhtml/session')->setRuffleData(null);
		} elseif ( Mage::registry('current_ruffle') ) {
			$form->setValues(Mage::registry('current_ruffle')->getData());
		}
		return parent::_prepareForm();
  	}
}