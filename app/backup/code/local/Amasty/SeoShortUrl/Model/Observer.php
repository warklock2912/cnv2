<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoShortUrl
 */
class Amasty_SeoShortUrl_Model_Observer
{
    public function handleControllerFrontInitRouters($observer)
    {
        if (!Mage::helper('amseoshorturl')->isDeniedModule()) {
            $observer->getEvent()->getFront()
                     ->addRouter('amseoshorturl', new Amasty_SeoShortUrl_Controller_Router());
        }
    }

    public function settingsChanged()
    {
        if (!Mage::helper('amseoshorturl')->isDeniedModule()) {
            $this->invalidateCache();
        }
    }

    public function attributeChanged()
    {
        if (!Mage::helper('amseoshorturl')->isDeniedModule()) {
            $this->invalidateCache();
        }
    }

    protected function invalidateCache()
    {
        $this->_getDataHelper()->invalidateCache();
    }

    protected function _getDataHelper()
    {
        /** @var Amasty_SeoShortUrl_Helper_Data $helper */
        $helper = Mage::helper('amseoshorturl');
        return $helper;
    }
}
