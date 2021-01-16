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
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_GoogleTagManager_Helper_Provider_Products extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    /**
     * @var string
     */
    protected $_enabledFlag = 'datalayerproducts';

    /**
     * @return array
     */
    public function getData()
    {
        $codeList = explode(",", Mage::getStoreConfig('fontis_gtm/settings/productattributes'));
        $tokensArray = array();

        foreach ($codeList as $code) {
            if (!empty($code)) {
                $tokensArray[$code] = "[product:" . $code . "]";
            }
        }
        return $tokensArray;
    }
}
