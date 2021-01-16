<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Model_Form_Source
{
    public function toOptionArray()
    {
        $result = array();
        $collection = Mage::getModel('amcustomform/form')->getCollection();
        $collection->load();
        foreach ($collection as $form) {
            $result[] = array(
                'value' => $form->getId(),
                'label' => $form->getCode(),
            );
        }
        return $result;
    }
}