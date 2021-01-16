<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Model_Observer extends Varien_Object {

    public function changeWidgetTemplate(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Edit_Tab_Form) {
            // consider getting the template name from configuration
            $template = 'imagehome/imagehome.phtml';
            $block->setTemplate($template);
        }
    }

}
