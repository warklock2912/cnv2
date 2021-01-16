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
 * @copyright  Copyright (c) 2015 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_Tokens_Helper_Provider_Cms extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * Get Custom CMS page data
     *
     * Simply looks in current page's data array.
     *
     * @param string $key
     * @return string|null
     */
    public function getTokenValue($key)
    {
        $page = Mage::getSingleton('cms/page');

        // page attributes
        if ($page->hasData($key)) {
            return $page->getData($key);
        }

        return null;
    }
}
