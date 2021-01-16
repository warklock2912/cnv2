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


class Plumrocket_Cartreservation_Model_Cron extends Mage_Core_Model_Abstract
{
    // Cron function
    public function removeLostReserveItems()
    {
        if (Mage::helper('cartreservation')->moduleEnabled()
            // pass if need keep items into cart after
            && (int)Mage::getStoreConfig('cartreservation/cart/after_end') == Plumrocket_Cartreservation_Model_Values_Keepincart::REMOVE
        ) {
            $items = Mage::getModel('cartreservation/item')->getCollection();
            $quotesIds = array();
            foreach ($items as $item) {
                if (! $item->isReserved()) {
                    // Log:
                        $deletedData = $item->getData();
                    $quotesIds[ $item->getQuoteId() ] = true;
                    $item->remove();
                    // Log:
                        Mage::helper('cartreservation')->logAction('delete_timeout_cron', $deletedData);
                }
            }

            foreach ($quotesIds as $id => $_) {
                Mage::getModel('sales/quote')->load($id)
                    ->collectTotals()
                    ->save();
            }
        }
    }

    public function sendEmails()
    {
        if (Mage::helper('cartreservation')->moduleEnabled()
            && Mage::getStoreConfig('cartreservation/reminders_email/send')
        ) {
            $quotes = array();
            $items = Mage::getModel('cartreservation/item')->getCollection()
                ->addFieldToFilter('customer_id', array('neq' => '0'));
            
            foreach ($items as $item) {
                $data = $item->getData();
                if (! isset($quotes[ $data['quote_id'] ])) {
                    $quotes[ $data['quote_id'] ] = array(
                        'customer_id'    => $data['customer_id'],
                        'items'            => array(),
                        'expired'        => array(),
                        'to_send'        => false
                    );
                }

                $quotes[ $data['quote_id'] ]['items'][] = $item;
                
                // if emailed then === False
                if ($item->leftReminderTime('email') === 0) {
                    $quotes[ $data['quote_id'] ]['to_send'] = true;
                    $quotes[ $data['quote_id'] ]['expired'][] = $item;
                }
            }

            foreach ($quotes as $quote) {
                if ($quote['to_send']) {
                    $customer = Mage::getModel('customer/customer')->load($quote['customer_id']);
                    
                    Mage::getModel('core/email_template')
                        ->sendTransactional(
                            Mage::getStoreConfig('cartreservation/reminders_email/template'), 
                            Mage::getStoreConfig('cartreservation/reminders_email/sender_identity'), 
                            $customer->getEmail(), 
                            Mage::app()->getStore()->getName(),
                            Mage::helper('cartreservation/customer')->getTemplateVariables($quote['items'], 'email', $customer)
                        );
                    foreach ($quote['expired'] as $item) {
                        $item->setEmailed(1)->save();
                    }
                }
            }
        }
    }
}