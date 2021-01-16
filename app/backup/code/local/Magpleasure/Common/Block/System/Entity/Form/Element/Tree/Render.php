<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_Tree_Render
    extends Magpleasure_Common_Block_System_Entity_Form_Element_Abstract
{
    /**
     * Path to element template
     */
    const TEMPLATE_PATH = 'magpleasure/system/config/form/element/tree.phtml';

    protected $_treeData;
    protected $_buttons = array();

    protected function  _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    protected function _addButton($id, $data)
    {
        $this->_buttons[$id] = $data;
        return $this;
    }

    public function getTreeData()
    {
        if (!$this->_treeData){
            /** @var $treeData Magpleasure_Common_Model_Form_Element_Tree_Data */
            $treeData = Mage::getModel('magpleasure/form_element_tree_data');
            $treeData
                ->setTreeConfig($this->getTree())
                ->setValues($this->getValue())
                ;

            $this->_treeData = $treeData;
        }
        return $this->_treeData;
    }

    public function getSelectedArray()
    {
        return "[".implode(",", $this->getTreeData()->getValues())."]";
    }

    protected function _dataToJson(array $data)
    {
        return Zend_Json::encode($data);
    }

    public function getTreeJson()
    {
        return $this->_dataToJson($this->getTreeData()->getArray());
    }

    public function getLeafsJson()
    {
        return $this->_dataToJson($this->getTreeData()->getLeafsArray());
    }

    public function getRootJson()
    {
        return $this->_dataToJson($this->getTreeData()->getRootArray());
    }

    public function getSelectedJson()
    {
        return Zend_Json::encode($this->getSelectedArray());
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId()."JsObj";
    }

    public function getButtonsJson()
    {
        # Prepare Buttons
        $this->_addButton('save', array(
            'label'     => $this->_commonHelper()->__('Apply'),
            'title'     => $this->_commonHelper()->__('Apply'),
            'class'     => 'default',
            'onclick'   => $this->getJsObjectName().'.apply(); return false;',
        ));

        $this->_addButton('cancel', array(
            'label'     => $this->_commonHelper()->__('Cancel'),
            'title'     => $this->_commonHelper()->__('Cancel'),
            'class'     => 'close',
            'onclick'   => $this->getJsObjectName().'.close(); return false;',
        ));

        $buttons = array();

        foreach ($this->_buttons as $button){
            $buttons[] = '"'.$button['label'].'":function(){'.$button['onclick'].'}';
        }

        return '{'.implode(",", $buttons).'}';
    }

    public function getRootVisible()
    {
        return isset($this->_data['tree']['root_visible']) ? $this->_data['tree']['root_visible'] : false;
    }

}
