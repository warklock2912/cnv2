<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Observer_Cron
{
    public function processQueue()
    {
        $lockFname = Mage::getBaseDir('tmp'). DS . 'amasty_optimization_process.lock';
        $fp = fopen($lockFname, 'w');

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            return;
        }

        Mage::getSingleton('amoptimization/minification_queueProcessor')->process();

        fclose($fp);
    }
}
