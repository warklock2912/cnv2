<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Processor_Css extends Amasty_Optimization_Model_Minification_Processor
{
    public function process($html)
    {
        // exact match for code generated in app/code/core/Mage/Page/Block/Html/Head.php
        $html = preg_replace_callback(
            '#(?P<prefix><link[\s]rel="stylesheet" type="text/css" href=")(?P<url>.+?)(?P<suffix>[\'"])#',
            array($this, 'match'),
            $html
        );

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinificator()
    {
        return Mage::getSingleton('amoptimization/minification_minificator_css');
    }
}
