<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_Mysql4_Details_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function getByRelation($relationId)
    {
        $this->getSelect()
            ->where('relation_id = ?', $relationId);
        return $this;
    }

    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('amcustomerattr/details', 'id');
    }


}
