<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Void extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Void
     * @throws Mage_Core_Exception
     */
	public function process($payment)
	{
	    $amount = $payment->getOrder()->getBaseGrandTotal();
        if ($this->useWebsiteCurrency()) {
            $amount = $payment->getOrder()->getGrandTotal();
        }

        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest($payment->getOrder()->getIncrementId());

        $ccAuthReversalService = new stdClass();
        $ccAuthReversalService->run = "true";
        $ccAuthReversalService->authRequestID = $payment->getAdditionalInformation('authRequestID');
        $ccAuthReversalService->reversalReason = 'incomplete';

        $purchaseTotals = new stdClass();
        $purchaseTotals->grandTotalAmount = $amount;

        $request->purchaseTotals = $purchaseTotals;
        $request->ccAuthReversalService = $ccAuthReversalService;

        try {
            $result = $soapClient->runTransaction($request);

            if ($result->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
                $payment->setAdditionalInformation('reversalRequestID', $result->requestID);
                $payment->setAdditionalInformation('reversalRequestToken', $result->requestToken);

                $payment->setCcTransId($result->requestID);
                $payment->setLastTransId($result->requestID);
                $payment->setTransactionId($result->requestID);
                $payment->setShouldCloseParentTransaction(1);
                $payment->setIsTransactionClosed(1);

                return $this;
            }

            throw new Exception('Failed to void payment with reason code ' . $result->reasonCode);
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
            );
        }

		return $this;
	}
}
