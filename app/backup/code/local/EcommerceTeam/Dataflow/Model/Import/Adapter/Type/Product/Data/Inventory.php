<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Inventory
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  array  */
    protected $_defaultInventoryData = array();
    /** @var string */
    protected $_stockItemTable;
    /** @var string */
    protected $_stockStatusTable;

    /**
     * Initialization
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_stockItemTable       = $this->_config->getResource()->getTableName('cataloginventory/stock_item');
        $this->_stockStatusTable     = $this->_config->getResource()->getTableName('cataloginventory/stock_status');

        $this->_defaultInventoryData = array(
            "qty"                              => 0,
            "is_in_stock"                      => 0,
            "stock_id"                         => Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID,
            "use_config_min_qty"               => 1,
            "use_config_backorders"            => 1,
            "use_config_manage_stock"          => 1,
            "backorders"                       => 0,
            "manage_stock"                     => 0,
            "min_sale_qty"                     => 0,
            "max_sale_qty"                     => 0,
            "use_config_min_sale_qty"          => 1,
            "use_config_enable_qty_inc"        => 1,
            "enable_qty_increments"            => 0,
            "use_config_qty_increments"        => 1,
            "qty_increments"                   => 0,
            "notify_stock_qty"                 => null,
            "use_config_notify_stock_qty"      => 1,
            "is_qty_decimal"                   => 0,
            "use_config_max_sale_qty"          => 1,
        );

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        $productId = $data['product_id'];
        if ($data['_is_new']) {
            $inventoryData = $this->_helper->arrayIntersectKeys($data, $this->_defaultInventoryData);
            $inventoryData = array_merge($this->_defaultInventoryData, $inventoryData);
        } else {
            $inventoryData = $this->_helper->arrayIntersectKeys($data, $this->_defaultInventoryData);
        }
        $inventoryData['product_id'] = $productId;
        if (!isset($inventoryData['stock_id'])) {
            $inventoryData['stock_id']   = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
        }

        $this->_writeConnection->insertOnDuplicate($this->_stockItemTable, $inventoryData);

        foreach ($data['website_id'] as $id) {
            $insertData = array (
                'product_id' => $productId,
                'website_id' => $id,
            );

            if ($data['_is_new']) {
                $insertData['stock_status'] = $inventoryData['is_in_stock'] ? Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK : Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK;
            }

            if(isset($inventoryData['stock_id'])) {
                $insertData['stock_id'] = $inventoryData['stock_id'];
            }
            if(isset($inventoryData['qty'])) {
                $insertData['qty'] = $inventoryData['qty'];
            }

            $this->_writeConnection->insertOnDuplicate($this->_stockStatusTable, $insertData);
        }

        return $this;
    }
}