<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'campaignmanage';
        $this->_controller = 'adminhtml_raffleonline';
        $this->_updateButton('save', 'label', Mage::helper('campaignmanage')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('campaignmanage')->__('Delete'));
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('ruffle_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'ruffle_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'ruffle_content');
                }
            }

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

            function manualSelectWinner(type) {
				if (type == 'general') {
					var generalIds = $$('input[name^=general_ids]')[0];
					if (generalIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/manualSelect', array('_secure' => true, 'group' => 'general')) . "';
				} else {
					var vipIds = $$('input[name^=vip_ids]')[0];
					if (vipIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/manualSelect', array('_secure' => true, 'group' => 'vip')) . "';
				}
				$('edit_form').submit();

			}

			function emailToSelectedWinner() {
					var winnerIds = $$('input[name^=winner_ids]')[0];
					if (winnerIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/emailToSelectedWinner', array('_secure' => true)) . "';

				$('edit_form').submit();

			}

			function unassign(url){
				var unassign = confirm('Are you sure to change winner to loser');
				if(unassign){
				    window.location.href = url;
				} else
				return;
			}
		";
    }

    public function getHeaderText()
    {
        if (Mage::registry('raffleonline_data') && Mage::registry('raffleonline_data')->getId()) {
            return Mage::helper('campaignmanage')->__("Edit '%s'", $this->htmlEscape(Mage::registry('raffleonline_data')->getCampaignName()));
        } else {
            return Mage::helper('campaignmanage')->__('Add New Raffle');
        }
    }
}
