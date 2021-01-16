<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Auth extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Auth
     * @throws Mage_Core_Exception
     */
	public function process($payment)
	{
        $config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();

        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest($payment->getOrder()->getIncrementId());

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $request->ccAuthService = $ccAuthService;

        $request->billTo = $this->buildBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
        if ($shippingAddress = $this->buildShippingAddress($payment->getOrder()->getShippingAddress())) {
            $request->shipTo = $shippingAddress;
        }
        $currency = $payment->getOrder()->getBaseCurrencyCode();
        $amount = $this->formatNumber($payment->getOrder()->getBaseGrandTotal());

        if ($this->useWebsiteCurrency()) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            $amount = $this->formatNumber($payment->getOrder()->getGrandTotal());
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $businessRules = new stdClass();
        if (! empty($config['forceavs'])) {
            if ($config['forceavs'] === (string)Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
                $businessRules->ignoreAVSResult = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::IGNORE_RULE_FALSE;
            } else {
                $businessRules->ignoreAVSResult = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::IGNORE_RULE_TRUE;
            }
        }
        if (! empty($config['forcecvn'])) {
            if ($config['forcecvn'] === (string)Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
                $businessRules->ignoreCVResult =  Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::IGNORE_RULE_FALSE;
            } else {
                $businessRules->ignoreCVResult = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::IGNORE_RULE_TRUE;
            }
        }
        $request->businessRules = $businessRules;

        try {			
            $result = $soapClient->runTransaction($request);

            if (in_array($result->reasonCode, $this->successCodeList)) {
                $payment->setAdditionalInformation('authTransactionID', $result->requestID);
                $payment->setAdditionalInformation('authRequestID', $result->requestID);
                $payment->setAdditionalInformation('authRequestToken', $result->requestToken);

                $payment->setLastTransId($result->requestID);
                $payment->setCcTransId($result->requestID);
                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(0);

                if (isset($result->ccAuthReply->avsCode)){
                    $payment->setCcAvsStatus($result->ccAuthReply->avsCode);
                }

                if (isset($result->ccAuthReply->cvCode)) {
                    $payment->setCcCidStatus($result->ccAuthReply->cvCode);
                }

                if ($result->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
                    $payment->setIsTransactionPending(1);
                    $payment->setIsFraudDetected(1);
                    Mage::getSingleton('adminhtml/session')->addWarning(
                        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getDmWarningMsg()
                    );
                }

                return $this;
            }

            throw new Exception('auth failed with reason code: ' . $result->reasonCode);
        } catch (Exception $e) {
           Mage::throwException(
                Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
            );
        }

        return $this;
	}
}
