<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amcustomform_form_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('form_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        /** @var Amasty_Customform_Model_Resource_Form_Collection $collection */
        $collection = Mage::getModel('amcustomform/form')->getCollection();
        $collection->join(array('form_store' => 'amcustomform/form_store'), 'form_store.form_id=main_table.id AND form_store.store_id = 0', array('title' => 'title'));

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
        ));

        $this->addColumn('title',
            array(
                'header'=> $helper->__('Title'),
                'index' => 'title',
                'filter_index'=>'form_store.title'
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
                    ),
//                    array(
//                        'caption'   => $helper->__('Preview'),
//                        'url'       => array(
//                            'base'      => '*/*/preview',
//                            'params'    => array('store'=>$this->getRequest()->getParam('store')),
//                        ),
//                        'field'     => 'id',
//                    ),
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
