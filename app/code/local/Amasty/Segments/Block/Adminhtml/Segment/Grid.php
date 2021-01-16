<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Block_Adminhtml_Segment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
  {
      parent::__construct();
      $this->setId('segmentGrid');
      $this->setDefaultSort('segment_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amsegments/segment')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
   
    $hlp =  Mage::helper('amsegments'); 
    
    $this->addColumn('segment_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'segment_id',
    ));

//    $this->addColumn('is_active', array(
//        'header'    => Mage::helper('salessegment')->__('Status'),
//        'align'     => 'left',
//        'width'     => '80px',
//        'index'     => 'is_active',
//        'type'      => 'options',
//        'options'   => $hlp->getSegmentStatuses()
//    ));    

    $this->addColumn('name', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'name',
    ));  

    $this->addColumn('generated_at', array(
        'header'  => $hlp->__('Calculated At'),
        'index'   => 'generated_at',
        'type'    => 'datetime',
        'getter'  => 'getGeneratedAt',
        'default' => '',
        'gmtoffset' => true,
        'width' => 180
    ));
    
    return parent::_prepareColumns();
  }

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getTemplatesSelectHtml() . $this->getReindexButtonHtml() . $html;
        return $html;
    }
  
    public function getReindexButtonHtml()
    {
        return $this->getChildHtml('reindex_button')."&nbsp;&nbsp;&nbsp;";
        
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setExportVisibility('true');
        $this->setChild('reindex_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('amsegments')->__('Reindex All'),
                    'onclick'   => 'document.location.href = \''.$this->getUrl('*/*/reindex').'\';',
                    'class'     => 'task'
                ))
        );

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}