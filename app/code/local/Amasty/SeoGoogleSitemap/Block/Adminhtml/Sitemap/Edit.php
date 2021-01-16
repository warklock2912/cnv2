<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'amseogooglesitemap';
        $this->_controller = 'adminhtml_sitemap';
        
        $this->_updateButton('save', 'label', Mage::helper('amseogooglesitemap')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('amseogooglesitemap')->__('Delete'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
	public function getHeaderText()
    {
        if(Mage::registry('am_sitemap_profile') && Mage::registry('am_sitemap_profile')->getId() ) {
            return Mage::helper('amseogooglesitemap')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('am_sitemap_profile')->getTitle()));
        } else {
            return Mage::helper('amseogooglesitemap')->__('Add Item');
        }
    }
}