<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */ 
class Amasty_Segments_Model_Mysql4_Cart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amsegments/cart');
    }
}
?>