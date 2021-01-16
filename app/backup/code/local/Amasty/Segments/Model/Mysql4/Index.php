<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Index extends Amasty_Segments_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amsegments/index', 'entity_id');
    }
}