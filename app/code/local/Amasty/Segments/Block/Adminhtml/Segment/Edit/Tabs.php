<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Block_Adminhtml_Segment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('segmentTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amsegments')->__('Segment Configuration'));
    }

    protected function _beforeToHtml()
    {
        $collection = Mage::getResourceModel('amsegments/index_collection')
                ->addResultSegmentData($this->getModel()->getId());
        
        $size = $collection->getSize();
        
        $tabs = array(
            'general' => 'General',
            'conditions' => 'Condition',
            'customers' => 'Customers' . ($size > 0 ? "(" . $size . ")" : "")
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amsegments')->__($label);
            
            $block = $this->getLayout()->createBlock('amsegments/adminhtml_segment_edit_tab_' . $code);
            $block->setModel($this->getModel());
            
            $content = $block
                ->setTitle($label)
                ->toHtml();
            
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content
            ));
        }
        
        return parent::_beforeToHtml();
    }
}