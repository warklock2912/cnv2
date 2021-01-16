<?php
class Tigren_Ruffle_Model_Mysql4_Product extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		$this->_init('ruffle/product', 'rp_id');
	}

	public function getWinnerQuotaByProductId(Tigren_Ruffle_Model_Product $ruffleProduct, $productId, $ruffleId) {
        $adapter = $this->_getReadAdapter();
        $bind    = array('product_id' => $productId, 'ruffle_id' => $ruffleId);
        $select  = $adapter->select()
            ->from($this->getTable('ruffle/product'), array('rp_id'))
            ->where('product_id = :product_id')
            ->where('ruffle_id = :ruffle_id');
        $rpId = $adapter->fetchOne($select, $bind);
        if ($rpId) {
            $this->load($ruffleProduct, $rpId);
        } else {
            $ruffleProduct->setData(array());
        }
        return $this;
    }
}