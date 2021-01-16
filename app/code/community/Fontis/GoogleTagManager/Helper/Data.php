<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_GoogleTagManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns true if GTM is enabled.
     *
     * @return bool
     */
    public function isGtmEnabled()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/active') && $this->getContainerId();
    }

    /**
     * Returns the GTM container ID.
     *
     * @return string
     */
    public function getContainerId()
    {
        return trim(Mage::getStoreConfig('fontis_gtm/settings/containerid'));
    }

    /**
     * Returns true if ecommerce data is to be included in the data layer.
     *
     * @return bool
     */
    public function includeEcommerceData()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/datalayerecommerce');
    }

    /**
     * Returns true if visitor data is to be included in the data layer.
     *
     * @return bool
     */
    public function includeVisitorData()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/datalayervisitors');
    }

    /**
     * Returns the value used for the transactionAffiliation field in the data
     * layer. If left blank in the admin panel config, will return the current
     * store code.
     *
     * @return string
     */
    public function getTransactionAffiliation()
    {
        $aff = Mage::getStoreConfig('fontis_gtm/settings/datalayertransactionaffiliation');
        if (!$aff) {
            return Mage::app()->getStore()->getCode();
        } else {
            return $aff;
        }
    }

    /**
     * @return array
     */
    public function getCustomerAttributes()
    {
        $codeList = explode(",", Mage::getStoreConfig('fontis_gtm/settings/customerattributes'));
        $tokensArray = array();

        foreach ($codeList as $code) {
            if (!empty($code)) {
                $tokensArray[$code] = "[customer:" . $code . "]";
            }
        }
        return $tokensArray;
    }

    /**
     * We want to turn this block thing off when it's not relevant.
     *
     * @return bool
     */
    public function getIsPersonalised()
    {
        if ($this->isGtmEnabled() && ($this->includeEcommerceData() || $this->includeVisitorData())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getPageType()
    {
        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'cms') {
            return "cms-" . Mage::getSingleton('cms/page')->getIdentifier();
        }
        if (Mage::app()->getFrontController()->getRequest()->getControllerName() == 'product') {
            return Fontis_GoogleTagManager_Helper_Provider_Abstract::PAGETYPE_PRODUCT;
        }
        return Mage::app()->getFrontController()->getRequest()->getRouteName();
    }
}
