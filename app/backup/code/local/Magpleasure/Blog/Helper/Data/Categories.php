<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Data_Categories
{
    protected $_categories = array();

    public function getCategoryName($categoryId)
    {
        if (!(isset($this->_categories[$categoryId]) && $this->_categories[$categoryId])){
            $this->_categories[$categoryId] = Mage::getModel('mpblog/category')->load($categoryId);
        }
        return $this->_categories[$categoryId]->getName();
    }

    public function renameImage($image_name) {
        $string = str_replace("  ", " ", $image_name);
        $new_image_name = str_replace(" ", "-", $string);
        $new_image_name = strtolower($new_image_name);
        return $new_image_name;
    }
}