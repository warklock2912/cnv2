<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isTagRewritingEnabled()
	{
		return Mage::getStoreConfig('amseotags/tags/enable');
	}

    /**
     * get Header for csv file
     * @param $data
     * @return array|mixed
     */
	protected function _getExportHeaders($data)
	{
		$titles = array(
			'product_name'  => $this->__('product_name (for reference only)')
		);

		$item = current($data);
		if (! empty($item)) {
			$item = array_keys($item);
			foreach ($item as &$val) {
				if (array_key_exists($val, $titles)) {
					$val = $titles[$val];
				}
			}

			return $item;
		} else {
			return array();
		}
	}

    /**
     * Export Seo Tags in csv
     * @param $data
     * @return array fileInfo for csv Export
     * @throws Exception
     */
	public function getCsvFile($data)
	{
		$io = new Varien_Io_File();

		$path = Mage::getBaseDir('var') . DS . 'export' . DS;
		$name = md5(microtime());
		$file = $path . $name . '.csv';

		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));
		$io->streamOpen($file, 'w+');
		$io->streamLock(true);
		$io->streamWriteCsv($this->_getExportHeaders($data));

		foreach ($data as $item) {
			$io->streamWriteCsv($item);
		}

		$io->streamUnlock();
		$io->streamClose();

		return array(
			'type'  => 'filename',
			'value' => $file,
			'rm'    => true // can delete file after use
		);
	}
}