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

class Bss_MinMaxQtyOrderPerCate_Model_System_OptionMinMax extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{       
    public function save()
    {
        $option = $this->getValue();
        $error = false;
      //CAR-72 : fix cant save config, cant delete all option
        $bssgr = array();
        foreach ($option as $k => $v) {
          if(isset($v['customer_group_id']) && isset($v['category_id']) && $v['min_sale_qty'] && $v['max_sale_qty']){
            $bssgr[]= $v['customer_group_id'].'/'.$v['category_id'];
              if (strlen($v['min_sale_qty'])>0) {
                 $min = $v['min_sale_qty'];
                 if (!ctype_digit($min)) {
                     Mage::throwException("Min Qty must be numeric");
                 }
                 if ($v['min_sale_qty']<1) {
                  Mage::throwException("Value of Min Qty must larger 0");
                }
              }
              if (strlen($v['max_sale_qty'])>0 ) {
                 $max = $v['max_sale_qty'];
                 if (!ctype_digit($max)) {
                     Mage::throwException("Max Qty must be numeric");
                 }
                  if ($v['max_sale_qty']<1) {
                    Mage::throwException("Value of Max Qty must larger 0");
                }
              }
              if ($v['min_sale_qty']>0 && $v['max_sale_qty']>0) {
                  $minus = $v['min_sale_qty'] - $v['max_sale_qty'];
                  if ($minus >= 0) {
                      $error = true;
                  }
              }
          }
        }
        if ($error) {
           Mage::throwException("Value of Min Qty can't larger  Value of Max Qty ");
        }
        foreach (array_count_values($bssgr) as $key => $value) {
            if ($value >1 ) {
                Mage::throwException("Duplicate  category vs category and groupcustomer vs groupcustomer");
            }
        }

        return parent::save();
    }
    
}