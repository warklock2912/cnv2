<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save',array(
				'id'=> $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));

		$raffle = Mage::registry('raffleonline_data');

        if ($raffle->getId()) {
            $form->addField('raffle_id', 'hidden', array(
                'name' => 'raffle_id',
				'value' => $raffle->getId()
            ));
        }

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}
