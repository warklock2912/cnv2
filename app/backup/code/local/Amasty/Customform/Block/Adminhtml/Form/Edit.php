<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomform';
        $this->_controller = 'adminhtml_form';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Form'));
        $this->_updateButton('delete', 'label', $this->__('Delete Form'));
        $this->_updateButton('delete', 'on_click', 'deleteConfirm(\''. Mage::helper('amcustomform')->__('ATTENTION! This will also erase ALL the collected data posted by users. Are you sure to delete entire form?') .'\', \'' . $this->getDeleteUrl() . '\')');

        $this->_addButton('preview', array(
            'label' => $this->__('Preview'),
            'on_click'  => 'formPreviewer.preview()',
        ));
    }

    protected function _prepareLayout()
    {
        $this->addButton('save_and_edit_button', array(
            'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit(\''.$this->getSaveAndContinueUrl().'\')',
            'class' => 'save',
        ));

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

    public function getHeaderText()
    {
        return $this->getForm()->getId()
            ? $this->__('Edit Form')
            : $this->__('New Form');
    }

    /**
     * @return Amasty_Customform_Model_Form
     */
    protected function getForm()
    {
        return Mage::registry('amcustomform_current_form');
    }
}