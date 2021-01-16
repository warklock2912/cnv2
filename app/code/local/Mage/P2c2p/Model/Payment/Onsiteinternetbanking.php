<?php

class Mage_P2c2p_Model_Payment_Onsiteinternetbanking  extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'p2c2p_onsite_internet_banking';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canReviewPayment = true;

    protected $_formBlockType = 'p2c2p/form_p2c2p_onsiteinternetbanking';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('p2c2p/payment/ibanking', array(
            '_secure' => true,
        ));
    }

    public function getReturnUrl()
    {
        return Mage::getUrl('p2c2p/payment/success', array(
            '_secure' => true,
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Payment/Model/Method/Abstract.php
     */
    public function assignData($data)
    {
        parent::assignData($data);
            if (isset($data["onsite"])) {
                $channelData=json_decode($data["onsite"]);
                $channelData=get_object_vars($channelData);
                $this->getInfoInstance()->setAdditionalInformation("agentCode", $channelData["agentCode"]);
                $this->getInfoInstance()->setAdditionalInformation("channelCode", $channelData["channelCode"]);

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
//        $stateObject->setState("new");
//        $stateObject->setStatus('Pending_2C2P');
//        $payment = $this->getInfoInstance();
//        if ($payment->getAdditionalInformation('channelCode') != '' && $payment->getAdditionalInformation('agentCode') !='') {
//            $this->processPayment($payment);
//        }
//
//        return $this;
    }


}
