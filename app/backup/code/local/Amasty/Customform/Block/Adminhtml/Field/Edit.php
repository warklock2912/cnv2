<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Field_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomform';
        $this->_controller = 'adminhtml_field';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Field'));

        $this->_updateButton('delete', 'label', $this->__('Delete Field'));
        $this->_updateButton('delete', 'on_click', 'deleteConfirm(\''. Mage::helper('amcustomform')->__('This will also remove current field from all the forms. Are you sure?') .'\', \'' . $this->getDeleteUrl() . '\')');
        $this->addButton('save_and_edit_button', array(
            'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit(\''.$this->getSaveAndContinueUrl().'\')',
            'class' => 'save',
        ),2);
    }

    public function getHeaderText()
    {
        return $this->getField()->getId()
            ? $this->__('Edit Field')
            : $this->__('New Field');
    }

    /**
     * @return Amasty_Customform_Model_Field
     */
    protected function getField()
    {
        return Mage::registry('amcustomform_current_field');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();


    }

    protected function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab'        => '{{tab_id}}',
            'active_tab' => null,
        ));
    }
}