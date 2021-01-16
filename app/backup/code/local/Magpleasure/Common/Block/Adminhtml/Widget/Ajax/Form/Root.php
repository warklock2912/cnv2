<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form_Root extends Mage_Adminhtml_Block_Template
{
    const TEMPLATE_PATH = "magpleasure/ajax/form/root.phtml";

    const DEFAULT_WIDTH = 640;
    const DEFAULT_HEIGHT = 460;

    protected $_id = "ajaxForm";

    protected $_container;

    protected function _construct()
    {
        $this->setWidth(self::DEFAULT_WIDTH);
        $this->setHeight(self::DEFAULT_HEIGHT);
        $this->setSuccessMessage($this->__("Item has been successfully saved."));
        $this->setParamKey('entity_id');

        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * Container Object
     *
     * @return Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form_Container
     */
    public function getContainerObject()
    {
        if (!$this->_container){

            $container = $this->getLayout()->createBlock($this->getContainer());
            $this->_container = $container;
        }
        return $this->_container;
    }

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId()."JsObject";
    }

    public function getHtmlId()
    {
        return $this->_id;
    }

    public function createForm($id, array $data)
    {
        $this->_id = $id;
        $this->addData($data);

        Mage::unregister('mp_ajax_var_name');
        Mage::register('mp_ajax_var_name', $this->getJsObjectName(), true);

        $this->getContainerObject();
        return $this;
    }

    protected function _getUrlParams()
    {
        $params = array(
            $this->getParamKey() => '{{entity_id}}',
        );

        if ($transferData = $this->getUrlTransferData()){
            if (is_array($transferData)){
                foreach ($transferData as $key=>$value){
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    public function getLoadUrl()
    {
        $paramKey = $this->getParamKey();
        return $this->getUrl("adminhtml/magpleasure_ajaxform/load", $this->_getUrlParams());
    }

    public function getSaveUrl()
    {
        $paramKey = $this->getParamKey();
        return $this->getUrl("adminhtml/magpleasure_ajaxform/save", $this->_getUrlParams());
    }

    public function getButtonsJson()
    {
        $buttons = array();
        if ($this->getContainerObject()){
            foreach ($this->getContainerObject()->getButtons() as $button){
                $buttons[] = '"'.$button['label'].'":function(){'.$button['onclick'].'}';
            }
        }
        return '{'.implode(",", $buttons).'}';
    }

    public function getUseDebug()
    {
        return Mage::getIsDeveloperMode();
    }

    public function getDefaultError()
    {
        return str_replace("'", "\\'", $this->__("Request couldn't be processed due to some error."));
    }

    public function getSuccessMessage()
    {
        return str_replace("'", "\\'", $this->getData('success_message'));
    }

    public function getDefaultEntityId()
    {
        return $this->getData('default_entity_id') ? $this->getData('default_entity_id') : '0';
    }
}