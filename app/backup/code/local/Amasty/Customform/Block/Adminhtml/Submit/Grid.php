<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Submit_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amcustomform_submit_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('submit_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        /** @var Amasty_Customform_Model_Resource_Form_Submit_Collection $collection */
        $collection = Mage::getModel('amcustomform/form_submit')->getCollection();

        $collection->join( array('form' => 'amcustomform/form'), 'form.id=main_table.form_id', array('form_code' => 'code'));
        $collection->join( array('form_store' => 'amcustomform/form_store'), 'form_store.form_id=main_table.form_id AND form_store.store_id = 0', array('form_title' => 'title'));

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

        $this->addColumn('form_code',
            array(
                'header'=> $helper->__('Form Code'),
                'index' => 'form_code',
                'filter_index'=>'form.code'
        ));

        $this->addColumn('form_title',
            array(
                'header'=> $helper->__('Form Title'),
                'index' => 'form_title',
                'filter_index'=>'form_store.title'
            ));

        $this->addColumn('store',
            array(
                'header'=> $helper->__('Store'),
                'index' => 'store_id',
                'type'  => 'store',
                'filter_index'=>'main_table.store_id'
            ));

        $this->addColumn('submitted',
            array(
                'header'=> $helper->__('Submitted'),
                'index' => 'submitted',
                'type'  => 'datetime',
                'filter_index'=>'main_table.submitted'
            ));

        $this->addColumn('action',
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('View'),
                        'url'     => array(
                            'base'=>'*/*/view',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id',
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('submit_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('catalog')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        return $this;
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
        return $this->getUrl('*/*/view', array(
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
