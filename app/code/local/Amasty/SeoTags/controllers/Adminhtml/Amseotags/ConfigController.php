<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Adminhtml_Amseotags_ConfigController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Export Tags to csv
     */
	public function exportAction()
	{
		$fileName   = 'export_tags-' . date('Y-m-d') . '.csv';
		$data       = Mage::getModel('tag/tag')->getExportData();
		$exportInfo = Mage::helper('amseotags')->getCsvFile($data);

		$this->_prepareDownloadResponse($fileName, $exportInfo);
	}
}
