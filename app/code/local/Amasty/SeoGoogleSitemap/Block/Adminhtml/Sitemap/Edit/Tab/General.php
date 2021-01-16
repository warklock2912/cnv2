<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
	    $this->setForm($form);
	    $fieldset = $form->addFieldset('amseogooglesitemap_form_general', array('legend' => Mage::helper('amseogooglesitemap')->__('General')));
	    
	    /* @var $helper Amasty_Seo_Helper_Data */
	    $helper = Mage::helper('amseogooglesitemap');
	     
	    $model = Mage::registry('am_sitemap_profile');
	    
	    $fieldset->addField('title', 'text', array(
	          'label'     => Mage::helper('amseogooglesitemap')->__('Name'),
	          'class'     => 'required-entry',
	          'required'  => true,
	          'name'      => 'title',
	    ));
	    
		
        $fieldset->addField('stores', 'select', array(
            'label'     => $helper->__('Stores'),
            'name'      => 'stores',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()            
        ));  
        
	    
	    $fieldset->addField('folder_name', 'text', 
	    	array(
		        'label'     => Mage::helper('amseogooglesitemap')->__('Path to sitemap file'),
		        'class'     => 'required-entry',
		        'required'  => true,
		        'name'      => 'folder_name',
				'note'     => Mage::helper('amseogooglesitemap')->__('Like "sitemap/sitemap1.xml". Make sure path is writable and accessible through internet'),
	    	)
	    );
	    
        $fieldset->addField('max_items', 'text', array(
	        'label'     => $helper->__('Max Items Per File'),
	        'name'      => 'max_items',
        	'note' => $helper->__('If exceed, index file will be created. Read more at https://support.google.com/webmasters/answer/71453?hl=en')
	    ));

		$fieldset->addField('max_file_size', 'text', array(
			'label'     => $helper->__('Max File Size (kB)'),
			'name'      => 'max_file_size',
			'note' => $helper->__('If exceed, index file will be created. Read more at https://support.google.com/webmasters/answer/71453?hl=en')
		));

		$fieldset->addField('exclude_urls', 'textarea', array(
			'label' => $helper->__('Exclude URLs'),
			'name' => 'exclude_urls',
			'note' => $helper->__('URL to exclude, one per line')
		));
	    
		$form->setValues($model->getData());
         
	    return parent::_prepareForm();
	}
}