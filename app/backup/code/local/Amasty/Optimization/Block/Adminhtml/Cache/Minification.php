<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Block_Adminhtml_Cache_Minification extends Mage_Adminhtml_Block_Template
{
    public function getFlushUrl()
    {
        return $this->getUrl('adminhtml/amoptimization_cache/flush');
    }
}
