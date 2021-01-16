<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
  	protected function _prepareForm() {
		$form = new Varien_Data_Form(
			array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
				'enctype' => 'multipart/form-data'
			)
		);

		$ruffle = Mage::registry('current_ruffle');

        if ($ruffle->getId()) {
            $form->addField('ruffle_id', 'hidden', array(
                'name' => 'ruffle_id',
            ));
            $form->setValues($ruffle->getData());
        }

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
  	}
}