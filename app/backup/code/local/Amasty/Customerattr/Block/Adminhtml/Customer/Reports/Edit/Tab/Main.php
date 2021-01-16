<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Reports_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function getAttributeValues()
    {
        $model = Mage::registry('entity_attribute');
        $customerCollection = Mage::getResourceModel(
            'customer/customer_collection'
        );
        $customerCollection->addAttributeToSelect($model->getAttributeCode());
        $customerCollection->addAttributeToFilter(
            $model->getAttributeCode(), array('notnull' => true)
        );
        $customerValue = $customerCollection->getColumnValues(
            $model->getAttributeCode()
        );

        $guestCollection = Mage::getModel('amcustomerattr/guest')
            ->getCollection();
        $guestCollection->addFieldToFilter(
            $model->getAttributeCode(), array('notnull' => true)
        );
        $guestValue = $guestCollection->getColumnValues(
            $model->getAttributeCode()
        );

        $valuesAsString = array_merge($customerValue, $guestValue);

        $valuesAsArray = array();
        foreach ($valuesAsString as $attrIdsString) {
            $valuesAsArray = array_merge(
                $valuesAsArray, explode(',', $attrIdsString)
            );
        }

        $optionCollection = Mage::getResourceModel(
            'eav/entity_attribute_option_collection'
        )
            ->setAttributeFilter($model->getAttributeId())
            ->addFieldToFilter(
                'main_table.option_id', array('in' => $valuesAsArray)
            )
            ->setStoreFilter();

        $qtyValues = array_count_values($valuesAsArray);

        $result = array();

        $options = $optionCollection->toOptionArray();
        $sum = array_sum($qtyValues);
        foreach ($options as $value) {
            $qty = $qtyValues[$value['value']];
            $label = $value['label'] . ' - ' . $qty . ' (' . round(
                    ($qty / $sum) * 100, 1
                ) . '%)';
            $result[$value['value']] = array('qty' => $qty, 'label' => $label);
        }

        return $result;
    }

    public function showReports()
    {
        $model = Mage::registry('entity_attribute');
        $frontendInput = array('multiselect', 'select');
        if (in_array($model->getFrontendInput(), $frontendInput)) {
            return true;
        } else {
            return false;
        }

    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('amasty/amcustomerattr/reports.phtml');
    }

}