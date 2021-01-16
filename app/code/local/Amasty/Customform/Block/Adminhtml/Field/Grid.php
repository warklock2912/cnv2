<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Field_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amcustomform_field_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('field_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        /** @var Amasty_Customform_Model_Resource_Field_Collection $collection */
        $collection = Mage::getModel('amcustomform/field')->getCollection();
        $collection->addFilter('is_deleted', 0);
        $collection->join(array('field_store' => 'amcustomform/field_store'), 'field_store.field_id=main_table.id AND field_store.store_id = 0', array('label' => 'label'));

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = $this->getDataHelper();

        $this->addColumn('id',
            array(
                'header'=> $helper->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
                'filter_index'=>'main_table.id'
        ));

        $this->addColumn('code',
            array(
                'header'=> $helper->__('Code'),
                'index' => 'code',
                'filter_index'=>'main_table.code'
            )
        );

        $this->addColumn('label',
            array(
                'header'=> $helper->__('Label'),
                'index' => 'label',
                'filter_index'=>'field_store.label'
            )
        );

        $this->addColumn('action',
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * @param Amasty_Customform_Model_Field $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }

    /**
     * @return Amasty_Customform_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('amcustomform');
    }
}
