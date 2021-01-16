<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Model_Observer
{

    public function checkCartAdd(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()->getControllerAction()->getFullActionName() == "checkout_cart_add") {
            $result    = false;
            $productId = Mage::app()->getRequest()->getParam('product');
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            $activeRules = Mage::helper('amgroupcat')->getActiveRulesForProductPrice($product);
            if ($activeRules) {
                $result = true;
            }
            // check deeper rules (product categories, forbid and etc)
            $productChecker = Mage::getModel('amgroupcat/observerProduct');
            $result         = $result || $productChecker->checkProductRestrictions($observer, $productId, false);

            if ($result) {
                Mage::getSingleton('core/session')->addError( Mage::helper('amgroupcat')->__('This product is restricted to purchase'));
                $lastUrl = Mage::getSingleton('core/session')->getLastUrl();
                header("Location: " . $lastUrl);
                Mage::helper('ambase/utils')->_exit();
            }
        }
    }
}
