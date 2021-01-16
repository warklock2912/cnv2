<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_relation');

        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => Mage::helper('catalog')->__(
                'Relation Properties'
            ))
        );
        if ($model->getId()) {
            $fieldset->addField(
                'id', 'hidden', array(
                'name' => 'id',
            )
            );
        }
        $this->_addElementTypes($fieldset);

        /* @var $relationModel Amasty_Customerattr_Model_Relation */
        $relationModel = Mage::getModel('amcustomerattr/relation');

        /*
         * Get list only attributes
         */
        $attributes = $relationModel->getUserDefinedAttributes();


        $fieldset->addField(
            'name', 'text', array(
            'name'     => 'name',
            'label'    => Mage::helper('catalog')->__('Relation name'),
            'title'    => Mage::helper('catalog')->__('Relation name'),
            'note'     => Mage::helper('catalog')->__('For internal use'),
            'required' => true,
        )
        );

        $attributeValues = array();

        $relationDetails = Mage::registry('entity_relation_details');

        $attributeIds = array();
        $optionIds = array();
        $dependentAttributesIds = array();


        $currentAttributeId = null;

        if ($relationDetails && $relationDetails->count() > 0) {
            foreach ($relationDetails as $relation) {
                $optionIds[] = $relation->getOptionId();
                $attributeIds[] = $relation->getAttributeId();
                $dependentAttributesIds[]
                    = $relation->getDependentAttributeId();
            }
            $currentAttributeId = $attributeIds[0];
        } else {
            $currentAttributeId = $attributes[0]['value'];
        }

        $attributeValues = $relationModel->getAttributeValues(
            $currentAttributeId
        );

        $fieldset->addField(
            'attribute_id', 'select', array(
            'name'     => 'attribute_id',
            'label'    => Mage::helper('catalog')->__('Parent Attribute'),
            'title'    => Mage::helper('catalog')->__('Parent Attribute'),
            'values'   => $attributes,
            'value'    => $attributeIds,
            'required' => true,
        )
        );


        $fieldset->addField(
            'option_id', 'multiselect', array(
            'name'     => 'option_id',
            'label'    => Mage::helper('catalog')->__('Attribute Options'),
            'title'    => Mage::helper('catalog')->__('Attribute Options'),
            'values'   => $attributeValues,
            'value'    => $optionIds,
            'required' => true,
        )
        );

        /*
         * Get all user defined attributes
         */
        $attributes = $relationModel->getUserDefinedAttributes(false, false);

        /*
         * Unset Current Attribute
         */
        foreach ($attributes as $key => $attribute) {
            if ($attribute['value'] == $currentAttributeId) {
                unset($attributes[$key]);
            }
        }
        $fieldset->addField(
            'dependend_attribute_id', 'multiselect', array(
            'name'     => 'dependend_attribute_id',
            'label'    => Mage::helper('catalog')->__('Dependent Attributes'),
            'title'    => Mage::helper('catalog')->__('Dependent Attributes'),
            'values'   => $attributes,
            'value'    => $dependentAttributesIds,
            'required' => true,
        )
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
