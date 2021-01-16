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

class Fontis_GoogleTagManager_Helper_Provider_Categories extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    const VIEWLIST_CONFIG = 'fontis_gtm/settings/viewlist';

    /**
     * @var string
     */
    protected $_enabledFlag = 'datalayercategories';

    /**
     * @return array
     */
    public function getData()
    {
        $codeList = explode(",", Mage::getStoreConfig('fontis_gtm/settings/categoryattributes'));
        $tokensArray = array();

        foreach ($codeList as $code) {
            if (!empty($code)) {
                $tokensArray[$code] = "[category:" . $code . "]";
            }
        }
        return $tokensArray;
    }
}
