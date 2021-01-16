<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_Validation
{
    /**
     * Retrieve additional validation types
     *
     * @return array
     */
    public function getAdditionalValidation()
    {
        $addon = array();
        $files = $this->_getValidationFiles();
        foreach ($files as $file) {
            if (false !== strpos($file, '.php')) {
                $addon[] = Mage::getModel(
                    'amcustomerattr/validation_' . str_replace(
                        '.php', '', $file
                    )
                )->getValues();
            }
        }
        return $addon;
    }

    protected function _getValidationFiles()
    {
        $path = Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'local'
            . DS . 'Amasty' . DS . 'Customerattr' . DS . 'Model' . DS
            . 'Validation';
        $files = scandir($path);
        return $files;
    }

    /**
     * Retrieve JS code
     *
     * @return string
     */
    public function getJS()
    {
        $js = '';
        $files = $this->_getValidationFiles();
        foreach ($files as $file) {
            if (false !== strpos($file, '.php')) {
                $js .= Mage::getModel(
                    'amcustomerattr/validation_' . str_replace(
                        '.php', '', $file
                    )
                )->getJS();
            }
        }
        return $js;
    }
}
