<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Stopword extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('mageworx_searchsuite/stopwords', 'id');
    }

    public function loadByWord($object, $word) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('word=?', $word)
                ->where('store_id=?', $object->getStoreId())
                ->limit(1);
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }

    public function import($object, $storeId, array $words) {
        $data = array();
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word)) {
                $data[] = array('store_id' => $storeId, 'word' => trim($word));
            }
        }
        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data, array('word'));
        return $this;
    }

}
