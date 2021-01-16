<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_SOPWebMobile_Data extends Mage_Core_Helper_Abstract
{
    const LOGFILE = 'cybs_sa.log';

	/**
     * Get controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }
    
     /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {
        $route = array();
        if ($controller === "onepage") {
            $route['action'] = 'saveOrder';
            $route['controller'] = 'onepage';
            $route['module'] = 'checkout';
        }

        return $route;
    }

    public function getPaymentActionName($saveToken = false)
    {
		$config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();
		//this is a standard web mobile payment
        if ($this->isMobile()) {
            if (isset($config['mobile_payment_action']) && $config['mobile_payment_action'] == 'sale') {
                //user selected Capture
                $config['payment_action'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_CAPTURE;
            } else {
                //User selected Authorize
                $config['payment_action'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_AUTHORIZE;
            }
        }

        // Set payment action to save token (can only occur for new cards)
        if ($saveToken) {
            //user selected to save a token
            if ($this->isMobile()) { //Web / Mobile
                if ($config['mobile_payment_action'] == 'sale') {
                    //the user selected Authorize + Capture + Create Token
                    $config['payment_action'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_CAPTURE_CREATE_TOKEN;
                    //user wants to update token
                } else {
                    //the user selected Authorize + Create Token
                    $config['payment_action'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_AUTHORIZE_CREATE_TOKEN;
                }
            } else { //SOP
                if ($config['payment_action'] == 'authorize_capture') {
                    //the user selected Authorize + Capture + Create Token
                    $config['payment_action'] = 'authorize_capture_create_payment_token';
                } else {
                    //the user selected Authorize + Create Token
                    $config['payment_action'] = 'authorize_create_payment_token';
                }
            }
        }
        
       return $config['payment_action'];
	}
	
	public function isMobile()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $methodInstance = $quote->getPayment()->getMethodInstance();
        if ($methodInstance->getCode() == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE) {
            return false;
        }

        return (bool) Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('mobile_enabled');
    }

    public function getCybersourceUrl()
    {
        $isTestMode = Mage::helper('cybersource_core')->getIsTestMode();

        if ($this->isMobile()) {
            return $isTestMode
                ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::MOBILE_TESTURL
                : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::MOBILE_LIVEURL;
        }

        return $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::LIVEURL;
    }

    /**
     * @param array|string $message
     * @param bool $force
     * @return $this
     */
    public function log($message, $force = false)
    {
        if (!$this->isDebugMode() && !$force) {
            return $this;
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        Mage::log($message, null, self::LOGFILE, $force);

        return $this;
    }

    public function isDebugMode()
    {
        return (bool) Mage::getStoreConfig('payment/cybersourcesop/debug');
    }
}
