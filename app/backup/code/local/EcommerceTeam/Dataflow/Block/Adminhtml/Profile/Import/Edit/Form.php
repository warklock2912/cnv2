<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Import_Profile */
        $profile = Mage::registry('profile');
        $formUrl = $this->getUrl('*/*/save', array('id' => $profile->getId()));
        $form    = new Varien_Data_Form(array(
            'id'        =>  'edit_form',
            'action'    =>  $formUrl,
            'method'    =>  'post',
            'enctype'   =>  'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
