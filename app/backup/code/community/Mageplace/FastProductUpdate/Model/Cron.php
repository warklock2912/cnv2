<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */


class Mageplace_FastProductUpdate_Model_Cron extends Mage_Core_Model_Config_Data
{
    protected function run()
    {
        $file = trim(Mage::getStoreConfig('mpfastproductupdate/general/import_file_path'));
        if (!$file) {
            $e = new Exception(Mage::helper('mpfastproductupdate')->__('Wrong configuration file'));
            Mage::logException($e);
            throw $e;
        }

        $file = rtrim($file, DS);
        if (DS != substr($file, 0, 1)) {
            $file = Mage::getBaseDir() . DS . $file;
        }

        if (@substr(strrchr($file, '.'), 1) != 'csv') {
            $e = new Exception(Mage::helper('mpfastproductupdate')->__('Input file must be in CSV format'));
            Mage::logException($e);
            throw $e;
        }

        Mage::helper('mpfastproductupdate')->parse($file);
    }
}