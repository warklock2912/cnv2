<?php

    class Crystal_Twoctwop_Model_Resource_Card_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
    {
        protected function _construct()
        {
            $this->_init('twoctwop/card');
        }
    }
