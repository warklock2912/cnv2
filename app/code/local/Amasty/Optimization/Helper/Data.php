<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function flushCache()
    {
        $cacheDir = Mage::getSingleton('amoptimization/minification_minificator_js')->getDestDir();

        $this->rmdir($cacheDir);

        Mage::app()->cleanCache('AMFPC');
    }

    public function rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->rmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }
}
