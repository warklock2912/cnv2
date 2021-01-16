<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Helper_Reflection
    extends Mage_Core_Helper_Abstract
{
    protected $_reflation = array();

    /**
     * @param string $callbackName
     * @return ReflectionMethod
     * @throws EcommerceTeam_Dataflow_Exception
     */
    public function getReflation($callbackName)
    {
        if (!is_string($callbackName)) {
            throw new EcommerceTeam_Dataflow_Exception($this->__('Wrong callback definition!'));
        }
        if (!isset($this->_reflation[$callbackName])) {
            $callback = explode('::', $callbackName);
            if (2 != count($callback)) {
                throw new EcommerceTeam_Dataflow_Exception($this->__('Wrong callback definition!'));
            }
            $reflection = new ReflectionClass($callback[0]);
            $method     = $reflection->getMethod($callback[1]);
            if (!$method->isStatic()) {
                throw new EcommerceTeam_Dataflow_Exception($this->__('Only static methods can be used for callback!'));
            }
            $this->_reflation[$callbackName] = $method;
        }
        return $this->_reflation[$callbackName];
    }
}