<?php
class MagentoMasters_MasterCheckout_Block_Info extends Mage_Checkout_Block_Onepage_Review_Info
{
    /**
     * Get current order's billing address
     * @return object
     */
    public function billingAddress() {
        $checkout = Mage::getSingleton('checkout/session')->getQuote();
        $billingAddress = $checkout->getBillingAddress();
        return $billingAddress;
    }

    /**
     * Get current order's shipping address
     * @return object
     */
    public function shippingAddress() {
        $checkout = Mage::getSingleton('checkout/session')->getQuote();
        $shippingAddress = $checkout->getShippingAddress();
        return $shippingAddress;
    }
}