<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Combine extends Mage_CatalogRule_Model_Rule_Condition_Combine
{
    protected $_value;

    public function __construct()
    {
        parent::__construct();
        $this->setType('dynamic_category/rule_condition_combine');
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml().'<ul id="'.$this->getPrefix().'__'.$this->getId().'__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            /** @var Mage_Rule_Model_Condition_Abstract $cond */
            try {
                $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
            } catch (Exception $e) {
                $html .= sprintf(
                    '<li>%s&nbsp;<span class="error">%s</span>%s</li>',
                    $cond->getAttributeName(),
                    $e->getMessage(),
                    $cond->getRemoveLinkHtml()
                );
            }
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';

        return $html;
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('dynamic_category/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array('value' => 'dynamic_category/rule_condition_product|' . $code, 'label' => $label);
        }
        $conditions = Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions();

        $conditions = array_merge_recursive($conditions, array(
            array(
                'label' => Mage::helper('catalogrule')->__('Conditions Combination'),
                'value' => 'dynamic_category/rule_condition_combine',
            ),
            array(
                'label' => Mage::helper('dynamic_category')->__('Special Product Condition'),
                'value' => array(
                    array(
                        'value' => 'dynamic_category/rule_condition_product_salable|is_salable',
                        'label' => Mage::helper('dynamic_category')->__('Is Salable'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_promo|in_promo',
                        'label' => Mage::helper('dynamic_category')->__('In Promo'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_new|is_new',
                        'label' => Mage::helper('dynamic_category')->__('Is New'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_created|created_at',
                        'label' => Mage::helper('dynamic_category')->__('Created'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_quantity|quantity',
                        'label' => Mage::helper('dynamic_category')->__('Quantity In Stock'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_stock|stock',
                        'label' => Mage::helper('dynamic_category')->__('In Stock'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_price_special_applied|price_special_applied',
                        'label' => Mage::helper('dynamic_category')->__('Special Price Applied'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_type|product_type',
                        'label' => Mage::helper('dynamic_category')->__('Product Type'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_hasImage|has_image',
                        'label' => Mage::helper('dynamic_category')->__('Has Image'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_totalChildQty|total_child_products_qty',
                        'label' => Mage::helper('dynamic_category')->__('Total Child Products Quantity In Stock'),
                    ),
                    array(
                        'value' => 'dynamic_category/rule_condition_product_parent|product_parent',
                        'label' => Mage::helper('dynamic_category')->__('Replace Matching Simple Products By Parent Products'),
                    ),
                ),
            ),
            array(
                'label' => Mage::helper('catalogrule')->__('Product Attribute'),
                'value' => $attributes,
            ),
        ));

        return $conditions;
    }

    public function validate(Varien_Object $object)
    {
        $conds = $this->getConditions();
        if (!$conds) {
            return true;
        }

        $all    = $this->getAggregator() === 'all';
        $true   = (bool) $this->getValue();

        foreach ($conds as $cond) {
            $validated = $cond->validate($object);

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }

        return $all ? true : false;
    }

    public function getValue()
    {
        if (null === $this->_value) {
            $this->_value = parent::getValue();
        }

        return $this->_value;
    }

    public function getPrefix()
    {
        return $this->_getData('prefix');
    }

    public function getConditions()
    {
        $prefix = $this->getPrefix();

        return $this->_getData($prefix ? $prefix : 'conditions');
    }

    public function getAggregator()
    {
        return $this->_getData('aggregator');
    }
}
