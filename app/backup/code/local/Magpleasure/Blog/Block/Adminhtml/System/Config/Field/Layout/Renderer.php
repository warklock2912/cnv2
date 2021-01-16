<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Renderer
    extends Magpleasure_Common_Block_Adminhtml_Template
{
    protected $_elementName;
    protected $_elementId;
    protected $_elementValue;
    protected $_layoutConfig = array();

    /**
     * @return mixed
     */
    public function getElementValue()
    {
        return $this->_elementValue;
    }

    /**
     * @param $elementValue
     * @return $this
     */
    public function setElementValue($elementValue)
    {
        $this->_elementValue = $elementValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getElementName()
    {
        return $this->_elementName;
    }

    /**
     * @param $elementName
     * @return $this
     */
    public function setElementName($elementName)
    {
        $this->_elementName = $elementName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getElementId()
    {
        return $this->_elementId;
    }

    /**
     * @param $elementId
     * @return $this
     */
    public function setElementId($elementId)
    {
        $this->_elementId = $elementId;
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/system/config/field/element.phtml");
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setLayoutConfig($config)
    {
        $this->_layoutConfig = $config;
        return $this;
    }

    public function getLayoutConfig()
    {
        return $this->_layoutConfig;
    }

    public function getLayoutConfigJson()
    {
        return Zend_Json::encode($this->getLayoutConfig());
    }
}