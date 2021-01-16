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

class Appmerce_AutoCancel_Model_Source_Periods
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 15,
                'label' => Mage::helper('autocancel')->__('15m'),
            ),
            array(
                'value' => 30,
                'label' => Mage::helper('autocancel')->__('30m'),
            ),
            array(
                'value' => 60,
                'label' => Mage::helper('autocancel')->__('1h'),
            ),
            array(
                'value' => 120,
                'label' => Mage::helper('autocancel')->__('2h'),
            ),
            array(
                'value' => 180,
                'label' => Mage::helper('autocancel')->__('3h'),
            ),
            array(
                'value' => 360,
                'label' => Mage::helper('autocancel')->__('6h'),
            ),
            array(
                'value' => 720,
                'label' => Mage::helper('autocancel')->__('12h'),
            ),
            array(
                'value' => 1440,
                'label' => Mage::helper('autocancel')->__('1d'),
            ),
            array(
                'value' => 2880,
                'label' => Mage::helper('autocancel')->__('2d'),
            ),
        );
    }

}
