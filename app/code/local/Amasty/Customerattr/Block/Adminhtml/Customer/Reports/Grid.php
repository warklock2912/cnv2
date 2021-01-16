<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Reports_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportsGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit', array('attribute_id' => $row->getAttributeId())
        );
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $filters = array(
            "is_user_defined = 1",
            "attribute_code != 'customer_activated' "
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection, 'eav_attribute', $filters
        );

        foreach ($collection as $attribute) {
            if ('statictext' == $attribute->getTypeInternal()) {
                $attribute->setFrontendInput('statictext');
            }
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_code', array(
                'header'   => Mage::helper('catalog')->__('Code'),
                'sortable' => true,
                'index'    => 'attribute_code'
            )
        );

        $this->addColumn(
            'frontend_label', array(
                'header'   => Mage::helper('catalog')->__('Label'),
                'sortable' => true,
                'index'    => 'frontend_label'
            )
        );

        $this->addColumn(
            'frontend_input', array(
                'header'   => Mage::helper('catalog')->__('Type'),
                'sortable' => true,
                'index'    => 'frontend_input',
                'type'     => 'options',
                'options'  => Mage::helper('amcustomerattr')->getAttributeTypes(
                    true
                ),
                'align'    => 'center',
                'renderer' => 'amcustomerattr/adminhtml_customer_attribute_grid_renderer_type',
            )
        );

        return parent::_prepareColumns();
    }

}