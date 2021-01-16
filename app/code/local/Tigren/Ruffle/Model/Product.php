<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Model_Product extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('ruffle/product');
	}

	public function getWinnerQuotaByProductId($productId, $ruffleId) {
		$this->_getResource()->getWinnerQuotaByProductId($this, $productId, $ruffleId);
        return $this;
	}

  public function getOptionConfigurableProduct($productId, $superAttribute){
    $product = Mage::getModel('catalog/product')->load($productId);
    $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($superAttribute, $product);

    return $childProduct;
  }

    public function getChildProduct($parentProductId,$optionData)
    {
        $product = Mage::getModel('catalog/product')->load($parentProductId);
        if($optionData){
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);
        }else{
            $childProduct = $product;
            if(!$childProduct){
                return 0;
            }
        }
        return $childProduct;
    }
}