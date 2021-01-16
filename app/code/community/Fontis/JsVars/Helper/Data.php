<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2017 Fontis Pty. Ltd. (https://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_JsVars_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return string|null
     */
    public function getContainerVariablePrefix()
    {
        if ($prefix = Mage::getStoreConfig("fontis_jsvars/settings/container_variable_prefix")) {
            return $prefix;
        } else {
            return null;
        }
    }
}

