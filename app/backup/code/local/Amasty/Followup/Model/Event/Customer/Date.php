<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Date extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
        }

        protected function _initCollection(){

            $resource = Mage::getSingleton('core/resource');

            $collection = Mage::getModel('customer/customer')->getCollection();

            $collection->addNameToSelect();

            $today = Mage::getModel('core/date')->date('Y-m-d');

            $collection->getSelect()->joinInner(
                array('rule' => $resource->getTableName('amfollowup/rule')),
                'rule.rule_id = ' . $this->_rule->getId() . ' and '.
                $collection->getConnection()->quoteInto('rule.customer_date_event = ?', $today),
                array()
            );

            $collection->getSelect()->joinLeft(
                array('history' => $resource->getTableName('amfollowup/history')),
                'e.entity_id = history.customer_id and '.
                'history.rule_id = ' . $this->_rule->getId(),
                array()
            );

            $collection->getSelect()->where("history.history_id is null");

            $segments = $this->_rule->getSegments();

            if (Mage::getConfig()->getNode('modules/Amasty_Segments/active') == 'true' &&
                !empty($segments)){

                $arrSegments = explode(',', $segments);

                $segmentCollection = Mage::getModel("amsegments/index")
                    ->getCollection()
                    ->addResultSegmentsData($arrSegments);

                $segmentCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);

                $segmentCollection
                    ->getSelect()
                    ->columns('customer.customer_id as target_customer_id');


                $idsSelect = "select DISTINCT target_customer_id from (" . $segmentCollection->getSelect()->__toString() . ") as tmp";


                $collection->getSelect()->where("e.entity_id in (" .
                    $idsSelect
                    . ")");
            }

            return $collection;
        }
    }
?>