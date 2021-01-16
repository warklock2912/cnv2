<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('form_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('campaignmanage')->__('Raffle Infomation'));
	}

	public function _beforeToHtml()
	{
		$this->addTab('form_session', array(
			'label' => Mage::helper('campaignmanage')->__('Raffle Online Detail'),
			'title' => Mage::helper('campaignmanage')->__('Raffle Online Detail'),
			'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_form')->toHtml(),
		));

        if( $this->getRequest()->getParam('id')) {
            $this->addTab('item', array(
                'label' => Mage::helper('campaignmanage')->__('Item(s)'),
                'url' => $this->getUrl('*/*/product', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
                'class' => 'ajax',
            ));
            $this->addTab('raffle_online_member', array(
                'label' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
                'title' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
                'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_allmember')->toHtml(),
            ));

//            $this->addTab('campaign_raffle', array(
//                'label' => Mage::helper('campaignmanage')->__('Raffle'),
//                'title' => Mage::helper('campaignmanage')->__('Raffle'),
//                'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_raffle')->toHtml(),
//            ));

            $this->addTab('assign_winner', array(
                'label' => Mage::helper('campaignmanage')->__('Assign Winner(s)'),
                'url' => $this->getUrl('*/*/winner', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
                'class' => 'ajax',
            ));
        }
		return parent::_beforeToHtml();
	}
}
