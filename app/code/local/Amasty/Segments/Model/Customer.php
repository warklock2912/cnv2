<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Customer extends Mage_Core_Model_Abstract
    {
        public function _construct()
        {    
            $this->_init('amsegments/customer');
        }
    }
?>