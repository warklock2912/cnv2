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

class Fontis_GoogleTagManager_Model_Source_Attributes_Products extends Fontis_GoogleTagManager_Model_Source_Attributes_Abstract
{
    /**
     * @var string
     */
    protected $_eavCode = 'catalog_product';

    /**
     * @param string $attributeCode
     * @return array|null
     */
    protected function handleAttributeSpecially($attributeCode)
    {
        switch ($attributeCode) {
            case 'quantity':
                return array('position' => 0, 'value' => 'qty', 'label' => 'Quantity (qty)');
            default:
                return null;
        }
    }

    /**
     * @return array
     */
    protected function getExtraArrayElements()
    {
        return array(
            array('position' => 0, 'value' => 'available', 'label' => 'Available (available)'),
        );
    }
}
