<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

class Amasty_Segments_Model_Segment_Condition_Customer
    extends Amasty_Segments_Model_Condition_Abstract
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('amsegments/segment_condition_customer');
        $this->setValue(null);
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $hlr = Mage::helper('amsegments');
        
        $conditions = array(
            array(
                    'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Customer_Subscriber::getDefaultLabel()),
                    'value' => 'amsegments/segment_condition_customer_subscriber',
                ),
            array(
                    'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Customer_Days_Visit::getDefaultLabel()),
                    'value' => 'amsegments/segment_condition_customer_days_visit',
                ),
            array(
                    'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Customer_Days_Registration::getDefaultLabel()),
                    'value' => 'amsegments/segment_condition_customer_days_registration',
                ),
            array(
                    'label' => $hlr->__(Amasty_Segments_Model_Segment_Condition_Customer_Days_Birthday::getDefaultLabel()),
                    'value' => 'amsegments/segment_condition_customer_days_birthday',
                )
        );
        
        
        $prefix = 'amsegments/segment_condition_customer_';
        $conditions = array_merge_recursive($conditions, Mage::getModel($prefix.'attributes')->getNewChildSelectOptions());

        return array(
            'value' => $conditions,
            'label'=>Mage::helper('amsegments')->__('Registered Customers')
        );
    }
}
