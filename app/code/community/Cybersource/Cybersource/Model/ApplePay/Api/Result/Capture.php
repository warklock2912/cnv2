<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture extends Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
{
    /**
     * What is the decision result for a capture transaction?
     */
    const RESULT_SUCCESSFUL_DECISION = 'ACCEPT';

    /**
     * Does the result object indicate a successful API call?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return
            !empty($this->getRawResult()['decision'])
            && $this->getRawResult()['decision'] == self::RESULT_SUCCESSFUL_DECISION;
    }

    /**
     * Get the transaction ID from the result
     *
     * @return string
     */
    public function getRequestId()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['requestID'])) {
                return $result['requestID'];
            }
        }
        return '';
    }

    /**
     * Retrieve the amount that was captured
     *
     * @return float
     */
    public function getCapturedAmount()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['ccCaptureReply']['amount'])) {
                return $result['ccCaptureReply']['amount'];
            }
        }
        return 0;
    }

    /**
     * Get the currency code for the purchase total
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['purchaseTotals']['currency'])) {
                return $result['purchaseTotals']['currency'];
            }
        }
        return '';
    }
}
