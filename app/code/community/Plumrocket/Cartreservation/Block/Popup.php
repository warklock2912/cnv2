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

class Plumrocket_Cartreservation_Block_Popup extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            $this->setTemplate('cartreservation/empty.phtml');
        }

        return parent::_toHtml();
    }
    
    public function getHtml()
    {
        $variables = Mage::helper('cartreservation/customer')->getTemplateVariables(
            Mage::helper('cartreservation/customer')->getItems(), 'alert'
        );
            
        $template = Mage::getModel('core/email_template')
            ->setTemplateText(Mage::getStoreConfig('cartreservation/reminders_alert/template'));

        $processor = $template->getTemplateFilter();
        $processor->setUseSessionInUrl(false)
            ->setPlainTemplateMode($template->isPlain())
            //->setTemplateProcessor(array($template, 'getTemplateByConfigPath'))
            ->setIncludeProcessor(array($template, 'getInclude'))
            ->setVariables($variables);

        return $processor->filter($template->getTemplateText());
    }
}