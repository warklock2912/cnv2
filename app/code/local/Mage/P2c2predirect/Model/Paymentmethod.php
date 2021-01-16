<?php

class Mage_P2c2predirect_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'p2c2predirect';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;

    protected $_formBlockType = 'p2c2predirect/form_p2c2predirect';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('p2c2predirect/payment/redirect', array(
            '_secure' => true,
        ));
    }

    public function getReturnUrl()
    {
        return Mage::getUrl('p2c2predirect/payment/success', array(
            '_secure' => true,
        ));
    }

    public function assignData($data)
    {
        if ($data->getCustomFieldOne()) {
            Mage::getSingleton('core/session')->setStoredCardId($data->getCustomFieldOne());
        }

        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState("new");
        $stateObject->setStatus('Pending_2C2P');
    }
}
