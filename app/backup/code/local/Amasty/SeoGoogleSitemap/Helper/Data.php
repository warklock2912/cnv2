<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Helper_Data extends Mage_Core_Helper_Abstract
{	
    public function getYesNo()
    {
    	return array(
			0 => $this->__('No'),
            1 => $this->__('Yes'),
		); 
    }
    
	public function getFrequency()
    {
    	return array(
			'always' => $this->__('always'),
            'hourly' => $this->__('hourly'),
	    	'daily' => $this->__('daily'),
	    	'weekly' => $this->__('weekly'),
	    	'monthly' => $this->__('monthly'),
	    	'yearly' => $this->__('yearly'),
	    	'never' => $this->__('never'),
		); 
    }
    
 	public function getProductUrlSettings()
    {
    	return array(
			0 => $this->__('Hide category path'),
            1 => $this->__('Show shortest path (if many)'),
            2 => $this->__('Show longest path (if many)'),
		); 
    }
}