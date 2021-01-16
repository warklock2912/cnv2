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

class Bss_MinMaxQtyOrderPerCate_Model_Observer
{

    public function limitMinMaxQty($observer)
    {
        if(Mage::helper('minmaxqtyorderpercate')->getConfig('enable')) {
            Mage::getSingleton('checkout/session')->getMessages(true);
            
            if (Mage::helper('checkout/cart')->getItemsCount() == 0) {
                return;
            }
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $cartItems = $quote->getAllVisibleItems();

            $cates_qty = array();
            $cates_name = array();
            foreach ($cartItems as $item){

                $cates = $item->getProduct()->getCategoryIds();
                $cates_name[$item->getName()] = $cates;

                foreach ($cates as  $cate) {
                    if (is_array($cates_qty) and !empty($cates_qty[$cate]) ) {
                        $cates_qty[$cate] += $item->getQty();
                    }else{
                        $cates_qty[$cate] = $item->getQty();
                    }
                }
            }

            $customer = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $_qty = Mage::helper('minmaxqtyorderpercate')->OrderQty($cates_qty, $customer);

            if (!empty($_qty)) {
                if(!empty($_qty['min_qty'])) {
                    foreach ($_qty['min_qty'] as $catid => $qtylimit) {
                        $product_names = array();
                            foreach ($cates_name as $prt_name => $catids) {
                                if (in_array($catid,$catids)) {
                                    $product_names[] = $prt_name;
                                }
                            }
                        $product_name = implode(',',$product_names);
                        $cate_name = Mage::getModel('catalog/category')->load($catid)->getName();
                        $message = str_replace("{{category_name}}",$cate_name,Mage::helper('minmaxqtyorderpercate')->getConfig('mess_err_min'));
                        $message = str_replace("{{qty_limit}}",$qtylimit,$message);
                        $message = str_replace("{{product_name}}",$product_name,$message);
                        // $message = "The min quantity allowed for purchase at category ".$namecate." is ".$k;
                        Mage::getSingleton('core/session')->addError($message); 
                    }

                }
                if(!empty($_qty['max_qty'])) {
                    foreach ($_qty['max_qty'] as $catid => $qtylimit) {
                        $product_names = array();
                            foreach ($cates_name as $prt_name => $catids) {
                                if (in_array($catid,$catids)) {
                                    $product_names[] = $prt_name;
                                }
                            }
                        $product_name = implode(',',$product_names);
                        $cate_name = Mage::getModel('catalog/category')->load($catid)->getName();
                        $message = str_replace("{{category_name}}",$cate_name,Mage::helper('minmaxqtyorderpercate')->getConfig('mess_err_max'));
                        $message = str_replace("{{qty_limit}}",$qtylimit,$message);
                        $message = str_replace("{{product_name}}",$product_name,$message);
                        // $message = "The min quantity allowed for purchase at category ".$namecate." is ".$k;
                        Mage::getSingleton('core/session')->addError($message); 
                    }

                }
                $quote->setHasError(true);
            }
        }
    }
}