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

class Fontis_GoogleTagManager_Model_Source_Attributes_Hashedemail
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'position' => 0,
                'value' => Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_DISABLED,
                'label' => 'Disabled',
            ),
            array(
                'position' => 1,
                'value' => Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_SHA1,
                'label' => 'Enabled (SHA1)',
            ),
            array(
                'position' => 2,
                'value' => Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_MD5,
                'label' => 'Enabled (MD5)',
            ),
        );
    }
}
