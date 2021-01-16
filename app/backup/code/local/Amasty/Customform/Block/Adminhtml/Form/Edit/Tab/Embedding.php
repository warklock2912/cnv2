<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Tab_Embedding extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if ($this->getFormEntity()->getId()) {
            $this->renderEmbedding();
        } else {
            $this->renderNotSaved();
        }


        return parent::_prepareForm();
    }

    protected function renderEmbedding()
    {
        $fieldset = $this->getForm()->addFieldset('cms', array(
            'legend'    => Mage::helper('amcustomform')->__('CMS Pages'),
            'class'     => 'fieldset-wide',
        ));

        $fieldset->addField('embedding-cms', 'textarea', array(
            'label'                 => Mage::helper('amcustomform')->__('CMS Embedding Code'),
            'title'                 => Mage::helper('amcustomform')->__('CMS Embedding Code'),
            'value'                 => $this->getFormEntity()->getCmsEmbeddingCode(),
            'after_element_html'    => '<small>' . Mage::helper('amcustomform')->__('Copy this code into CMS Page Editor to insert form into any CMS pag.e') . '</small>',
        ));

        $fieldset = $this->getForm()->addFieldset('template', array(
            'legend'    => Mage::helper('amcustomform')->__('Templates'),
            'class'     => 'fieldset-wide',
        ));

        $fieldset->addField('embedding-template', 'textarea', array(
            'label'                 => Mage::helper('amcustomform')->__('Template Embedding Code'),
            'title'                 => Mage::helper('amcustomform')->__('Template Embedding Code'),
            'value'                 => $this->getFormEntity()->getTemplateEmbeddingCode(),
            'after_element_html'    => '<small>' . Mage::helper('amcustomform')->__('Insert this code into *.phtml template directly to display form in any block.') . '</small>',
        ));
    }

    protected function renderNotSaved()
    {
        $note = new Varien_Data_Form_Element_Note();
        $note->setData('text', Mage::helper('amcustomform')->__('This form is not saved yet. Please save this form first to get your embedding codes.'));

        $this->getForm()->addElement($note);
    }

    /**
     * @return Amasty_Customform_Model_Form
     */
    protected function getFormEntity()
    {
        return Mage::registry('amcustomform_current_form');
    }

}