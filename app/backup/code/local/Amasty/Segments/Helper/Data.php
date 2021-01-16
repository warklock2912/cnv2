<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Helper_Data extends Mage_Core_Helper_Abstract
{
    function getSegmentStatuses(){
        return array(
                Amasty_Segments_Model_Segment::STATUS_ACTIVE => $this->__('Active'),
                Amasty_Segments_Model_Segment::STATUS_INACTIVE => $this->__('Inactive'),
            ); 
    }
    
    public function getSqlOperator($operator)
    {
        /*
            '{}'  => Mage::helper('rule')->__('contains'),
            '!{}' => Mage::helper('rule')->__('does not contain'),
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of'),
            requires custom selects
        */

        switch ($operator) {
            case '==':
                return '=';
            case '!=':
                return '<>';
            case '{}':
                return 'LIKE';
            case '!{}':
                return 'NOT LIKE';
            case '()':
                return 'IN';
            case '!()':
                return 'NOT IN';
            case '[]':
                return 'FIND_IN_SET(%s, %s)';
            case '![]':
                return 'FIND_IN_SET(%s, %s) IS NULL';
            case 'between':
                return 'BETWEEN %s AND %s';
            case '>':
            case '<':
            case '>=':
            case '<=':
                return $operator;
            default:
                Mage::throwException(Mage::helper('amsegments')->__('Unknown operator specified.'));
        }
    }
}