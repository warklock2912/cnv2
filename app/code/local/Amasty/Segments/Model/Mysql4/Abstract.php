<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    const LAST_EXECUTED_PATH = 'amsegments/common/last_executed';
    
    protected static $_lastExecuted = null;
    protected static $_currentExecution  = null;
    
    
    public function _construct(){}
            
            
    static function getLastExecuted(){
        if (self::$_lastExecuted === null){
            self::$_lastExecuted = (string) Mage::getStoreConfig(self::LAST_EXECUTED_PATH);
            
            self::$_currentExecution = time();

            Mage::getConfig()->saveConfig(self::LAST_EXECUTED_PATH, self::$_currentExecution);
            Mage::getConfig()->cleanCache();
        }
        return self::$_lastExecuted;
    }

    static function getCurrentExecution(){
        return self::$_currentExecution ? self::$_currentExecution : time();
    }
    
    protected function _addTimeRange($select, $field){
        if (self::getLastExecuted())
            $select->where($field . ' > ?', $this->date(self::getLastExecuted()));
        
        if (self::getCurrentExecution())
            $select->where($field . ' < ?', $this->date(self::getCurrentExecution()));
    }
    
    function date($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }
}
