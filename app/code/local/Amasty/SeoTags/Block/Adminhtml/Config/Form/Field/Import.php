<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */


class Amasty_SeoTags_Block_Adminhtml_Config_Form_Field_Import extends Varien_Data_Form_Element_Abstract
{

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }
}
