<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

class Amasty_Segments_Model_Segment_Condition_Combine
    extends Mage_Rule_Model_Condition_Combine
{
    /**
     * Initialize model
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('amsegments/segment_condition_combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $hlr = Mage::helper('amsegments');
        $conditions = Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
                array(
                    // Subconditions combo
                    'value' => 'amsegments/segment_condition_combine',
                    'label' => $hlr->__('Conditions Combination')
                ),
                array('value' => array(

                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Daysfirstcomplete::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_daysfirstcomplete',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Dayslastcomplete::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_dayslastcomplete',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Payment_Method::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_payment_method',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Shipping_Method::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_shipping_method',
                        ),
//                        array(
//                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Avgsum::getDefaultLabel()),
//                            'value' => 'amsegments/segment_condition_order_avgsum',
//                        ),
//                        
//                        array(
//                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Country::getDefaultLabel()),
//                            'value' => 'amsegments/segment_condition_order_country',
//                        ),
//                        array(
//                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_City::getDefaultLabel()),
//                            'value' => 'amsegments/segment_condition_order_city',
//                        ),
//                        array(
//                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Email::getDefaultLabel()),
//                            'value' => 'amsegments/segment_condition_order_email',
//                        ),
                        array(
                            'label' => $hlr->__("Orders quantity by condition"),
                            'value' => 'amsegments/segment_condition_order_orders',
                        ),
                        array(
                            'label' => $hlr->__("Total amount by condition"),
                            'value' => 'amsegments/segment_condition_order_total',
                        ),
                        array(
                            'label' => $hlr->__("Ordered products by condition"),
                            'value' => 'amsegments/segment_condition_order_products',
                        )
                    ),
                    'label' => $hlr->__('Order'),
                ),
                array(
                    'label' => $hlr->__('Billing Address'),
                    'value' => array(
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_Email::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_billing_email',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_City::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_billing_city',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_State::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_billing_state',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_Country::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_billing_country',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_Zip::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_billing_zip',
                        ),
                    )
                ),
                array(
                    'label' => $hlr->__('Shipping Address'),
                    'value' => array(
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Shipping_City::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_shipping_city',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Shipping_State::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_shipping_state',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Shipping_Country::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_shipping_country',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Order_Address_Shipping_Zip::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_order_address_shipping_zip',
                        ),
                    )
                ),
                array('value' => array(

                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Cart_Daysfromcreated::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_cart_daysfromcreated',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Cart_Daysfrommodified::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_cart_daysfrommodified',
                        ),
//                        array(
//                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Cart_Daysfrommodified::getDefaultLabel()),
//                            'value' => 'amsegments/segment_condition_cart_daysfrommodified',
//                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Cart_Grandtotal::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_cart_grandtotal',
                        ),
                        array(
                            'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Cart_Productscount::getDefaultLabel()),
                            'value' => 'amsegments/segment_condition_cart_productscount',
                        ),
                    ),
                    'label' => $hlr->__('Cart'),
                ),
                Mage::getModel('amsegments/segment_condition_customer')->getNewChildSelectOptions(),
                array(
                    'label' => $hlr->__('Products'),
                    'value' => array(

                        array(
                            'label' => $hlr->__("Viewed products by condition"),
                            'value' => 'amsegments/segment_condition_product_viewed',
                        ),
                        array(
                            'label' => $hlr->__("Products in wishlist by condition"),
                            'value' => 'amsegments/segment_condition_product_wishlist',
                        ),
                    ),
                ),
            ));
            return $conditions;
    }
    
    public function getResource()
    {
        return Mage::getResourceSingleton('amsegments/segment');
    }
    
    public function process($websiteIds, $combineCondition = null){
        $conditionsCount = 0;
        foreach ($this->getConditions() as $condition) {
            $condition->process($websiteIds, $this);
            $conditionsCount++;
        }
        
        $collection = Mage::getModel("amsegments/customer")->getCollection()
                ->addIndexData($this->getRule()->getId(), $this->getId());
        
        
        $resultExpr = "0";
        
        if ($conditionsCount > 0 ) {
            if ($this->getValue() == 1){

                if ($this->getAggregator() == 'all'){
                    $resultExpr = '(FIND_IN_SET(0, GROUP_CONCAT(IFNULL(result, 0))) = 0';
                    $resultExpr .= ' AND COUNT(IFNULL(result, 0)) = ' . $conditionsCount . ')';
                } else {
                    $resultExpr = 'FIND_IN_SET(1, GROUP_CONCAT(IFNULL(result, 0))) <> 0';
                }

            } else {
                if ($this->getAggregator() == 'all'){
                    $resultExpr = '(FIND_IN_SET(1, GROUP_CONCAT(IFNULL(result, 0))) = 0';
                    $resultExpr .= ' AND COUNT(IFNULL(result, 0)) = ' . $conditionsCount . ' ) ';
                } else {
                    $resultExpr = '(FIND_IN_SET(0, GROUP_CONCAT(IFNULL(result, 0))) <> 0';
                    $resultExpr .= ' OR COUNT(IFNULL(result, 0)) <> ' . $conditionsCount . ')';
                }

            }
        } else {
            $resultExpr = new Zend_Db_Expr('true');
        }
        
        $adapter = $this->getResource()->getReadConnection();
        
       $select = $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition ? $combineCondition->getId() : null)),
                        "main_table.entity_id",
                        $resultExpr
                        ))
                    ->where("main_table.website_id IN (?) ", $websiteIds)
                    ->group("main_table.entity_id");
        
//        echo $select;
//        exit;
        $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/index')), array(
                "segment_id",
                "level",
                "parent",
                "customer_id",
                "result"
            ), FALSE);

        return $this->getResource()->query($sql);
    }
    
}
