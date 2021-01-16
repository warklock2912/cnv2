<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Block_Adminhtml_Data_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'amreports';
        $this->_controller = 'adminhtml_data';
        parent::__construct();
        $model = Mage::registry('amreports_data');
        $this->_removeButton('reset');
        if (!$model->getData()) {
            $this->_removeButton('save');
        }
    }

    protected function _prepareLayout(){
        parent::_prepareLayout();
        $head = $this->getLayout()->getBlock('head');
        $head->addJs('amasty/amreports/amcharts/amcharts.js');
        $head->addJs('amasty/amreports/amcharts/pie.js');
        $head->addJs('amasty/amreports/amcharts/serial.js');
        $head->addJs('amasty/amreports/amcharts/amstock.js');
        $head->addJs('amasty/amreports/main.js');
        $head->addJs('amasty/amreports/sorttable.js');
    }
    public function getHeaderText()
    {
        $helper = Mage::helper('amreports');
        $model = Mage::registry('amreports_data');

        if ($model->getId()) {
            return $helper->__(
                "Edit Report '%s'", $this->escapeHtml($model->getName())
            );
        } else {
            return $helper->__("Add Report");
        }
    }
}
