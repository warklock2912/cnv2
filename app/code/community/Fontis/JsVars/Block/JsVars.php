<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2017 Fontis Pty. Ltd. (https://www.fontis.com.au)
 * @license    Fontis Software License
 */

/**
 * @method Fontis_JsVars_Block_JsVars setContainerVariablePrefix(string $prefix)
 */
class Fontis_JsVars_Block_JsVars extends Mage_Core_Block_Abstract
{
    const DEFAULT_CONTAINER_VARIABLE_PREFIX = "fontis";

    /** @var Mage_Core_Helper_Js */
    protected $_jsHelper = null;

    /** @var array Contains variables that are output as JSON. */
    protected $_variables = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_jsHelper = Mage::helper("core/js");
        if ($prefix = Mage::helper("fontis_jsvars")->getContainerVariablePrefix()) {
            $this->setContainerVariablePrefix($prefix);
        }
    }

    /**
     * Sets a JS variable.
     *
     * @param string $var Variable name
     * @param mixed $value Value
     */
    public function addVar($var, $value)
    {
        $this->_variables[$var] = $value;
    }

    /**
     * Sets a JS variable to a nested array of values. Takes a JSON string
     * which is decoded to a PHP array. Designed to be called from layout XML,
     * as this allows setting multiple nested values at once.
     *
     * @param string $var Variable name
     * @param string $json Value as a JSON string
     */
    public function addJsonVar($var, $json)
    {
        $this->_variables[$var] = Zend_Json::decode($json, Zend_Json::TYPE_ARRAY);
    }

    /**
     * Gets a JS variable. Can be used to update a nested value. Assumes $var
     * exists in the array; use isVarSet() to test before accessing if it may
     * not.
     *
     * @param string $var Variable name
     * @return mixed Variable content.
     */
    public function getVar($var)
    {
        return $this->_variables[$var];
    }

    /**
     * Checks if a JS variable is set.
     *
     * @param string $var Variable name.
     * @return bool True if JS var is set.
     */
    public function isVarSet($var)
    {
        return isset($var);
    }

    /**
     * Dispatches an event before the block's HTML is rendered, allowing other
     * extensions to insert variables from code.
     *
     * @return Fontis_JsVars_Block_JsVars
     */
    protected function _beforeToHtml()
    {
        Mage::dispatchEvent('fontis_jsvars_before_to_html', array('block' => $this));
        $currentFullActionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        Mage::dispatchEvent("fontis_jsvars_before_to_html_$currentFullActionName", array('block' => $this));
        return $this;
    }

    /**
     * Returns a <script> tag containing the JSON-encoded variables, as well
     * as a variable containing the container prefix.
     *
     * @return string
     */
    public function _toHtml()
    {
        $containerName = "var jsvars_container_prefix = '{$this->getContainerVariablePrefix()}';\n";
        $vars = Zend_Json::encode($this->_variables);
        $container = "var " . $this->getContainerVariableName() . " = $vars ;\n";

        return "\n" . $this->_jsHelper->getScript($containerName . $container) . "\n";
    }

    /**
     * @return string
     */
    public function getContainerVariablePrefix()
    {
        if ($this->hasData("container_variable_prefix")) {
            return $this->getData("container_variable_prefix");
        } else {
            return self::DEFAULT_CONTAINER_VARIABLE_PREFIX;
        }
    }

    /**
     * @return string
     */
    public function getContainerVariableName()
    {
        return $this->getContainerVariablePrefix() . "_jsvars";
    }
}
