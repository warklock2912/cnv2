<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Blogimages extends Mage_Core_Model_Abstract
{
   
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/blogimages');
    }

}