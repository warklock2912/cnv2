<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Helper_Indexer extends Bubble_DynamicCategory_Helper_Data
{
    /**
     * @var resource
     */
    protected $_lockFile;

    /**
     * @var bool
     */
    protected $_isLocked;

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return array
     * @throws Mage_Core_Exception
     */
    public function process(Mage_Catalog_Model_Category $category)
    {
        if ($this->_isLocked($category)) {
            Mage::throwException($this->__('The grid generation is being processed. Please try again later.'));
        }

        $this->_lock($category);

        $process = Mage::getSingleton('index/process')->load('catalog_url', 'indexer_code');
        if ($process->getId()) {
            $process->lock();
        }

        $collection = $this->getDynamicProductCollection($category);
        $productIds = $collection->getAllIds();

        $oldProducts = $category->getProductsPosition();
        $products = array_fill_keys($productIds, '');
        $common = array_intersect_key($oldProducts, $products);
        $products = $common + $products;
        $category->setPostedProducts($products)
            ->setDynamicProductsRefresh(0)
            ->save();

        if ($process->getId()) {
            $process->setStatus(Mage_Index_Model_Process::STATUS_PENDING);
            $process->save();
            $this->cleanNewProcessEvents($process->getId());
            $process->unlock();
        }

        $this->_unlock($category);

        if ($this->isLoggingEnabled()) {
            Mage::log(sprintf(
                '[Dynamic Category] Category %d successfully reindexed (%d): %s',
                $category->getId(),
                count($productIds),
                implode(', ', $productIds)
            ));
        }

        return $productIds;
    }

    /**
     * Remove pending process to avoid recurring indexation required message
     *
     * @param   int $processId  Process Id
     * @return  int             The number of affected rows
     */
    public function cleanNewProcessEvents($processId)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('write');

        return $adapter->delete($resource->getTableName('index/process_event'), array(
            'process_id = ?' => (int) $processId,
            'status = ?' => Mage_Index_Model_Process::EVENT_STATUS_NEW,
        ));
    }

    /**
     * Get lock file resource
     *
     * @param Varien_Object $category
     * @return resource
     */
    protected function _getLockFile(Varien_Object $category)
    {
        if ($this->_lockFile === null) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $file = $varDir . DS . 'dynamic_category_' . $category->getId() . '.lock';
            if (is_file($file)) {
                $this->_lockFile = fopen($file, 'w');
            } else {
                $this->_lockFile = fopen($file, 'x');
            }
            fwrite($this->_lockFile, date('r'));
        }

        return $this->_lockFile;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process runing and fast lock validation.
     *
     * @param Varien_Object $category
     * @return $this
     */
    protected function _lock(Varien_Object $category)
    {
        $this->_isLocked = true;
        flock($this->_getLockFile($category), LOCK_EX | LOCK_NB);

        return $this;
    }

    /**
     * Unlock process
     *
     * @param Varien_Object $category
     * @return $this
     */
    protected function _unlock(Varien_Object $category)
    {
        $this->_isLocked = false;
        flock($this->_getLockFile($category), LOCK_UN);

        return $this;
    }

    /**
     * Check if process is locked
     *
     * @param Varien_Object $category
     * @return bool
     */
    protected function _isLocked(Varien_Object $category)
    {
        if ($this->_isLocked !== null) {
            return $this->_isLocked;
        }

        $fp = $this->_getLockFile($category);
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);
            return false;
        }

        return true;
    }

    /**
     * Close file resource if it was opened
     */
    public function __destruct()
    {
        if ($this->_lockFile) {
            fclose($this->_lockFile);
        }
    }
}