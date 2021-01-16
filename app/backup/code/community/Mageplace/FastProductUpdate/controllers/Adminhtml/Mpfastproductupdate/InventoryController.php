<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */
class Mageplace_FastProductUpdate_Adminhtml_Mpfastproductupdate_InventoryController extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        if (empty($_FILES[Mageplace_FastProductUpdate_Helper_Const::FILE_FIELD_NAME])
            || empty($_FILES[Mageplace_FastProductUpdate_Helper_Const::FILE_FIELD_NAME]['tmp_name'])
            || empty($_FILES[Mageplace_FastProductUpdate_Helper_Const::FILE_FIELD_NAME]['name'])
        ) {
            $this->_getSession()->addError($this->__('Error during uploading CSV file'));
            return $this->_redirectProductAdminhtml();
        }

        $file = $_FILES[Mageplace_FastProductUpdate_Helper_Const::FILE_FIELD_NAME];

        if (@substr(strrchr($file['name'], '.'), 1) != 'csv') {
            $this->_getSession()->addError($this->__('Input file must be in CSV format'));
            return $this->_redirectProductAdminhtml();
        }

        $csvFileName = Mage::getBaseDir('tmp') . DS . uniqid(str_replace('.csv', '', $file['name'])) . '.csv';
        if (!move_uploaded_file($file['tmp_name'], $csvFileName)) {
            $this->_getSession()->addError($this->__('Error during moving CSV file'));
            return $this->_redirectProductAdminhtml();
        }

        $startTime = microtime(true);

        $numProducts = 0;
        $delimiter   = Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_DELIMITER);
        if (($handle = fopen($csvFileName, "r")) !== false) {
            $row     = 0;
            $inserts = array();

            if (Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_DIRECT_DB_IMPORT)) {
                $resource   = Mage::getSingleton('core/resource');
                $write      = $resource->getConnection('write');
                $stockTable = $resource->getTableName('cataloginventory_stock_item');
            }


            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $row++;
                if (count($data) < 2) {
                    if (!is_array($data) || !is_null($data[0])) {
                        $this->_getSession()->addWarning($this->__('Skip row %s', $row));
                    }

                    continue;
                }

                list($sku, $qty) = $data;

                if (is_null($qty) || $qty === '') {
                    $this->_getSession()->addWarning($this->__('Skip row %s', $row));
                    continue;
                }

                try {
                    $firstCol = Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_FIRST_COLUMN);
                    if (!$firstCol) {
                        $productId = $this->_getProductModel()->getIdBySku($sku);
                        if (!$productId) {
                            $this->_getSession()->addWarning($this->__("Product SKU '%s' not founded", $sku));
                            continue;
                        }
                    } else {
                        $productId = (int)$sku;
                    }

                    $stock = $this->_getStockModel()->loadByProduct($productId);
                    if (!is_object($stock) || !$stock->getId()) {
                        $this->_getSession()->addWarning($this->__("Product ID#%s stock not loaded", $productId));
                        continue;
                    }

                    if (!$stock->getManageStock() || !Mage::helper('catalogInventory')->isQty($stock->getTypeId())) {
                        $this->_getSession()->addWarning($this->__("Product ID#%s stock not updated", $productId));
                        continue;
                    }

                    if (substr($qty, 0, 1) == '-' || substr($qty, 0, 1) == '+') {
                        $stockQty = $stock->getQty();
                        $qty      = floatval($qty);
                        $qty      = $stockQty + $qty;
                    } else {
                        $qty = floatval($qty);
                    }

                    $stock->setQty($qty)->setIsInStock(1)->save();

                    $numProducts++;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Skip row %s (%s)', $row, $e->getMessage()));
                }
            }
            fclose($handle);

            if (Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_DIRECT_DB_IMPORT)) {
                if (Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_ENABLE_REINDEX)) {
                    $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection();
                    foreach ($indexingProcesses as $process) {
                        try {
                            $process->reindexEverything();
                        } catch (Exception $e) {
                            $this->_getSession()->addWarning(
                                $this->__('There was a problem with reindexing process: %s. Indexer code: %s.', $e->getMessage(), $process->getData('indexer_code'))
                            );
                            Mage::logException($e);
                        }
                    }
                } else {
                    $this->_getSession()->addWarning('Please don\'t forget to reindex data');
                }

            }
        } else {
            $this->_getSession()->addError($this->__('Error during opening CSV file'));
            return $this->_redirectProductAdminhtml();
        }

        $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("%s product(s) changed.", $numProducts));

        return $this->_redirectProductAdminhtml();
    }

    public function exportAction()
    {
        $productCollection = $this->_getProductModel()->getCollection();

        /*$config = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_SUBTRACT);*/

        $csvRows = array();
        /* @var $product Mage_Catalog_Model_Product */
        foreach ($productCollection as $product) {
            $row = array();
            if (!Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_FIRST_COLUMN)) {
                $row[] = $product->getSku();
            } else {
                $row[] = $product->getId();
            }

            $stock = $this->_getStockModel()->loadByProduct($product);

            if ($stock->getId() && $stock->getManageStock() /*&& $config*/ && Mage::helper('catalogInventory')->isQty($stock->getTypeId())) {
                $qty = $stock->getQty();
                if (!is_numeric($qty)) {
                    $qty = Mage::app()->getLocale()->getNumber($qty);
                }

                if (!$stock->getIsQtyDecimal()) {
                    $qty = intval($qty);
                }

                $row[]     = $qty;
                $csvRows[] = $row;
            }
        }

        if (empty($csvRows)) {
            $this->_getSession()->addError($this->__('Error during export data to CSV file'));
            return $this->_redirectProductAdminhtml();
        }

        $delimiter = Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_DELIMITER);

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=export.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");
        foreach ($csvRows as $row) {
            fputcsv($output, $row, $delimiter);
        }
        fclose($output);

        exit;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProductModel()
    {
        return Mage::getModel('catalog/product');
    }

    /**
     * @return Mageplace_FastProductUpdate_Model_Stock
     */
    protected function _getStockModel()
    {
        return Mage::getModel('mpfastproductupdate/stock');
    }

    protected function _redirectProductAdminhtml()
    {
        return $this->_redirect('adminhtml/catalog_product');
    }

    /**
     * Check access (in the ACL) for current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Mageplace_FastProductUpdate_Helper_Const::ACL_INVENTORY_PATH);
    }
}