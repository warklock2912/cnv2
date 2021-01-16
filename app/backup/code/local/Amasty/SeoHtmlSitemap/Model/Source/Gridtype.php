<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Model_Source_Gridtype
{

    const TYPE_TREE = 1;
    const TYPE_LIST = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = array(
            array('value' => self::TYPE_TREE, 'label'=> 'Tree'),
            array('value' => self::TYPE_LIST, 'label'=> 'List')
        );

        return $data;
    }

}