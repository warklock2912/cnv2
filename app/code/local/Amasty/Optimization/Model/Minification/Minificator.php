<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


abstract class Amasty_Optimization_Model_Minification_Minificator
{
    protected $destDir;

    public function __construct()
    {
        $this->destDir = $this->getDestDir();
    }

    public function isDeferred()
    {
        return false;
    }

    abstract public function getCode();

    abstract protected function minifyFile($path);

    public function getDestDir()
    {
        return Mage::getBaseDir() . DS . $this->getRelativeDestDir();
    }

    public function getRelativeDestDir()
    {
        return 'media' . DS . 'amoptimization';
    }

    /**
     * @param string $path relative path of source file
     *
     * @return string relative path of minified file
     */
    public function minify($path)
    {
        $destination = $this->destDir . DS . $path;
        $minifiedPath = $this->getRelativeDestDir() . DS . $path;

        if (file_exists($destination)
            && filemtime($destination) >= filemtime(Mage::getBaseDir() . DS . $path)
        ) {
            return $minifiedPath;
        }

        $destDir = dirname($destination);

        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        copy($path, $destination);

        if ($this->isDeferred()) {
            Mage::getResourceSingleton('amoptimization/task')
                ->scheduleTask($destination, $this->getCode());
        }
        else {
            $this->minifyInPlace($destination);
        }

        return $minifiedPath;
    }

    public function minifyInPlace($path)
    {
        if (!is_writable($path)) {
            if (Mage::getStoreConfigFlag('amoptimization/debug/log_minification_errors')) {
                Mage::log(
                    "Minification failed. Not enough permissions for '{$path}'.",
                    Zend_Log::WARN,
                    '',
                    true
                );
            }

            return false;
        }

        return $this->minifyFile($path);
    }
}
