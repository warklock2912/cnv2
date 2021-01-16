<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract extends Mage_Core_Model_Abstract
{
    protected $successCodeList = array(
        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT,
        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW
    );

    /**
     * @param null $orderId
     * @return stdClass
     */
    protected function iniRequest($orderId = null)
    {
        $request = new stdClass();

        $request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $request->merchantReferenceCode = $orderId ? $orderId : Mage::helper('core')->uniqHash();
        $request->clientLibrary = "PHP";
        $request->clientLibraryVersion = phpversion();

        return $request;
    }

    /**
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    protected function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_SOPWebMobile_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcesop')->isDebugMode());

        return $client;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $billing
     * @param String $email
     * @return stdClass
     */
    protected function buildBillingAddress($billing, $email)
    {
        if (! $email) {
            $email = $billing->getEmail();
        }

        $billTo = new stdClass();

        $billTo->company = substr($billing->getCompany(), 0, 40);
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->street1 = $billing->getStreet(1);
        $billTo->street2 = $billing->getStreet(2);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountry();
        $billTo->phoneNumber = $this->cleanPhoneNum($billing->getTelephone());
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = Mage::helper('core/http')->getRemoteAddr();

        return $billTo;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $shipping
     * @return bool|stdClass
     */
    protected function buildShippingAddress($shipping)
    {
        if (! $shipping) {
            return false;
        }

        $shipTo = new stdClass();

        $shipTo->company = substr($shipping->getCompany(), 0, 40);
        $shipTo->firstName = $shipping->getFirstname();
        $shipTo->lastName = $shipping->getLastname();
        $shipTo->street1 = $shipping->getStreet(1);
        $shipTo->street2 = $shipping->getStreet(2);
        $shipTo->city = $shipping->getCity();
        $shipTo->state = $shipping->getRegion();
        $shipTo->postalCode = $shipping->getPostcode();
        $shipTo->country = $shipping->getCountry();
        $shipTo->phoneNumber = $this->cleanPhoneNum($shipping->getTelephone());

        return $shipTo;
    }

    /**
     * @return bool
     */
    protected function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_SOPWebMobile_Source_Currency::DEFAULT_CURRENCY;
    }

    /**
     * @param string|int $num
     * @param int $pre
     * @return string
     */
    protected function formatNumber($num, $pre = 2)
    {
        return number_format($num, $pre, '.', '');
    }

    /**
     * @param string $phoneNumberIn
     * @return string|mixed
     */
    protected function cleanPhoneNum($phoneNumberIn)
    {
        return preg_replace("/[^0-9]/","", $phoneNumberIn);
    }
}
