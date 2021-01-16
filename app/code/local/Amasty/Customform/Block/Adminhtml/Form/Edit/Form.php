<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->addField('validation-url', 'hidden', array(
            'value' => $this->getUrl('*/*/validate', array('_current'=>true)),
        ));

        $form->addField('preview-url', 'hidden', array(
            'value' => $this->getBaseUrl() . '/customform/form/preview',
        ));

        $form->addField('preview-title', 'hidden', array(
            'value' => $this->__('Form Preview'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
