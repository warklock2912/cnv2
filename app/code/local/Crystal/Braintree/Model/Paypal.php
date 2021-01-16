<?php
/**
 * Created by PhpStorm.
 * User: icoldstyle
 * Date: 10/11/18
 * Time: 23:04
 */

class Crystal_Braintree_Model_Paypal extends Mage_Payment_Model_Method_Abstract{

    protected $_code = 'crystal_paypal';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;
    protected $_infoBlockType  = 'braintree/info_paypal';
    //protected $_isGateway = true;
    //protected $_canAuthorize = true;

    public function authorize(Varien_Object $payment, $amount) {
        Mage::log("Dummypayment\tIn authorize");
        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState("new");
        $stateObject->setStatus('Pending');
    }
}