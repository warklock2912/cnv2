<?php

/**
 *
 *
 * @category Sunarc
 * @package stockavailability-magento
 * @author Sunarc Team <info@sunarctechnologies.com>
 * @copyright Sunarc (http://sunarctechnologies.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Sunarc_StockAvailability_Model_Observer
{
    /**
     * Rewrite necessary classes
     *
     * @param $observer
     */
    public function prepareProductOnSave($observer)
    {
        $isRewriteEnabled = Mage::getStoreConfig('stockavailability/options/enable');
        $product = $observer->getProduct();
        $stockData = $product->getStockData();
        if ($isRewriteEnabled && $product && $stockData['qty'] > 0) {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getEntityId());
            // Load the stock for this product
            $stock->setData('is_in_stock', 1); // Set the Product to InStock
            $stock->save();
        }
    }

    public function refundOrderInventory($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $isRewriteEnabled = Mage::getStoreConfig('stockavailability/options/enable');
        if ($isRewriteEnabled) {
            foreach ($creditmemo->getAllItems() as $item) {
                // check if item was to be returned to stock
                $process = false;
                if ($item->hasBackToStock()) {
                    if ($item->getBackToStock() && $item->getQty()) {
                        $process = true;
                    }
                } elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
                    $process = true;
                }

                if ($process == true) {
                    // update the product id
                    $productId = $item->getProductId();
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if (!$product->isConfigurable()) {
                        $stockItem = $product->getStockItem();
                        $stockItem->setIsInStock(1);
                        $stockItem->save();
                    }
                }
            }
        }
    }
}