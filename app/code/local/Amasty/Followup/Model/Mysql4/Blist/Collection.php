<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */ 
class Amasty_Followup_Model_Mysql4_Blist_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfollowup/blist');
    }
}