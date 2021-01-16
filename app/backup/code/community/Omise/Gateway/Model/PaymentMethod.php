<?php
class Omise_Gateway_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /** @var string */
    protected $_code            = 'omise_gateway';

    /** @var string */
    protected $_formBlockType   = 'omise_gateway/form_cc';

    /** @var string */
    protected $_infoBlockType   = 'payment/info_cc';

    public function isAvailable($quote = null)
    {
        $isActive =  (bool)(int)Mage::getStoreConfig('payment/omise_gateway/active');
        $CurrentAmount = (double)Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal();
        //=> Allowed IP Address
        if (trim(Mage::getStoreConfig('payment/omise_gateway/allowedip')) != "") {
            $ClientIP = Mage::helper('core/http')->getRemoteAddr();
            if (trim(Mage::getStoreConfig('payment/omise_gateway/allowedip')) != $ClientIP) {
                $isActive = false;
            }
        }

        return $isActive;
    }

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway       = true;
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;
    protected $_canOrder        = true;
    private $_chargeDetail;
    /**
     * Authorize payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    // public function authorize(Varien_Object $payment, $amount)
    // {
    //     Mage::log("Start authorize with OmiseCharge API! ", null, 'Omise_Authorize.log');
    //     $data = $payment->getData('additional_information');
    //     // $response_omise = Mage::getModel('omise_gateway/omiseToken')->createToken($payment->getData('additional_information'));
    //     // $omise_token = $response_omise['card'];
    //     // $omise_token['omise_token'] = $response_omise['id'];
    //     // $this->getInfoInstance()->setAdditionalInformation('omise_token', $omise_token['omise_token']);
    //     $order = Mage::getModel('sales/order')->load($payment->getData('parent_id'));
    //     $increment_id = $order->getData('increment_id');
    //
    //     $this->getInfoInstance()->setAdditionalInformation('omise_card_id', $omise_token['id']);
    //     $charge = Mage::getModel('omise_gateway/omiseCharge')->createOmiseCharge(array(
    //         "amount"        => number_format($amount, 2, '', ''),
    //         "currency"      => "thb",
    //         "description"   => 'Charge a card from Magento that order id is '.$increment_id,
    //         "capture"       => false,
    //         'return_uri'    => Mage::getUrl('test/test'),
    //         "card"          => $data['omise_token']
    //     ));
    //     Mage::log(print_r($charge,true),null,'test3d.log',true);
    //     if (isset($charge['error'])){
    //         Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));
    //     }
    //
    //     if (!$charge['authorized']){
    //         Mage::throwException(Mage::helper('payment')->__('Your authorize failed:: ('.$charge['failure_code'].') - '.$charge['failure_code']));
    //     }
    //     $this->getInfoInstance()->setAdditionalInformation('reference', $charge['reference']);
    //     Mage::getSingleton('core/session')->setRedirecturl($charge['authorize_uri']);
    //     $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge['id']);
    //     $this->chargeDetail = $charge;
    //     Mage::log('This transaction was authorized! (by OmiseCharge API)');
    //     return $this;
    // }

    /**
     * Capture payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $additional_information = $payment->getData('additional_information');
        $authorized = isset($additional_information['omise_charge_id']) ? $additional_information['omise_charge_id'] : false;

        if ($authorized) {
            // Capture only.
            Mage::log("Start capture with OmiseCharge API! - A", null, 'Omise_Capture.log',true);

            $charge = Mage::getModel('omise_gateway/omiseCharge')->captureOmiseCharge($authorized);
        } else {
            // Authorize and capture.
            Mage::log("Start capture with OmiseCharge API! - B", null, 'Omise_Capture.log',true);
            $data = $payment->getData('additional_information');
            // $response_omise = Mage::getModel('omise_gateway/omiseToken')->createToken($payment->getData('additional_information'));
            // $omise_token = $response_omise['card'];
            // $omise_token['omise_token'] = $response_omise['id'];
            // $this->getInfoInstance()->setAdditionalInformation('omise_token', $omise_token['omise_token']);
            // $this->getInfoInstance()->setAdditionalInformation('omise_card_id', $omise_token['id']);


            $order = Mage::getModel('sales/order')->load($payment->getData('parent_id'));
            $increment_id = $order->getData('increment_id');

            $charge = Mage::getModel('omise_gateway/omiseCharge')->createOmiseCharge(array(
                "amount"        => number_format($amount, 2, '', ''),
                "currency"      => "thb",
                'return_uri'    => Mage::getUrl('omise/gateway/callback').'orderid/'.$order->getId(),
                "description"   => 'Charge a card from Magento that order id is '.$increment_id,
                "card"          => $data['omise_token']
            ));
            $this->getInfoInstance()->setAdditionalInformation('reference', $charge['reference']);
            Mage::getSingleton('core/session')->setRedirecturl($charge['authorize_uri']);
            Mage::log(print_r($charge,true),null,'test3d.log',true);
        }

        if (isset($charge['error'])){
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));
        }

        if (!$charge['capture'])
            Mage::throwException(Mage::helper('payment')->__('Your payment failed:-: ('.$charge['failure_code'].') - '.$charge['failure_code']));
        $this->chargeDetail = $charge;
        Mage::log('This transaction was authorized and capture! (by OmiseCharge API)');
        return $this;
    }


    /**
     * Assign data to info model instance
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);
        $this->getInfoInstance()->setAdditionalInformation('name',$data->getData('cc_name'));
        $this->getInfoInstance()->setAdditionalInformation('number',$data->getData('cc_number'));
        $this->getInfoInstance()->setAdditionalInformation('expiration_month',$data->getData('cc_exp_month'));
        $this->getInfoInstance()->setAdditionalInformation('expiration_year',$data->getData('cc_exp_year'));
        $this->getInfoInstance()->setAdditionalInformation('security_code',$data->getData('cc_cid'));
        $this->getInfoInstance()->setAdditionalInformation('omise_token',$data->getData('omise_token'));
        Mage::log(print_r($data,true),null,'omise_assignData.log',true);
        // if (is_array($data)) {
        //     if (!isset($data['omise_token']))
        //         Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

        //     Mage::log('Data that assign is Array',true);
        //     $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        // } elseif ($data instanceof Varien_Object) {
        //     if (!$data->getData('omise_token'))
        //         Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

        //     Mage::log('Data that assign is Object');
        //     $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        // }

        return $result;
    }

    public function getOrderPlaceRedirectUrl()
    {
        Mage::log(Mage::getSingleton('core/session')->getRedirecturl(),null,'testomise.log',true);
        return Mage::getSingleton('core/session')->getRedirecturl();
    }
}
