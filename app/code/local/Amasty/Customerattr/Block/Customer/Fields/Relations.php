<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Customer_Fields_Relations
    extends Mage_Core_Block_Template
{
    protected $_hasRequired = false;
    protected $_hasValidation = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amcustomerattr/relations.phtml');
    }

    public function setParts($column)
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();

        $filters = array(
            "is_user_defined = 1"
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection, 'eav_attribute',$filters
        );

        $filters = array();
        if ($column) {
            $filters[] = $column . " = 1";
        }
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection, 'customer_eav_attribute',$filters
        );

        if (0 < $collection->getSize()) {
            foreach ($collection as $attribute) {
                if ($attribute->getFrontend()->getClass()) {
                    $this->_hasValidation = true;
                }
                if ($attribute->getIsRequired()
                    || $attribute->getRequiredOnFront()
                ) {
                    $this->_hasRequired = true;
                }
            }
        }

        return $this;
    }

    public function hasRequired()
    {
        return $this->_hasRequired;
    }

    public function hasValidation()
    {
        return $this->_hasValidation;
    }
}