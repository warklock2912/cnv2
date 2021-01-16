<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'campaignmanage';
        $this->_controller = 'adminhtml_campaign';
        $this->_updateButton('save', 'label', Mage::helper('campaignmanage')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('campaignmanage')->__('Delete'));
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $typeStoreQueue = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_QUEUE;
        $typeStoreShuffle = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE;
        if ($this->getCampaignType() == $typeStoreQueue || $this->getCampaignType() == $typeStoreShuffle) {
            $this->_addButton('endqueue', array(
                'label' => Mage::helper('adminhtml')->__('End Queue'),
                'onclick' => 'setLocation(\' '  . $this->getUrl('*/*/endQueue', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                'class' => 'endqueue',
            ), -100);
        }
        $this->_formScripts[] = "

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		
			function addSelectedItems() {
					var productIds = $$('input[name^=product_ids]')[0];
					if (productIds.value == '') {
						alert('Please select at least one item');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/addSelectedItems', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . "';
				
				$('edit_form').submit();

			}
		";
    }

    public function getCampaignType()
    {
        $campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
        return $campaign->getCampaignType();
    }

    public function getHeaderText()
    {
        if (Mage::registry('campaign_data') && Mage::registry('campaign_data')->getId()) {
            return Mage::helper('campaignmanage')->__("Edit '%s'", $this->htmlEscape(Mage::registry('campaign_data')->getCampaignName()));
        } else {
            return Mage::helper('campaignmanage')->__('Add New Campaign');
        }
    }

    public function getBackUrl()
    {
        $locator_id = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'))['dealerlocator_id'];
        parent::getBackUrl();
        return $this->getUrl('*/*/index', array('id' => $locator_id));
    }
}