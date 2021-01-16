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

class Bss_MinMaxQtyOrderPerCate_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_OPTION_MIN_MAX_QTY = 'minmaxqtyorder_per_cate/min_max_qty_gr/min_max_qty';

    public function getConfig($field){
        return Mage::getStoreConfig('minmaxqtyorder_per_cate/min_max_qty_gr/'.$field);
    }
    public function getConfigMinMaxQty($categorie,$customerGroupId)
    {
        $result = '';
        $value = Mage::getStoreConfig(self::XML_PATH_OPTION_MIN_MAX_QTY);
        $value = unserialize($value);
        //CAR-72 : fix case dont have any option in system config
        if(empty($value)){
            return;
        }
        //end
        foreach ($value as $grty) {
            $ss[] = $grty['customer_group_id'];
            if ($grty['customer_group_id'] == $customerGroupId && $grty['category_id'] ) {

                $result['max'][$grty['category_id']] = $grty['max_sale_qty'];
                $result['min'][$grty['category_id']] = $grty['min_sale_qty'];
            }
        }
        return $result;
    }
    
    public function getMinQty($categorie,$customerGroupId)
    {
        $min = array();
        $min = $this->getConfigMinMaxQty($categorie,$customerGroupId);

        $result = array();
        if ( !empty($min['min']) ) {
            
                foreach ($min['min'] as $id => $qty) {
                    if ( !empty($categorie[$id]) && !empty($min['min'][$id]) ) {
                        $bss_vl[$id] = $qty - $categorie[$id];
                    }
                }
                if (isset($bss_vl)) {
                    foreach ($bss_vl as $id => $mmqty) {
                    if ($mmqty > 0) {
                            $result[$id] = $min['min'][$id];
                        }
                    }
                }
            return $result;
        }
        return false;
    }
    public function getMaxQty($categorie,$customerGroupId)
    {
        $max = array();
        $max = $this->getConfigMinMaxQty($categorie,$customerGroupId);
        $result = array();
        if ( !empty($max['max']) ) {
                foreach ($max['max'] as $id => $qty) {
                    if ( !empty($categorie[$id]) && !empty($max['max'][$id]) ) {
                        $bss_vl[$id] = $qty - $categorie[$id];
                    }
                }
                if (isset($bss_vl)) {
                    foreach ($bss_vl as $id => $mmqty) {
                    if ($mmqty < 0) {
                            $result[$id] = $max['max'][$id];
                        }
                    }
                }

            return $result;
        }
        return false;
       
    }

    public function OrderQty($categorie, $customer)
    {
            
        if ($categorie) {
            $minqty = $this->getMinQty($categorie,$customer);
            $maxqty = $this->getMaxQty($categorie,$customer);
            if (isset($minqty) && $minqty != false) {
                $return['min_qty'] =  $minqty;
            }
            if (isset($maxqty) && $maxqty != false) {
                $return['max_qty'] =  $maxqty;
            }
            if ( isset($return['max_qty']) || isset($return['min_qty']) ) {
                return $return;
            }
        }
        return false;
    }

}

     