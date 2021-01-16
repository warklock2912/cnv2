<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'imagehome';
        $this->_controller = 'adminhtml_imagehome';
        $this->_removeButton('save');

        //$this->_updateButton('delete', 'label', Mage::helper('imagehome')->__('Delete Item'));
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_addButton('save', array(
            'label' => Mage::helper('adminhtml')->__('Save'),
            'onclick' => 'saveAndContinueEdit_()',
            'class' => 'save',
                ), -99);

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_addButton('addnewwidget', array(
            'label' => Mage::helper('adminhtml')->__('Add new widget'),
            'onclick' => 'addnew()',
            'class' => 'addwidget',
                ), -200);
        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('imagehome_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'imagehome_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'imagehome_content');
				}
			}

			function saveAndContinueEdit(){
                        $('savewidget').click();
				editForm.submit($('edit_form').action+'back/edit/');
			}
                        function saveAndContinueEdit_(){
                        $('savewidget').click();
                        editForm.submit($('edit_form').action);
                      
			}
                        
                        function addnew(){
                       $('newwidget').click();
                        }
		";
    }

    public function getHeaderText() {
        if (Mage::registry('imagehome_data') && Mage::registry('imagehome_data')->getId()) {
            return Mage::helper('imagehome')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('imagehome_data')->getTitle()));
        } else {
            return Mage::helper('imagehome')->__('Add Item');
        }
    }

}
