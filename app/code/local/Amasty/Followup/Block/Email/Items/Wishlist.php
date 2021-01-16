<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
    class Amasty_Followup_Block_Email_Items_Wishlist extends Amasty_Followup_Block_Email_Items
    {
        protected $_wishlist;
        
        function getItems(){
            if( !$this->_wishlist ) {
                
                $this->_wishlist = Mage::getModel('wishlist/wishlist')
                    ->loadByCustomer($this->getCustomer());
                
                $this->_wishlist->getItemCollection()
                    ->load();
            }
            return $this->_wishlist->getItemCollection();
        }
        
        function loadProduct($wishListItem){
            return $wishListItem->getProduct();
        }
        
        public function getPrice($_product){
            $price = 0;
        
            if ($this->showPriceIncTax()){
                $price = Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice());
            } else {
                $price = $_product->getFinalPrice();
            }
            
            return $price;
        }
        
    }
?>


