<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Model_SalesRule_Segments extends Mage_Rule_Model_Condition_Abstract
{
    protected $_inputType = 'multiselect';

    public function getValueElementType()
    {
        return 'multiselect';
    }

    public function getValueSelectOptions()
    {
        $segments = Mage::getModel('amsegments/segment')->getResourceCollection()
                ->addFieldToFilter('is_active', 1)
                ->toOptionArray();

        $this->setData('value_select_options', $segments);

        return $segments;
    }

    public function asHtml()
    {
        $value = '';
        try{
            $value = $this->getValueElementHtml();
        } catch(Exception $e){

        }

        return $this->getTypeElementHtml()
            . Mage::helper('amsegments')->__(Mage::helper('amsegments')->__('Segments') . ' %s %s:', $this->getOperatorElementHtml(), $value)
            . $this->getRemoveLinkHtml();
    }

    protected function _isSegmentEnabled(){
        $ret = false;

        foreach($this->getValueSelectOptions() as $segment){
            if ($this->getValue() == $segment['value']){
                $ret = true;
            }
        }

        return $ret;
    }


    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
public function validate(Varien_Object $object)
    {
        $ret = false;
        $customerEmail = null;

        $customerId = $object->getCustomerId();

        if (!$customerId) {
            $customerEmail = $object->getEmail();
        }

        if ($customerEmail || $customerId) {

            $arrSegments = array();

            foreach($this->getValueSelectOptions() as $segment){
                $arrSegments[] = $segment['value'];
            }

            $collection = Mage::getModel("amsegments/index")
                   ->getCollection()
                   ->addResultSegmentsData($arrSegments);

            if ($customerId){
                $collection->addFieldToFilter('customer.customer_id', array('eq' => $customerId));
            } else if ($customerEmail){
                $collection->addFieldToFilter('customer.customer_email', array('eq' => $customerEmail));
            }

            $segments = $collection->getData();

            $ids = array();

            foreach($segments as $segment){
                $ids[] = $segment['segment_id'];
            }

            if (count($ids) > 0 ){
                $ret = $this->validateAttribute($ids);
            }
        }

        return $ret;
    }
}