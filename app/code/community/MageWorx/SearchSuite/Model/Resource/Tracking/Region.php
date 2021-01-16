<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Tracking_Region extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/region_tracking', 'id');
    }

    protected function _checkUnique(Mage_Core_Model_Abstract $object) {
        $uses = $object->getNumUses();
        if (!$uses) {
            $object->setNumUses(1);
        }
        if ($object->getQueryId() && $object->getCountry()) {
            $select = $this->_getWriteAdapter()->select()
                            ->from($this->getMainTable())->where('query_id=?', $object->getQueryId())->where('country=?', $object->getCountry());
            $test = $this->_getWriteAdapter()->fetchRow($select);

            if ($test) {
                $object->setNumUses($uses++);
                $object->setId($test['id']);
                $object->setNumUses($test['num_uses'] + 1);
            }
        }
        return $this;
    }

}
