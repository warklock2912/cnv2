<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Resource_Task extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amoptimization/task', 'id');
    }

    public function scheduleTask($path, $code)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getMainTable(), array(
                'path' => $path,
                'minificator_code' => $code
            )
        );
    }
}
