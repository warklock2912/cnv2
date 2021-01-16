<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Segment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amsegments/segment', 'segment_id');
    }
    
    function query($sql){
//        echo $sql . "<br/><br/>";
        return $this->_getWriteAdapter()->query($sql);
    }
    
    public function createSelect()
    {
        return $this->_getReadAdapter()->select();
    }

    function clearIndex($segment){
        
        $this->_getWriteAdapter()->delete(
            $this->getTable('amsegments/index'),
            array('segment_id=?' => $segment->getId())
        );
        
        $this->_getWriteAdapter()->delete(
            $this->getTable('amsegments/product_index'),
            array('segment_id=?' => $segment->getId())
        );
        
        return $this;
    }
    
    public function createConditionSql($field, $operator, $value)
    {
        if (!is_array($value)) {
            $prepareValues = explode(',', $value);
            if (count($prepareValues) <= 1) {
                $value = $prepareValues[0];
            } else {
                $value = array();
                foreach ($prepareValues as $val) {
                    $value[] = trim($val);
                }
            }
        }

        if (count($value) != 1 and in_array($operator, array('==', '!='))) {
            $operator = $operator == '==' ? '()' : '!()';
        }
        $sqlOperator = Mage::helper("amsegments")->getSqlOperator($operator);
        $condition = '';

        switch ($operator) {
            case '{}':
            case '!{}':
                if (is_array($value)) {
                    if (!empty($value)) {
                        $condition = array();
                        foreach ($value as $val) {
                            $condition[] = $this->_getReadAdapter()->quoteInto(
                                $field . ' ' . $sqlOperator . ' ?', '%' . $val . '%'
                            );
                        }
                        $condition = implode(' AND ', $condition);
                    }
                } else {
                    $condition = $this->_getReadAdapter()->quoteInto(
                        $field . ' ' . $sqlOperator . ' ?', '%' . $value . '%'
                    );
                }
                break;
            case '()':
            case '!()':
                if (is_array($value) && !empty($value)) {
                    $condition = $this->_getReadAdapter()->quoteInto(
                        $field . ' ' . $sqlOperator . ' (?)', $value
                    );
                } else{
                    $condition = $this->_getReadAdapter()->quoteInto($field . ' ' . $sqlOperator . ' (?)', $value);
                }
                break;
            case '[]':
            case '![]':
                if (is_array($value) && !empty($value)) {
                    $conditions = array();
                    foreach ($value as $v) {
                        $conditions[] = $this->_getReadAdapter()->prepareSqlCondition(
                            $field, array('finset' => $this->_getReadAdapter()->quote($v))
                        );
                    }
                    $condition  = sprintf('(%s)=%d', join(' AND ', $conditions), $operator == '[]' ? 1 : 0);
                } else {
                    if ($operator == '[]') {
                        $condition = $this->_getReadAdapter()->prepareSqlCondition(
                            $field, array('finset' => $this->_getReadAdapter()->quote($value))
                        );
                    } else {
                        $condition = 'NOT (' . $this->_getReadAdapter()->prepareSqlCondition(
                            $field, array('finset' => $this->_getReadAdapter()->quote($value))
                        ) . ')';
                    }
                }
                break;
            case 'between':
                $condition = $field . ' ' . sprintf($sqlOperator,
                    $this->_getReadAdapter()->quote($value['start']), $this->_getReadAdapter()->quote($value['end']));
                break;
            default:
                $condition = $this->_getReadAdapter()->quoteInto($field . ' ' . $sqlOperator . ' ?', $value);
                break;
        }
        return $condition;
    }
}