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

class Fontis_Tokens_Helper_Provider_Other extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * As an 'other' page, there's nothing much we can do, so just return null.
     *
     * @param string $key
     * @return null
     */
    public function getTokenValue($key)
    {
        return null;
    }
}
