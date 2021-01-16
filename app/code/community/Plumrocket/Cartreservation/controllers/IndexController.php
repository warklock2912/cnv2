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

class Plumrocket_Cartreservation_IndexController extends Mage_Core_Controller_Front_Action
{
    public function lockpopupAction()
    {
        Mage::helper('cartreservation/customer')->expireItemsWithExpiredReminderTime('alert');
        Mage::app()->getResponse()->setBody('ok');
    }

    public function loadtemplateAction()
    {
        $result = (string) Mage::getConfig()->getNode('default/cartreservation/reminders_alert/template_default');
        Mage::app()->getResponse()->setBody($result);
    }

    public function realTimeAction()
    {
        return $this->getResponse()->setBody(
            json_encode(
                array(
                'rCTime' => time()
                )
            )
        );
    }

    public function reloadAction()
    {
        Mage::getModel('checkout/cart')->getQuote();
        $referer = $this->getRequest()->getParam('referer');
        if (!$referer) {
            $referer = Mage::getBaseUrl();
        }

        $this->getResponse()->setRedirect($referer);
    }

}
