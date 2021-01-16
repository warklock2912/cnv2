<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   Auto-Cancel Orders
 * @type        Order management
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php 
 * 
 * @category    Magento Commerce
 * @package     Appmerce_AutoCancel
 * @copyright   Copyright (c) 2011-2013 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_AutoCancel_Model_Source_Timestamp
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'created_at',
                'label' => Mage::helper('autocancel')->__('Order Creation Time'),
            ),
            array(
                'value' => 'updated_at',
                'label' => Mage::helper('autocancel')->__('Order Last Updated Time'),
            ),
        );
    }

}
