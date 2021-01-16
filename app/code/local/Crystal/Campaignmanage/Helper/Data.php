<?php

class Crystal_Campaignmanage_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function renameImage($image_name)
	{
		$string = str_replace("  ", " ", $image_name);
		$new_image_name = str_replace(" ", "-", $string);
		$new_image_name = strtolower($new_image_name);
		return $new_image_name;
	}

	public function convertOptionsToString($productId)
	{
		$sizes = null;

		$_product = Mage::getModel('catalog/product')->load($productId);
		if ($_product->isConfigurable()) {
			$allProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);
			$i = 1;
			foreach ($allProducts as $subproduct) {

				if ($subproduct->getIsInStock() == 1)
					$qty = (int)$subproduct->getStockItem()->getQty();

				if ($qty) {
					if ($i == 1) {
						$sizes = $subproduct->getData('size_products') . ',' . $qty;
						$i++;
					} else {
						$sizes .= ';' . $subproduct->getData('size_products') . ',' . $qty;
					}
				}
			}
		}
		return $sizes;
	}

	public function getOptions($productId, $campaignId)
	{
		$options = array();
		$optionArr = array();
		$product = Mage::getModel('campaignmanage/products')->getCollection()
			->addFieldToFilter('campaign_id', $campaignId)
			->addFieldToFilter('product_id', $productId)
			->getFirstItem();
		$optionArr = explode(";", $product->getOption());
		foreach ($optionArr as $option) {
			$option = explode(",", $option);
			$options[] = $option;
		}
		return $options;
	}

	public function getOptionValues($productId, $campaignId)
	{
		$options = array();
		$optionArr = array();
		$product = Mage::getModel('campaignmanage/products')->getCollection()
			->addFieldToFilter('campaign_id', $campaignId)
			->addFieldToFilter('product_id', $productId)
			->getFirstItem();
		$optionArr = explode(";", $product->getOption());
		foreach ($optionArr as $option) {
			$option = explode(",", $option);
			$options[] = $option[0];
		}
		return $options;
	}

	public function setListWinnerByOption($optionValue, $qty, $productId, $campaignId)
	{
		$collection = Mage::getModel('campaignmanage/raffle')->getCollection()
			->addFieldToFilter('campaign_id', $campaignId)
			->addFieldToFilter('product_id', $productId)
			->addFieldToFilter('option', $optionValue);
		$collection->getSelect()->order(new Zend_Db_Expr('RAND()'))
			->limit($qty);
		foreach ($collection as $item) {
			$item->setIsWinner(true)->save();
		}
		return true;
	}

	public function setListWinnerByProduct($productId, $qty, $campaignId)
	{
		$collection = Mage::getModel('campaignmanage/raffle')->getCollection()
			->addFieldToFilter('campaign_id', $campaignId)
			->addFieldToFilter('product_id', $productId);
		$collection->getSelect()->order(new Zend_Db_Expr('RAND()'))
			->limit($qty);
		foreach ($collection as $item) {
			$item->setIsWinner(true)->save();
		}
		return true;
	}

	public function getOptionLabel($optionValue)
	{
		$product = Mage::getModel('catalog/product')
			->setStoreId(0)
			->setData('size_products', $optionValue);
		$option_label = $product->getAttributeText('size_products');
		return $option_label;
	}
}
