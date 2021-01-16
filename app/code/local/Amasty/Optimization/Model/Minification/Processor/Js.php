<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Processor_Js extends Amasty_Optimization_Model_Minification_Processor
{
    public function process($html)
    {
        $html = preg_replace_callback(
            '#(?P<prefix><script.+?src\w*=\w*[\'"])(?P<url>.+?)(?P<suffix>[\'"])#',
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
        return Mage::getSingleton('amoptimization/minification_minificator_js');
    }
}
