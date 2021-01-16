<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Model_Blist extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amfollowup/blist');
    }
}