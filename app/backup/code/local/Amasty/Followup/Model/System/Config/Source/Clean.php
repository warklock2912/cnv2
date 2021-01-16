<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

class Amasty_Followup_Model_System_Config_Source_Clean
{
    public function toOptionArray()
    {
        $helper = Mage::helper('amfollowup');

        return array(
            array(
                'label' => $helper->__('No'),
                'value' => '0'
            ),
            array(
                'label' => $helper->__('In 30 days'),
                'value' => '30'
            ),
            array(
                'label' => $helper->__('In 90 days'),
                'value' => '90'
            ),
            array(
                'label' => $helper->__('In 180 days'),
                'value' => '180'
            ),
            array(
                'label' => $helper->__('In 360 days'),
                'value' => '360'
            )
        );
    }
}