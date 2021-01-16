<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


class Amasty_Customform_Helper_Captcha extends Mage_Captcha_Helper_Data
{


    /**
     * Get Captcha
     *
     * @param string $formId
     * @return Mage_Captcha_Model_Interface
     */
    public function getCaptcha($formId)
    {
        if (!array_key_exists($formId, $this->_captcha)) {
            $this->_captcha[$formId] = Mage::getModel('amcustomform/captcha', array('formId' => $formId));
        }
        return $this->_captcha[$formId];
    }

    /**
     * Returns value of the node with respect to current area (frontend or backend)
     *
     * @param string $id The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param Mage_Core_Model_Store $store
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfigNode($id, $store = null)
    {
        //$areaCode = 'amcustomform';
        $areaCode = 'amcustomform';
        return Mage::getStoreConfig( $areaCode . '/captcha/' . $id, $store);
    }



}