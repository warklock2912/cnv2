<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Categories_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
  protected function _prepareForm() {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('categories_form', array('legend' => Mage::helper('bannerads')->__('Item information')));

    if (Mage::getSingleton('adminhtml/session')->getCategoryData()) {
      $data = Mage::getSingleton('adminhtml/session')->getCategoryData();
      Mage::getSingleton('adminhtml/session')->setCategoryData(null);
    } elseif (Mage::registry('category_data')) {
      $data = Mage::registry('category_data')->getData();
    }

    $fieldset->addField('category_title', 'text', array('label' => Mage::helper('bannerads')->__('Title'), 'class' => 'required-entry', 'required' => TRUE, 'name' => 'category_title',));

    $fieldset->addField('category_description', 'editor', array('name' => 'category_description', 'label' => Mage::helper('bannerads')->__('Description'), 'title' => Mage::helper('bannerads')->__('Description'), 'style' => 'width:400px; height:250px;', 'wysiwyg' => TRUE, 'required' => FALSE, 'config' => TRUE,));

    $fieldset->addField('status', 'select', array('label' => Mage::helper('bannerads')->__('Status'), 'name' => 'status', 'values' => array(array('value' => 1, 'label' => Mage::helper('bannerads')->__('Enabled'),),

      array('value' => 2, 'label' => Mage::helper('bannerads')->__('Disabled'),),),));


    $form->setValues($data);
    return parent::_prepareForm();
  }
}
