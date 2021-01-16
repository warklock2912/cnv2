<?php

class Crystal_Pushnotification_Block_Adminhtml_Pushnotification_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm()
	{

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('notification_form', array(
				'legend' => Mage::helper('pushnotification')->__('Notification information'))
		);
        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('pushnotification')->__('Title'),
            'class' => 'required-entry',
            'required' => TRUE,
            'name' => 'title',
        ));
		$fieldset->addField('message', 'textarea', array(
			'label' => Mage::helper('pushnotification')->__('Message'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'message',
		));
		$fieldset->addField('url', 'text', array(
			'label' => Mage::helper('pushnotification')->__('Url'),
			'name' => 'url',
            'after_element_html' => '<br/><small>  product/:id <br> category/:id <br> blog/:id <br>cms/:id</small>',
		));


		$data = Mage::registry('notification_data')->getData();

		if (Mage::getSingleton('adminhtml/session')->getNotificationData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getNotificationData());
			Mage::getSingleton('adminhtml/session')->setNotificationData(null);
		} elseif (Mage::registry('notification_data')) {
			$form->setValues($data);
		}
		return parent::_prepareForm();
	}
}