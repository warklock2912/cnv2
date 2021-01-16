<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Field_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amcustomform/field/options.phtml');
    }

    protected function _prepareLayout()
    {
        $deleteButton = $this->getLayout()->createBlock('adminhtml/widget_button');
        $deleteButton->setData(array(
            'label' => Mage::helper('catalog')->__('Delete'),
            'class' => 'delete delete-option'
        ));
        $this->setChild('delete_button', $deleteButton);

        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button');
        $addButton->setData(array(
            'label' => Mage::helper('catalog')->__('Add Option'),
            'class' => 'add',
            'id'    => 'add_new_option_button'
        ));
        $this->setChild('add_button', $addButton);

        return parent::_prepareLayout();
    }

    public function getStores()
    {
        /** @var Mage_Core_Model_Resource_Store_Collection $stores */
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }

    public function getOptionValues()
    {
        $result = array();

        $fieldOptionCollection = $this->getField()->getFieldOptions();
        foreach ($fieldOptionCollection as $option) {
            /** @var Amasty_Customform_Model_Field_Option $option */
            $optionResult = $option->getData();

            foreach ($this->getStores()->getAllIds() as $storeId)
            {
                /** @var Amasty_Customform_Model_Field_Option_Store $storeData */
                $optionResult['label_store' . $storeId] = $option->getLabel($storeId);
            }

            $result[] = json_encode($optionResult);
        }

        return $result;
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return Amasty_Customform_Model_Field
     */
    protected function getField()
    {
        return Mage::registry('amcustomform_current_field');
    }

}