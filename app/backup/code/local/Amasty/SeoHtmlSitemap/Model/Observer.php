<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Model_Observer
{
    public function onAdminhtmlInitSystemConfig($observer)
    {
        if (!Mage::helper('core')->isModuleEnabled('Amasty_Xlanding')) {
            $observer->getConfig()->setNode(
                'sections/amseohtmlsitemap/groups/landing/show_in_default', 0, true
            );
        }
    }
}