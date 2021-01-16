<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Adminhtml_System_Cvncomment
{
    /**
     * Loads CVN template to be shown in system config.
     * @return mixed
     */
    public function getCommentText()
    {
        return Mage::getBlockSingleton('cybersourcesop/cvncomment')->getCommentHtml();
    }
}
