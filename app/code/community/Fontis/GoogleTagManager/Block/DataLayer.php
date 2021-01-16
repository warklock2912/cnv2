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

abstract class Fontis_GoogleTagManager_Block_DataLayer extends Mage_Core_Block_Abstract
{
    /**
     * Because the personalised cache will assume an empty string is a falsey
     * value, it will keep coming back here to try and load this unless we give
     * it something pointless, but meaty, like empty script tags.
     * Note that HTML comments are not counted as "meaty".
     *
     * @return string
     */
    protected function getEmptyContent()
    {
        return '<script></script>';
    }
}
