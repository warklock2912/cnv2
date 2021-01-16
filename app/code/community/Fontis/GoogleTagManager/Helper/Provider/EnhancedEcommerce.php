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

class Fontis_GoogleTagManager_Helper_Provider_EnhancedEcommerce extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->includeEcommerceData() &&
        ($this->getEcommerceDataType() == Fontis_GoogleTagManager_Model_Source_EcommerceType::ECOMMERCE_TYPE_ENHANCED);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();
        $items = Mage::getConfig()->getNode('frontend/enhancedecommerce')->asArray();

        foreach ($items as $key => $item) {
            $value = Mage::helper($item)->getData();

            if (empty($value)) {
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
