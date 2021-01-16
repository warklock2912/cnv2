<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Task extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amoptimization/task');
    }

    public function process()
    {
        $path = $this->getData('path');

        if (!file_exists($path))
            return false;

        /** @var Amasty_Optimization_Model_Minification_Minificator $minificator */
        $minificator = Mage::getSingleton('amoptimization/minification_minificator_' . $this->getData('minificator_code'));
        $success = $minificator->minifyInPlace($path);

        return $success;
    }
}
