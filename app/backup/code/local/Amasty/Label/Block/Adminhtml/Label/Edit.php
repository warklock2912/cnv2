<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amlabel';
        $this->_controller = 'adminhtml_label';
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
       $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";        
       $this->_formScripts[] = " function showOptions(sel) {
            new Ajax.Request('" . $this->getUrl('*/*/options', array('isAjax'=>true)) ."', {
                parameters: {code : sel.value, multiple : $('attr_multi').value },
                onSuccess: function(transport) {
                    $('attr_value').up().update(transport.responseText);
                }
            });
        }";
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amlabel')->__('New Product Label');
        if (Mage::registry('amlabel_label')->getId()){
            $header = Mage::helper('amlabel')->__('Edit Product Label `%s`', Mage::registry('amlabel_label')->getName());
        }
        return $header;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }
}