<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Highlight {

    public function productAttribute($obj, $result, $params) {
        if (isset($params['attribute'])) {
            $helper = Mage::helper('mageworx_searchsuite');
            $sdata = Mage::helper('mageworx_searchsuite')->getSearchTransition();
            if ($sdata['query_text']) {
                $attribute = $params['attribute'];
                if (strpos($attribute, 'meta') !== 0 && strpos($attribute, 'url') !== 0 && $attribute != 'image' && $attribute != 'small_image' && $attribute != 'thumbnail') {
                    $result = $helper->highlightText($result, $sdata['query_text']);
                }
            }
        }
        return $result;
    }

}
