<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Refund extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Refund
     * @throws Mage_Core_Exception
     */
	public function process($payment, $amount)
	{
		if ($this->useWebsiteCurrency()) {
			$amount = $this->formatNumber($payment->getCreditmemo()->getGrandTotal());
		}

        try {
            if (! $captureRequestId = $payment->getAdditionalInformation('captureRequestID')) {
                throw new Exception('Capture transaction was not found.');
            }

            $request = $this->iniRequest($payment->getOrder()->getIncrementId());

			$soapClient = $this->getSoapClient();

			$ccCreditService = new stdClass();
			$ccCreditService->run = "true";
			$ccCreditService->captureRequestID = $captureRequestId;
			$ccCreditService->captureRequestToken = $payment->getAdditionalInformation('captureRequestToken');
            $request->ccCreditService = $ccCreditService;

			$request->billTo = $this->buildBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());

            $purchaseTotals = new stdClass();
            $purchaseTotals->grandTotalAmount = $amount;
            $request->purchaseTotals = $purchaseTotals;

            $result = $soapClient->runTransaction($request);

            if ($result->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
                $payment->setAdditionalInformation('refundRequestID', $result->requestID);
                $payment->setAdditionalInformation('refundTransactionID', $result->requestID);

                $closeParent = $payment->getOrder()->canCreditmemo() ? 0 : 1;

                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction($closeParent);

                return $this;
            }

            throw new Exception(
                Mage::helper('cybersourcesop')->__('There is an error in refunding the payment. Please try again or process manually.')
            );
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
            );
        }

		return $this;
	}
}
