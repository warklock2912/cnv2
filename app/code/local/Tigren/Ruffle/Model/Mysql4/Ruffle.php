<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Model_Mysql4_Ruffle extends Mage_Core_Model_Mysql4_Abstract {
	protected $_ruffleProductTable;
	public function _construct() {    
		$this->_init('ruffle/ruffle', 'ruffle_id');
		$this->_ruffleProductTable = $this->getTable('ruffle/product');
	}

	protected function _afterLoad(Mage_Core_Model_Abstract $object) {
		if ($object->getId()) {
	      	$products = $this->lookupProductIds($object->getId());
	      	$object->setData('ruffle_items', $products);
	    }
	    return parent::_afterLoad($object);
	}

	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		$this->_saveRuffleItems($object);
		return parent::_afterSave($object);
	}

	protected function _saveRuffleItems($ruffle) {
		$id = $ruffle->getId(); 
		$products = $ruffle->getPostedProducts();
		if ($products === null) {
            return $this;
        }
        //delete all old 
        $oldProducts = $this->lookupProductIds($id);
        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $adapter = $this->_getWriteAdapter();

        if (!empty($delete)) {
            $cond = array(
                'product_id IN(?)' => array_keys($delete),
                'ruffle_id=?' => $id
            );
            $adapter->delete($this->_ruffleProductTable, $cond);
        }

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $productId => $qty) {
            	$qtyValue = base64_decode($qty);
				$qty = Mage::helper('core/string')->parseQueryStr($qtyValue);
                $data[] = array(
                    'ruffle_id' => (int)$id,
                    'product_id'  => (int)$productId,
                    'general_qty'    => (int)$qty['general_qty'],
                    'vip_qty'    => (int)$qty['vip_qty']
                );
            }
            $adapter->insertMultiple($this->_ruffleProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $productId => $qty) {
            	$qtyValue = base64_decode($qty);
				$qty = Mage::helper('core/string')->parseQueryStr($qtyValue);
                $where = array(
                    'ruffle_id = ?'=> (int)$id,
                    'product_id = ?' => (int)$productId
                );
                $bind  = array('general_qty' => (int)$qty['general_qty'], 'vip_qty' => (int)$qty['vip_qty']);
                $adapter->update($this->_ruffleProductTable, $bind, $where);
            }
        }
    }

    public function lookupProductIds($ruffleId) {
    	$adapter = $this->_getWriteAdapter();
    	$select = $adapter->select()
    		->from($this->getTable('ruffle/product'), array('product_id', 'general_qty', 'vip_qty'))
    		->where('ruffle_id = ?', (int)$ruffleId);
    	$result = $adapter->fetchAll($select);
    	$products = array();
    	foreach ($result as $item) {
    		$qtyString = 'general_qty='.$item['general_qty'].'&vip_qty='.$item['vip_qty'];
    		$products[$item['product_id']] = base64_encode($qtyString);
    	}
    	return $products;
  	}

    public function getRuffleByProductId(Tigren_Ruffle_Model_Ruffle $ruffle, $productId) {
        $adapter = $this->_getReadAdapter();
        $bind    = array('product_id' => $productId);
        $select  = $adapter->select()
            ->from($this->getTable('ruffle/product'), array('ruffle_id'))
            ->where('product_id = :product_id');
        $ruffleIds = $adapter->fetchAll($select, $bind);
        if ($ruffleIds) {
          foreach($ruffleIds as $_ruffleId){
            $this->load($ruffle, $_ruffleId);
            if($ruffle->getData('is_active') == '1'){
              return $this;
            }
          }
        } else {
            $ruffle->setData(array());
        }
        return $this;
    }
}