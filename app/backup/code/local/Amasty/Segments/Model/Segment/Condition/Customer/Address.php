<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * Customer address attributes conditions combine
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Address
    extends Enterprise_CustomerSegment_Model_Condition_Combine_Abstract
{
    /**
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_customer_address');
    }

    /**
     * Get list of available subconditions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $prefix = 'enterprise_customersegment/segment_condition_customer_address_';
        $result = array_merge_recursive(parent::getNewChildSelectOptions(), array(
            array(
                'value' => $this->getType(),
                'label' => Mage::helper('enterprise_customersegment')->__('Conditions Combination')
            ),
            Mage::getModel($prefix.'default')->getNewChildSelectOptions(),
            Mage::getModel($prefix.'attributes')->getNewChildSelectOptions(),
        ));
        return $result;
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('If Customer Addresses match %s of these Conditions:', $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Condition presented without value select. Default value is "1"
     *
     * @return int
     */
    public function getValue()
    {
        return 1;
    }

    /**
     * Prepare base condition select which related with current condition combine
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $resource = $this->getResource();
        $select = $resource->createSelect();
        $addressEntityType = Mage::getSingleton('eav/config')->getEntityType('customer_address');
        $addressTable = $resource->getTable($addressEntityType->getEntityTable());

        $select->from(array('customer_address' => $addressTable), array(new Zend_Db_Expr(1)));
        $select->where('customer_address.entity_type_id = ?', $addressEntityType->getId());
        $select->where($this->_createCustomerFilter($customer, 'customer_address.parent_id'));

        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);

        return $select;
    }

}
