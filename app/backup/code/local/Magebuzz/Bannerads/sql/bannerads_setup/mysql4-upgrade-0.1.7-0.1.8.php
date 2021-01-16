<?php
	$baseDir = dirname(__FILE__);
	$oldName = $baseDir . '/../../../../../../../media/bannerads';
	$newName = $baseDir . '/../../../../../../../media/banners';
	if (is_dir($oldName)) {
		rename($oldName, $newName);
	}