<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */


class Amasty_SeoSingleUrl_Model_Source_Breadcrumb
{
    const BREADCRUMB_URL = 0;
    const BREADCRUMB_LAST_VISITED = 1;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amseourl');
        $vals = array(
            self::BREADCRUMB_URL            => $hlp->__('Current URL'),
            self::BREADCRUMB_LAST_VISITED   => $hlp->__('Last Visited Category'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                'value' => $k,
                'label' => $v
            );

        return $options;
    }
}