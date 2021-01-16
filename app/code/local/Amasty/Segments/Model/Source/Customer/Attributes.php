<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Model_Source_Customer_Attributes extends Varien_Object
{
    public function toOptionArray($vl = true)
    {
        $productAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();
 
        $attributes = array();

        foreach ($productAttributes as $attribute) {
            $label = $attribute->getFrontendLabel();
            if (!$label) {
                continue;
            }
            // skip "binary" attributes
            if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                continue;
            }
            // skip "binary" attributes
            if (in_array($attribute->getAttributeCode(), array('default_billing', 'default_shipping'))) {
                continue;
            }
            
            if ($vl) {
                $attributes[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $label
                );
            } else {
                $attributes[$attribute->getAttributeCode()] = $label;
            }
        }
        
        return $attributes;
    }
}
?>