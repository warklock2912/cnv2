<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Processor_Footerjs extends Amasty_Optimization_Model_Minification_Processor
{
    public function process($html)
    {
        if (Mage::app()->getRequest()->isAjax())
            return $html;

        $html = Mage::helper('amoptimization/js')->moveJsToFooter($html);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinificator()
    {
        return null;
    }
}
