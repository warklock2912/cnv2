<?php

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Cart_Reservation-v1.5.x
@copyright	Copyright (c) 2013 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 
*/

class Plumrocket_Cartreservation_Block_System_Config_Alert extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setWysiwyg(true);
        $element->setConfig(Mage::getSingleton('cms/wysiwyg_config')->getConfig());
        return parent::_getElementHtml($element) . $this->_getLoadButtonHtml();
    }

    private function _getLoadButtonHtml()
    {
        return '<script type="text/javascript">
		//<![CDATA[
		    function load_template() {
		        new Ajax.Request("' . $this->_getAjaxLoadUrl() . '", {
		            method: "get",
		            onSuccess: function(transport) {
		                if (transport.responseText) {
                            tinyMCE.activeEditor.setContent(transport.responseText);
		                }
		            }
		        });
		    }
		//]]>
		</script><br />' . $this->_getButtonHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    private function _getAjaxLoadUrl()
    {
        return Mage::helper('adminhtml')->getUrl('cartreservation/index/loadtemplate');
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    private function _getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'id'        => 'cr_button',
                'label'     => $this->helper('adminhtml')->__('Load Default Template'),
                'onclick'   => 'load_template(); return false;'
                )
            );
 
        return $button->toHtml();
    }
}
