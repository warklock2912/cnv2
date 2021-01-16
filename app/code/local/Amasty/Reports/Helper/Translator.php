<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Helper_Translator extends Mage_Core_Helper_Abstract
{
    const CSV_SEPARATOR     = ',';

    protected $_locale;

    public function loadModuleTranslation($moduleName)
    {
        $file = $this->_getModuleFilePath($moduleName);
        $data = $this->_getFileData($file);
        return $data;
    }

    protected function _getModuleFilePath($fileName)
    {
        $file = Mage::getBaseDir('locale');
        $file.= DS.$this->getLocale().DS.$fileName.'.csv';
        return $file;
    }

    protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $parser = new Varien_File_Csv();
            $parser->setDelimiter(self::CSV_SEPARATOR);
            $data = $parser->getDataPairs($file);
        }
        return $data;
    }

    public function getLocale()
    {
        if (is_null($this->_locale)) {
            $this->_locale = Mage::app()->getLocale()->getLocaleCode();
        }
        return $this->_locale;
    }
}