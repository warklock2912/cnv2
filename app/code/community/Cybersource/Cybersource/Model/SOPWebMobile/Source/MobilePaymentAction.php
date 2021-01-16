<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Source_MobilePaymentAction
{
    /**
     * Returns payment actions
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_AUTHORIZE,
                'label' => Mage::helper('cybersourcesop')->__('Authorize Only')
            ),
            array(
                'value' => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::ACTION_CAPTURE,
                'label' => Mage::helper('cybersourcesop')->__('Authorize & Capture')
            ),
        );
    }
}
