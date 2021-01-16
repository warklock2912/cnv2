<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   Auto-Cancel Orders
 * @type        Order management
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php 
 * 
 * @category    Magento Commerce
 * @package     Appmerce_AutoCancel
 * @copyright   Copyright (c) 2011-2013 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_AutoCancel_Helper_Mapping
{
    protected $_divider = '@';
    
    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $method_status => $period) {
                if (!array_key_exists($method_status, $data)) {
                    $method_status = explode($this->_divider, $method_status);
                    $data[$method_status[0] . $this->_divider . $method_status[1]] = $period;
                }
            }
            if (count($data) == 1 && array_key_exists('default', $data)) {
                return (string)$data['default'];
            }
            return serialize($data);
        }
        else {
            return '';
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return unserialize($value);
        }
        else {
            return array();
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param mixed
     * @return bool
     */
    protected function _isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('method', $row) || !array_key_exists('status', $row) || !array_key_exists('period', $row) || !array_key_exists('customperiod', $row)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     * @return array
     */
    protected function _encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $method_status => $period) {
            $_id = Mage::helper('core')->uniqHash('_');
            $method_status = explode($this->_divider, $method_status);
            if (strpos($period,$this->_divider)){
                $period = explode($this->_divider, $period);
                $result[$_id] = array(
                    'method' => $method_status[0],
                    'status' => $method_status[1],
                    'period' => $period[0],
                    'customperiod' => $period[1],
                );
            } else{
                $result[$_id] = array(
                    'method' => $method_status[0],
                    'status' => $method_status[1],
                    'period' => $period
                );
            }

        }
        return $result;
    }

    /**
     * Decode value from used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     * @return array
     */
    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('method', $row) || !array_key_exists('status', $row) || !array_key_exists('period', $row) || !array_key_exists('customperiod', $row)) {
                continue;
            }
            $method = $row['method'];
            $status = $row['status'];
            $period = $row['period'];
            $customPeriod = $row['customperiod'];
            if ($period == 1){
                $result[$method . $this->_divider . $status] = $period. $this->_divider .$customPeriod;
            } else{
                $result[$method . $this->_divider . $status] = $period;
            }
        }
        return $result;
    }

    /**
     * Retrieve period value from config
     *
     * @param int $method
     * @param mixed $store
     * @return float|null
     */
    public function getConfigValue($methodCode, $statusCode, $store = null)
    {
        $value = Mage::getStoreConfig('autocancel/settings/mapping', $store);
        $value = $this->_unserializeValue($value);
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $result = null;
        foreach ($value as $method_status => $period) {
            $method_status = explode($this->_divider, $method_status);
            if ($method_status[0] == $methodCode) {
                if ($method_status[1] == $statusCode) {
                    $result = $period;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Make value readable by Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param mixed $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->_unserializeValue($value);
        if (!$this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param mixed $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $value = $this->_serializeValue($value);

        return $value;
    }

}
