<?php

class Crystal_BlockSlide_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function renameImage($image_name)
	{
		$string = str_replace("  ", " ", $image_name);
		$new_image_name = str_replace(" ", "-", $string);
		$new_image_name = strtolower($new_image_name);
		return $new_image_name;
	}
}