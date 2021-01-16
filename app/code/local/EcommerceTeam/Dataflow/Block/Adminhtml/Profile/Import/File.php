<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_File
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->__('Run Profile'),
                'onclick'   => "$('edit_form').submit()",
                'class'     => 'success'
            ));
        $this->setChild('continue_button', $button);
        return parent::_prepareLayout();
    }
    
    protected function _prepareForm()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Import_Profile */
        $profile = Mage::registry('profile');
        $formUrl = $this->getUrl('*/*/run', array('id' => $profile->getId()));
        $form    = new Varien_Data_Form(array(
            'id'        =>  'edit_form',
            'action'    =>  $formUrl,
            'method'    =>  'post',
            'enctype'   =>  'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldSet = $form->addFieldset('main_fieldset', array('legend' => $this->__('General Information')));

        $fieldSet->addField('datafile', 'file', array(
            'name'     => 'datafile',
            'label'    => $this->__('Data File'),
            'title'    => $this->__('Data File'),
            'required' => true,
            'note'     => $this->__('Your server PHP settings allow you to upload files not more than %s at a time. Please modify post_max_size (currently is %s) and upload_max_filesize (currently is %s) values in php.ini if you want to upload larger files.', $this->getDataMaxSize(), $this->getPostMaxSize(), $this->getUploadMaxSize()),
        ));

        $fieldSet->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));
        
        return parent::_prepareForm();
    }

    public function getDataMaxSize()
    {
        return min($this->getPostMaxSize(), $this->getUploadMaxSize());
    }
    public function getPostMaxSize()
    {
        return ini_get('post_max_size');
    }

    public function getUploadMaxSize()
    {
        return ini_get('upload_max_filesize');
    }
}
