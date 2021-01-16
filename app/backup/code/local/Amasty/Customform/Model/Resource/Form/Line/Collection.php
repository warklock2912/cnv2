<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Model_Resource_Form_Line_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amcustomform/form_line');
        $this->setOrder('sort_order', 'ASC');
    }
}