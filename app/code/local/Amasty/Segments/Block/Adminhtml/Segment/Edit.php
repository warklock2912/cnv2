<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Block_Adminhtml_Segment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amsegments';
        $this->_controller = 'adminhtml_segment';
        
          
        $label = Mage::helper('amsegments')->__('Save and Continue Edit');
        
        if (!$this->getRequest()->getParam("id")){
            $this->_removeButton('save');
            $this->_removeButton('reset');
            
            $label = Mage::helper('amsegments')->__('Continue');
        }
        
        $this->_addButton('save_and_continue', array(
                'label'     => $label,
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
                $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";  
        
    }
    
    public function getHeaderText()
    {
        $header = Mage::helper('amsegments')->__('New Segment');
        $model = $this->getModel();//Mage::registry('amsegments_segment');
        
        if ($model->getId()){
            $header = Mage::helper('amsegments')->__('Edit Segment `%s`', $model->getName());
        }
        return $header;
    }
}