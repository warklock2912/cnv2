<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Crystal_BlockSlide_Block_Adminhtml_Blockslide_Renderer_Images extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$imgId = $row->getImage();
		$html = '';
		if ($imgId != '') {
			$imgPath = Mage::getBaseUrl('media') . "blockslide/images/" . $imgId;
			$html = '<img src="' . $imgPath . '" alt=" ' . $imgPath . '" height="180" width="260" />';
		}
		return $html;
	}
}