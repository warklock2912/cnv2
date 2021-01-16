<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Order_Create_Form_Attributes extends Mage_Adminhtml_Block_Template
{
    protected $_entityTypeId;

    protected $_formElements = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amcustomerattr/create_attributes.phtml');
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
    }

    public function getFormElements()
    {

        if ($this->_formElements)
        {
            return $this->_formElements;
        }
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $customer = $quote->getCustomer();
        if(!$customer->getId()){
            $parentOrderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId();
            $customer = Mage::getModel('amcustomerattr/guest')->load(
                $parentOrderId, 'order_id'
            );
        }

        $customerData = $customer->getData();

        $attributes = Mage::getModel('customer/attribute')->getCollection();

        $filters = array(
            "is_user_defined = 1",
            "entity_type_id = " . Mage::getModel('eav/entity')
                ->setType('customer')->getTypeId()
        );

        $attributes = Mage::helper('amcustomerattr')->addFilters($attributes, 'eav_attribute', $filters);

        $sorting = 'sorting_order';
        $filters = array("on_order_view = 1");
        $attributes = Mage::helper('amcustomerattr')->addFilters(
            $attributes, 'customer_eav_attribute', $filters, $sorting
        );

        $attributes->load();
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('amcustomerattr', array());

        $formData = array();

        foreach ($attributes as $attribute)
        {

            $currentStore = Mage::getSingleton('adminhtml/session_quote')->getStore()->getId();
            $storeIds = explode(',', $attribute->getData('store_ids'));
            if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
            {
                continue;
            }

            if ($inputType = $attribute->getFrontend()->getInputType())
            {
                $afterElementHtml = '';
                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                // applying translations
                $attributeLabel = $attribute->getFrontendLabel();

                $elementOptions=  array(
                    'name'      => 'amcustomerattr['.$attribute->getAttributeCode().']',
                    'label'     => $attributeLabel,
                    'class'     => $attribute->getFrontend()->getClass(),
                    'required'  => $attribute->getIsRequired(),
                );

                if('checkboxes'==$inputType || 'radios'==$inputType) {
                    $elementOptions['name']  .= '[]';
                    $formData[$attribute->getAttributeCode()] = explode(',', $customerData[$attribute->getAttributeCode()]);
                    $elementOptions['values'] = $attribute->getSource()->getAllOptions(false, true);
                }else{
                    if(isset($customerData[$attribute->getAttributeCode()])){
                        $formData[$attribute->getAttributeCode()] = $customerData[$attribute->getAttributeCode()];
                    }

                }


                if ('date' == $inputType) {

                    $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                    $elementOptions= array(
                        'name' => 'amcustomerattr['.$attribute->getAttributeCode().']',
                        'label' => $attributeLabel,
                        'title' => $attributeLabel,
                        'image' => $this->getSkinUrl('images/grid-cal.gif'),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'format' => $dateFormatIso,
                        'readonly' => 1,
                        'onclick' => 'amcustomerattr_trig(' . $attribute->getAttributeCode() . '_trig)',
                    );
                    if ('time' == $attribute->getNote()) {
                        $elementOptions['time'] = true;
                        $elementOptions['format'] .= ' HH:mm';
                    }
                    $afterElementHtml .= '<script type="text/javascript">'
                        . 'function amcustomerattr_trig(id)'
                        . '{ $(id).click(); }'
                        . '</script>';
                }

                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType, $elementOptions)
                    ->setEntityAttribute($attribute);
                $element->setAfterElementHtml($afterElementHtml);
                if ($inputType == 'radios' || $inputType == 'select' || $inputType == 'multiselect' || $inputType == 'boolean') {
                    // getting values translations
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($currentStore, false)
                        ->load();

                    foreach ($valuesCollection as $item) {
                        $values[$item->getId()] = $item->getValue();
                    }

                    // applying translations
                    $options = $attribute->getSource()->getAllOptions(true, true);
                    foreach ($options as $i => $option)
                    {
                        if (isset($values[$option['value']]))
                        {
                            $options[$i]['label'] = $values[$option['value']];
                        }
                    }

                    $element->setValues($options);
                }
            }
        }

        $form->setValues($formData);

        $this->_formElements = $form->getElements();
        return $this->_formElements;
    }



}
