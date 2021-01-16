<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */



class Amasty_Customerattr_Model_Rewrite_Eav_Entity_Attribute_Frontend_Default
    extends Mage_Eav_Model_Entity_Attribute_Frontend_Default
{

    public function getInputRendererClass()
    {
        $attribute = $this->getAttribute();
        $renderer = "Amasty_Customerattr_Block_Adminhtml_Data_Form_Element_"
            . ((isset($inputTypes[$attribute->getData('frontend_input')])) ?
                ucfirst($inputTypes[$attribute->getData('frontend_input')])
                : ucfirst($attribute->getData('frontend_input')));
        if (class_exists($renderer)) {
            $attribute->setData('frontend_input_renderer', $renderer);
        }

        return parent::getInputRendererClass();
    }
}
