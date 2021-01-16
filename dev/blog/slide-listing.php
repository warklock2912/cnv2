<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$slideCollection = Mage::getModel('blockslide/blockslide')->getCollection()
	->addFieldToFilter('status', true)
	->setOrder('position', 'ASC');
$dataResponse = array();
foreach ($slideCollection as $slide):
	$dataResponse[] = Mage::getBaseUrl('media')  . 'blockslide' . DS . 'images' . DS . $slide->getImage();
endforeach;
dataResponse(200, 'valid', $dataResponse);