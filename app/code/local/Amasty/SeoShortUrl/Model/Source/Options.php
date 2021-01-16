<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoShortUrl
 */  
class Amasty_SeoShortUrl_Model_Source_Options extends Varien_Object
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amseoshorturl');
        return array(
        	array('value' => '-',   'label' => $hlp->__('-')),
        	array('value' => '_',   'label' => $hlp->__('_')),
            array('value' => '--',  'label' => $hlp->__('--')),
        );
    }
    
}