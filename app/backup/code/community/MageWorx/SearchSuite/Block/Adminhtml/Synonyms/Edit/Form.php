<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Synonyms_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Init Form properties
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('synonym_form');
    }

    protected function _prepareForm() {
        $model = Mage::registry('current_catalog_search');


        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'method' => 'post',
        ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('catalog')->__('General Information')));

        if ($model->getId()) {
            $fieldset->addField('query_id', 'hidden', array(
                'name' => 'query_id',
            ));
        }

        $fieldset->addField('query_text', 'text', array(
            'name' => 'query_text',
            'label' => Mage::helper('catalog')->__('Search Query'),
            'title' => Mage::helper('catalog')->__('Search Query'),
            'required' => true,
        ));

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
                'name' => 'store_id'
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
