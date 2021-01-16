<?php
    class Crystal_Twoctwop_Model_Twoctwop extends Mage_Payment_Model_Method_Abstract{

        protected $_code = 'crystal_twoctwop';
        protected $_isInitializeNeeded = true;
        protected $_canUseInternal = true;
        protected $_canUseCheckout = true;
        protected $_canUseForMultishipping = true;
        protected $_canAuthorize = true;
        protected $_canCapture = true;
        protected $_canRefund = true;
        protected $_canReviewPayment = true;
        //protected $_isGateway = true;
        //protected $_canAuthorize = true;

        public function authorize(Varien_Object $payment, $amount) {
            Mage::log("Dummypayment\tIn authorize");
            return $this;
        }

        public function initialize($paymentAction, $stateObject)
        {
            $stateObject->setState("new");
            $stateObject->setStatus('Pending_2C2P');
        }
    }
