<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Model_System_Config_Tag_Import extends Mage_Core_Model_Config_Data
{
	/** @var Magento_Db_Adapter_Pdo_Mysql $_writeAdapter */
	protected $_writeAdapter;

	public function __construct()
	{
		parent::__construct();
		$this->_writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_write');
	}

	public function _afterSave()
	{
		$this->_uploadFile();
	}

	protected function _getHeaderKeys($headers)
	{
		$requiredValues = array('customerID', 'store', 'sku', 'is_active', 'product_tags');
		$diff           = array_diff($requiredValues, $headers);

		if (empty($diff)) {
			$headersKeys = array();
			foreach ($requiredValues as $item) {
				$headersKeys[$item] = array_search($item, $headers);
			}

			return array_values($headersKeys);
		}

		Mage::throwException(Mage::helper('amseotags')
			->__('Following headers are missing in imported file: %s', implode(', ', $diff)));

		return false;
	}

	/**
	 * Main method
	 *
	 * @return $this
	 */
	protected function _uploadFile()
	{
		if (empty($_FILES['groups']['tmp_name']['tags']['fields']['import']['value'])) {
			return $this;
		}

		$csvFile = $_FILES['groups']['tmp_name']['tags']['fields']['import']['value'];

		$io   = new Varien_Io_File();
		$info = pathinfo($csvFile);
		$io->open(array('path' => $info['dirname']));
		$io->streamOpen($info['basename'], 'r');

		// check and skip headers
		$headers = $io->streamReadCsv();
		if ($headers === false) {
			$io->streamClose();
			Mage::throwException(Mage::helper('amseotags')->__('Invalid Tags File Format'));
		}

		list($customerID, $store, $sku, $isActive, $productTags) = $this->_getHeaderKeys($headers);

		$this->_writeAdapter->beginTransaction();

		/** @var Amasty_SeoTags_Model_Tag_Tag $tagModel */
		$tagModel      = Mage::getModel('tag/tag');
		$statusActive  = $tagModel->getApprovedStatus();
		$statusPending = $tagModel->getPendingStatus();

		try {
			while (false !== ($csvLine = $io->streamReadCsv())) {
				$tagNames = explode(',', $csvLine[$productTags]);

				$colStatus = $csvLine[$isActive] == 1 ? $statusActive : $statusPending;

				$colCustomerId = (int) $csvLine[$customerID];
				$colStoreId    = (int) $csvLine[$store];
				$colSku        = trim($csvLine[$sku]);

				$productId = Mage::getModel('catalog/product')
					->getIdBySku($colSku);

				if (! $productId || ! $colCustomerId) {
					continue;
				}

				foreach ($tagNames as $itemTag) {
					$tagName = trim($itemTag);
                    if($tagName == '') {
                        continue;
                    }
					// unset previously added tag data
					$tagModel->unsetData()
						->loadByName($tagName);

					if (! $tagModel->getId()) {
						$tagModel->setName($tagName)
							->setFirstCustomerId($colCustomerId)
							->setFirstStoreId($colStoreId)
							->setStatus($colStatus)
							->save();
					}

					$tagModel->saveRelationRewritten($productId, $colCustomerId, $colStoreId, $colStatus);
				}

				if (empty($csvLine)) {
					continue;
				}
			}

			$io->streamClose();
		} catch (Exception $e) {
			$this->_writeAdapter->rollback();
			$io->streamClose();
			Mage::logException($e);
			Mage::throwException(Mage::helper('shipping')->__('An error occurred while import.'));
		}

		$this->_writeAdapter->commit();
	}
}
