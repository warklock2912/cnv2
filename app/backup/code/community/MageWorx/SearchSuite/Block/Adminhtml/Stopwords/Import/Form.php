<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Stopwords_Import_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Init Form properties
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('stopwords_upload');
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/upload'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('catalog')->__('General Information')));


        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'select', array(
                'name' => 'store_id',
                'label' => Mage::helper('catalog')->__('Store'),
                'title' => Mage::helper('catalog')->__('Store'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
                'required' => true,
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }
        $fieldset->addField('file', 'file', array(
            'name' => 'file',
            'label' => Mage::helper('mageworx_searchsuite')->__('Select File to Import (*.txt)'),
            'title' => Mage::helper('mageworx_searchsuite')->__('Select File to Import (*.txt)'),
            'required' => true,
        ));

        $form->setValues(Mage::getSingleton('adminhtml/session')->getPageData(true));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
