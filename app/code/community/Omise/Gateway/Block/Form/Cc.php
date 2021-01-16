<?php

require_once Mage::getBaseDir().'/lib/omise-php/lib/Omise.php';
$config = Mage::getModel('omise_gateway/config')->load(1);
if($config->getTestMode()){
    define('OMISE_PUBLIC_KEY', $config->getPublicKeyTest());
    define('OMISE_SECRET_KEY', $config->getSecretKeyTest());
}else {
    define('OMISE_PUBLIC_KEY', $config->getPublicKey());
    define('OMISE_SECRET_KEY', $config->getSecretKey());
}
class Omise_Gateway_Block_Form_Cc extends Mage_Payment_Block_Form
{
    protected  $_customerOmise;

    /**
     * Preparing global layout
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     *
     * @see    Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if ($this->_isApplicable()) {
            $this->setTemplate('payment/form/omise/omisecc.phtml');
        } else {
            $this->setTemplate('payment/form/omise/omise-inapplicable-method.phtml');
        }

        return $this;
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Check if the payment method is applicable for the checkout form.
     *
     * @return bool
     */
    protected function _isApplicable()
    {
        return $this->_isStoreCurrencySupported();
    }

    /**
     * @return bool
     */
    protected function _isStoreCurrencySupported()
    {
        return in_array(
            Mage::app()->getStore()->getBaseCurrencyCode(),
            array('JPY', 'THB', 'SGD', 'USD', 'EUR', 'GBP')
        );
    }

    /**
     * Whether the One Step Checkout Support option is enabled
     *
     * @return bool
     */
    public function isOscSupportEnabled()
    {
        return Mage::getModel('omise_gateway/payment_creditcard')->isOscSupportEnabled();
    }

    /**
     * Retrieve Omise keys from database
     *
     * @return string|array
     */
    public function getOmiseKeys($omise_key = '')
    {
        // Create a new model instance and query data from 'omise_gateway' table.
        $config = Mage::getModel('omise_gateway/config')->load(1);

        if ($config->test_mode) {
            $data['public_key'] = $config->public_key_test;
            $data['secret_key'] = $config->secret_key_test;
        } else {
            $data['public_key'] = $config->public_key;
            $data['secret_key'] = $config->secret_key;
        }

        if ($omise_key == '') {
            return $data;
        }

        return isset($data[$omise_key]) ? $data[$omise_key] : '';
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }

        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    /**
     * Retrieve has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        return true;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent(
            'payment_form_block_to_html_before',
            array('block' => $this)
        );

        return parent::_toHtml();
    }


    public function getListCard()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getCustomerApiId()) {

                $currentCustomerApi = OmiseCustomer::retrieve($customer->getCustomerApiId());

                $this->_customerOmise = $currentCustomerApi;
                return $currentCustomerApi['cards']['data'];
            }
        }
        return false;
    }

    public function getCardDefault(){
        $customerOmise = $this->getCustomerOmise();
        return $customerOmise['default_card'];
    }

    public function getCustomerOmise(){
        return $this->_customerOmise;
    }

}
