<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Observer
{
    public function onBlockToHtmlAfter($observer)
    {
        $hlp = Mage::helper('amoptimization/js');

        if (!$hlp->isFooterJsEnabled())
            return;

        if (Mage::app()->getRequest()->isAjax())
            return;

        $transport = $observer->getTransport();

        if (Mage::registry('amfpc_blocks')) { // Cache hit with block updates
            $html = $hlp->removeJs($transport->getHtml());
            $transport->setHtml($html);
        }
    }
}
