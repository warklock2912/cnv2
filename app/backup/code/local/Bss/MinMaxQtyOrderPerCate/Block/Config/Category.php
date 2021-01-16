<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_MinMaxQtyOrderPerCate
* @author     Extension Team
* @copyright  Copyright (c) 2014-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/

class Bss_MinMaxQtyOrderPerCate_Block_Config_Category extends Mage_Core_Block_Html_Select
{

    public function toOptionArray($bssAddEmpty = true)
    {
 
        $oCategoryCollection = Mage::getResourceModel('catalog/category_collection');
 
        $oCategoryCollection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('parent_id')
           // ->setStoreId($iStoreId)
            ->addFieldToFilter('parent_id', array('gt' => 0))
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', array('gteq' => 1))
            ->addAttributeToSort('path', 'asc');
        $aOptions = array();
 
        if ($bssAddEmpty) {
            $aOptions[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        foreach ($oCategoryCollection as $oCategory) {
            $categoryName = htmlspecialchars($oCategory->getName(), ENT_QUOTES);
            $sLabel = $categoryName."(ID: ".$oCategory->getId().")";
            $iPadWidth = ($oCategory->getLevel() - 1) * 2 + strlen($sLabel);
            $sLabel = str_pad($sLabel, $iPadWidth, '---', STR_PAD_LEFT);
 
            $aOptions[] = array(
                'label' => $sLabel,
                'value' => $oCategory->getId()
            );
        }
 
        return $aOptions;
    }

    public function _toHtml()
    {
        $options =  $this->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
