<?php
class Omise_Gateway_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function formatPrice($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'THB':
                $amount = "฿" . number_format(($amount / 100), 2);
                if (preg_match('/\.00$/', $amount)) {
                    $amount = substr($amount, 0, -3);
                }
                break;

            case 'SGD':
                $amount = "S$" . number_format(($amount / 100), 2);
                if (preg_match('/\.00$/', $amount)) {
                    $amount = substr($amount, 0, -3);
                }
                break;

            case 'JPY':
                $amount = number_format($amount) . "円";
                break;
        }

        return $amount;
    }

    public function restoreQuote($order){
        if ($order->getId())
        {
            $session = Mage::getSingleton('checkout/session');
            if ($session->getLastRealOrderId())
            {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED)
                {
                    $order->registerCancellation("Canceled by Payment Provider")->save();
                }
                $quote = Mage::getModel('sales/quote')
                    ->load($order->getQuoteId());
                //Return quote
                if ($quote->getId())
                {
                    $quote->setIsActive(1)
                        ->setReservedOrderId(NULL)
                        ->save();
                    $session->replaceQuote($quote);
                }

                //Unset data
                $session->unsLastRealOrderId();
            }
        }
    }
}
