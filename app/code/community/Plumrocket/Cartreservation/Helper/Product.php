<?php

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Cart_Reservation-v1.5.x
@copyright	Copyright (c) 2013 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 
*/

class Plumrocket_Cartreservation_Helper_Product extends Plumrocket_Cartreservation_Helper_Main
{
    const XML_PATH_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';
    protected $_cacheByProducts = array();

    public function getReservedItems($pid, $limit = 0)
    {
        if (array_key_exists($pid, $this->_cacheByProducts)
            && array_key_exists($limit, $this->_cacheByProducts[$pid])
        ) {
            return $this->_cacheByProducts[$pid][$limit];
        }

        $result = array();

        if ($limit > 0) {
            $_items = Mage::getModel('cartreservation/item')->getCollection()
                ->addFieldToFilter(
                    array('product_id', 'child_product_id'),
                    array(
                        array('eq' => $pid),
                        //array('eq' => $pid),
                        array('finset' => $pid),
                    )
                );
            $_items->getSelect()
                ->order('main_table.item_id ASC');
                // DO NOT LIMIT COLLECTION!!!
        
            $cnt = 0;
            foreach ($_items as $item) {
                if ($item->isReserved()) {
                    // if last avail reserved item will overflow limit then
                    // set qty as limit minus counter without current item
                    if (($cnt + $item->getQty()) > $limit) {
                        $item->setQty($limit - $cnt);
                    }

                    $cnt += $item->getQty();

                    $result[] = $item;

                    if ($cnt >= $limit) {
                        break;
                    }
                }
            }
        }

        $this->_cacheByProducts[$pid][$limit] = $result;
        return $result;
    }

    public function getReservedCount($pid, $limit = 0)
    {
        $count = 0;
        $items = $this->getReservedItems($pid, $limit);
        
        foreach ($items as $item) {
            $count += (int)$item->getQty();
        }

        return $count;
    }

    /*
	 * Clear items cache for product id
	 */
    public function clearCache($pid)
    {
        if (array_key_exists($pid, $this->_cacheByProducts)) {
            unset($this->_cacheByProducts[$pid]);
        }
    }

    public function leftReservationTime($pid)
    {
        //$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pid);
        $items = $this->getReservedItems($pid, 9999999);

        $times = array();
        foreach ($items as $item) {
            $times[] = (int)$item->leftReservationTime();
        }

        return empty($times)? 0: min($times);
    }

    public function init($product)
    {
        // Configurable product may have reserved all children products,
        // then Magento mark it as Sold Out - because all children is sold out.
        // Thus we must check reserved whenever sold out it or not.
        switch ($product->getTypeId()) {
            case 'configurable':
                $associated_products = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $this->_processProduct($product, $associated_products);
                break;

            case 'bundle':
                $typeInstance = $product->getTypeInstance(true);
                $typeInstance->setStoreFilter($product->getStoreId(), $product);

                $optionCollection = $typeInstance->getOptionsCollection($product);

                $selectionCollection = $typeInstance->getSelectionsCollection(
                    $typeInstance->getOptionsIds($product), 
                    $product
                );

                $options = $optionCollection->appendSelections(
                    $selectionCollection, false,
                    Mage::helper('catalog/product')->getSkipSaleableCheck()
                );

                $isSalable = false;
                $isReserved = false;
                foreach ($options as $option) {
                    $this->_processProduct($product, $option->getSelections());

                    $isSalable = $isSalable || $product->isSalable();
                    $isReserved = $isReserved || $product->getIsReserved();

                    if ($option->getRequired() && !$product->isSalable()) {
                        $isSalable = false;
                        break;
                    }
                }

                $product->setData('is_salable', $isSalable);
                $product->setData('is_reserved', $isReserved);
                // unset options data because core will try to append the same data later
                $product->unsetData('_cache_instance_options_collection');
                break;

            case 'grouped':
                $childIds = array_values(
                    Mage::getModel('catalog/product_type_grouped')
                        ->getChildrenIds($product->getId())
                );
                $childIds = array_shift($childIds);

                if ($childIds) {
                    $associated_products = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addFieldToFilter('entity_id', array('in' => array($childIds)));

                    $this->_processProduct($product, $associated_products);
                }
                break;

            default:
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if ($product->isSalable() && $this->getManageStock($product, $stock)) {
                    switch ($stock->getBackorders()) {
                        case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                        case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                            $inStock = true;
                            break;
                        default:
                            $inStock = (int)$stock->getQty() > 0;
                    }

                    $product->setData('is_salable', $inStock);
                    $product->getStockItem()->setData('is_in_stock', $inStock);
                }

                $product->setData('is_reserved', $stock->isReserved() || ((int)$product->getData('reserved_after_order') === 1));
        }
    }

    protected function _processProduct($product, $children)
    {
        $isSalable = false;
        $isReserved = false;
        if ($children) {
            foreach ($children as $child) {
                $isSalable = $child->isSalable();
                $isReserved = $isReserved || $child->getIsReserved();
                if ($isSalable) { break; 
                }
            }
        }

        $product->setData('is_salable', $isSalable);
        $product->setData('is_reserved', $isReserved || ((int)$product->getData('reserved_after_order') === 1));
    }

    public function initAdmin($product)
    {
        Mage::helper('cartreservation')->startAdminMode();
        $this->init($product);
        Mage::helper('cartreservation')->stopAdminMode();
    }

    public function getManageStock($product, $stock = null)
    {
        if (is_null($stock)) {
            $stock = $product->getStockItem();
        }

        if ($stock->getUseConfigManageStock()) {
            return (int) Mage::getStoreConfigFlag(self::XML_PATH_MANAGE_STOCK);
        }

        return $stock->getData('manage_stock');
    }


    public function cleanCache($ids)
    {
        if (Mage::getConfig()->getModuleConfig('Enterprise_PageCache')) {
            if (!is_array($ids)) {
                $ids = array($ids);
            }

            $entity = Mage::getModel('catalog/product');
            $cacheTags = array();
            foreach ($ids as $entityId) {
                $entity->setId($entityId);
                $cacheTags = array_merge($cacheTags, $entity->getCacheIdTags());
            }

            if (!empty($cacheTags)) {
                Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($cacheTags);
            }
        }
    }
}