<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Block_Review extends Mage_Review_Block_Helper
{
    public function getReviewsUrl($hashType = '', $product = false)
    {
        $url = '';

        if ($product) {
            try {
                // this can cause fatal errors if product url will be empty somehow
                $rewrite = Mage::getModel('core/url_rewrite')->loadByRequestPath(
                    $product->getProductUrl()
                );
                $url     = $rewrite->getTargetPath();
            } catch (Exception $e) {
                $url = $product->getProductUrl();
            }
            $url = $url ? $url : $product->getProductUrl();
        }

        /** @var Amasty_SeoReviews_Helper_Data $helper */
        $helper = Mage::helper('amseoreviews');
        $hash   = $helper->getHashByType($hashType);

        $url .= $hash;

        return $url;
    }

	protected function _construct()
	{
		if (! $this instanceof Amasty_SeoRichData_Block_Review) {
			$this->_availableTemplates = array(
				'default' => 'amasty/amseoreviews/review/helper/summary.phtml',
				'short'   => 'amasty/amseoreviews/review/helper/summary_short.phtml'
			);
		}
	}
}