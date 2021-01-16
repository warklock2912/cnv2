<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */  
class Amasty_SeoGoogleSitemap_Model_Source_Product extends Varien_Object
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amsitemap');
        return array(
        	array('value' => 0, 'label' => $hlp->__('Hide category path')),
            array('value' => 1, 'label' => $hlp->__('Show shortest path (if many)')),
            array('value' => 2, 'label' => $hlp->__('Show longest path (if many)')),
        );
    }
    
}