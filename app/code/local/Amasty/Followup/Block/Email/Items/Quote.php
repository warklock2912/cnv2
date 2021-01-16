<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
    class Amasty_Followup_Block_Email_Items_Quote extends Amasty_Followup_Block_Email_Items
    {
        function getItems(){
            return $this->getQuote()->getAllVisibleItems();
        }
        
        function loadProduct($quoteItem){
            return Mage::getModel('catalog/product')
                ->setStoreId($quoteItem->getQuote()->getStoreId())
                ->load($quoteItem->getProductId());
        }
        
//        public function getPrice($_item){
//            return $this->showPriceIncTax() ? $_item->getRowTotalInclTax() : $_item->getRowTotal();
//        }
        
        public function getCurrencyCode(){
            return $this->getQuote()->getQuoteCurrencyCode();
        }
    }
?>


