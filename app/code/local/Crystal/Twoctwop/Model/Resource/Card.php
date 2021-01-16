<?php

    class Crystal_Twoctwop_Model_Resource_Card extends Mage_Core_Model_Resource_Db_Abstract
    {
        protected function _construct()
        {
            $this->_init('twoctwop/card','id');
        }
    }
