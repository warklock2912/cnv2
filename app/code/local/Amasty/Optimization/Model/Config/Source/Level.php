<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Config_Source_Level
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amoptimization');

        $vals = array(
            'WHITESPACE_ONLY'        => $hlp->__('Remove whitespaces (Recommended)'),
            'SIMPLE_OPTIMIZATIONS'   => $hlp->__('Simple code optimization'),
            'ADVANCED_OPTIMIZATIONS' => $hlp->__('Advanced code optimization'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                'value' => $k,
                'label' => $v
            );

        return $options;
    }
}
