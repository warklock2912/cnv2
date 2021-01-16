<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


class Amasty_Segments_Model_Segment_Condition_Customer_Attributes
    extends Amasty_Segments_Model_Condition_Abstract
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('amsegments/segment_condition_customer_attributes');
        $this->setValue(null);
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
       
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value' => $this->getType() . '|' . $code, 'label' => $label);
        }

        return $conditions;
    }

    /**
     * Retrieve attribute object
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttributeObject()
    {
        return Mage::getSingleton('eav/config')->getAttribute('customer', $this->getAttribute());
    }

    /**
     * Load condition options for castomer attributes
     *
     * @return Amasty_Segments_Model_Segment_Condition_Customer_Attributes
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = Mage::getStoreConfig("amsegments/general/customer_attributes");
        
        $productAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();
 
        $attributes = array();

        foreach ($productAttributes as $attribute) {
            $label = $attribute->getFrontendLabel();
            if (!$label) {
                continue;
            }
            // skip "binary" attributes
            if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                continue;
            }
            // skip "binary" attributes
            if (in_array($attribute->getAttributeCode(), array('default_billing', 'default_shipping'))) {
                continue;
            }
            
            if (in_array($attribute->getAttributeCode(), explode(",", $customerAttributes))) {
                $attributes[$attribute->getAttributeCode()] = $label;
            }
        }
        
        asort($attributes);
        
        $this->setAttributeOption($attributes);
        
        return $this;
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options') && is_object($this->getAttributeObject())) {
            if ($this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $this->setData('value_select_options', $optionsArr);
            }
        }

        return $this->getData('value_select_options');
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    /**
     * Get attribute value input element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                    break;
            }
        }
        return $element;
    }

    /**
     * Chechk if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }
        return false;
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }
    /**
     * Customer attributes are standalone conditions, hence they must be self-sufficient
     *
     * @return string
     */
    public function asHtml()
    {
        return Mage::helper('amsegments')->__('Registered Customer %s', parent::asHtml());
    }

    /**
     * Return values of start and end datetime for date if operator is equal
     *
     * @return array|string
     */
    public function getDateValue()
    {
        if ($this->getOperator() == '==') {
            $dateObj = Mage::app()->getLocale()
                ->date($this->getValue(), Varien_Date::DATE_INTERNAL_FORMAT, null, false)
                ->setHour(0)->setMinute(0)->setSecond(0);
            $value = array(
                'start' => $dateObj->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
                'end' => $dateObj->addDay(1)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
            return $value;
        }
        return $this->getValue();
    }

    /**
     * Return date operator if original operator is equal
     *
     * @return string
     */
    public function getDateOperator()
    {
        if ($this->getOperator() == '==') {
            return 'between';
        }
        return $this->getOperator();
    }
    
    protected function _getResultExpr()
    {
        $attribute = $this->getAttributeObject();
        $alias = 'cust_attr_' . $attribute->getAttributeCode();
        
        return new Zend_Db_Expr("NOT ISNULL(" . $alias . ".entity_id) as result");
    }

    protected function _prepareSelect(&$select)
    {    
        $attribute = $this->getAttributeObject();
        
        $table = $attribute->getBackendTable();
        $alias = 'cust_attr_' . $attribute->getAttributeCode();
        
        $joinLeft = 'main_table.customer_id = ' . $alias . '.entity_id';
        
        $value    = $this->getValue();
        $operator = $this->getOperator();
        
        if ($attribute->isStatic()) {
            $field = $alias . ".{$attribute->getAttributeCode()}";
        } else {
            $joinLeft .= ' and ' . $alias . '.attribute_id = ' . $attribute->getId();
//            $select->where($alias . '.attribute_id = ?', $attribute->getId());
            $field = $alias . '.value';
        }
        $field = $select->getAdapter()->quoteColumnAs($field, null);

//        if ($attribute->getFrontendInput() == 'date') {
//            $value    = $this->getDateValue();
//            $operator = $this->getDateOperator();
//        }

        
        $condition = $this->getResource()->createConditionSql($field, $operator, $value);
        
        $joinLeft .= ' and ' . $condition;
        
        $select->joinLeft(
                array($alias => $table),
                $joinLeft,//'main_table.customer_id = ' . $alias . '.entity_id',
                array()
        );
    }
}
