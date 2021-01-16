<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Model_Source_Numberrange
{

	protected $_rangeMin = 1;
	protected $_rangeMax = 5;

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		$data = array();

		for ($i = $this->_rangeMin; $i <= $this->_rangeMax; $i ++) {
			$data[] = array('value' => $i, 'label'=> $i);
		}

		return $data;
	}

}