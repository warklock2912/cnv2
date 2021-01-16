<?php

class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form(array(
			'id'	=> 'edit_form',
			'action'	=> $this->getUrl('*/*/processImport'),
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		));
		
		$fieldset = $form->addFieldset('profile_fieldset',array('legend'=>Mage::helper('rewardpointscsv')->__('Import Form')));
		
		$fieldset->addField('filecsv','file',array(
			'label'		=> Mage::helper('rewardpointscsv')->__('Import File'),
			'title'		=> Mage::helper('rewardpointscsv')->__('Import File'),
			'name'		=> 'filecsv',
			'required'	=> true,
		));
        
        $fieldset->addField('sample', 'note', array(
            'label' => Mage::helper('rewardpointscsv')->__('Download Sample CSV File'),
            'text'  => '<a href="'.
                    $this->getUrl('*/*/downloadSample').
                    '" title="'.
                    Mage::helper('rewardpointscsv')->__('Download Sample CSV File').
                    '">import_point_balance_sample.csv</a>'
        ));
		
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}