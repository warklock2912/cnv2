<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
    class Amasty_Followup_Block_Email_Items extends Mage_Catalog_Block_Product_Abstract
    {
         protected $_params = array(
            'mode' => array(
                'default' => 'table',
                'available' => array(
                    'list', 'table'
                )
            ),
            'image' => array(
                'default' => 'yes',
                'available' => array(
                    'yes', 'no'
                )
            ),
            'price' => array(
                'default' => 'yes',
                'available' => array(
                    'yes', 'no'
                )
            ),
            'priceFormat' => array(
                'default' => 'exculdeTax',
                'available' => array(
                    'exculdeTax', 'includeTax'
                )
            ),
            'descriptionFormat' => array(
                'default' => 'short',
                'available' => array(
                    'short', 'full', 'no'
                )
            ),
            'discount' => array(
                'default' => 'yes',
                'available' => array(
                    'yes', 'no'
                )
            ),
        );
        
        protected function _getLayoutParam($key){
            return in_array($this->$key, $this->_params[$key]['available']) ? $this->$key : $this->_params[$key]['default'];
        }
        
        public function getMode(){
            return $this->_getLayoutParam('mode');
        }
        
        public function showImage(){
            return $this->_getLayoutParam('image') == 'yes';
        }

        public function showPrice(){
            return $this->_getLayoutParam('price') == 'yes';
        }
        
        public function showShortDescription(){
            return $this->_getLayoutParam('descriptionFormat') == 'short';
        }
        
        public function hideDescription(){
            return $this->_getLayoutParam('descriptionFormat') == 'no';
        }
        
        public function showPriceIncTax(){
            return $this->_getLayoutParam('priceFormat') == 'includeTax';
        }
        
        public function showDiscount(){
            return $this->_getLayoutParam('discount') == 'yes';
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
        
        public function getImageWidht(){
            return 135;
        }
        
        public function getImageHeight(){
            return 135;
        }
        
        public function getCurrencyCode(){
            return Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        
        public function getImageUrl($_product){
            return $this->helper('catalog/image')->init($_product, 'small_image')->resize($this->getImageWidht(), $this->getImageHeight());
        }
        
        public function getDescription($_product){
            $desc = '';
            
            if (!$this->hideDescription()) {
                $desc = $this->showShortDescription() ? $_product->getShortDescription() : $_product->getDescription();
            }
            
            return $desc;
        }

        public function getDiscountPrice($price){
            $discountPrice = $price;

            $sceduleId = $this->getHistory()->getScheduleId();
            $schedule = Mage::getModel('amfollowup/schedule')->load($sceduleId);
                    
            switch($schedule->getCouponType()){
                case "by_percent":
            
                        $discountPrice -= $discountPrice * $schedule->getDiscountAmount() / 100;
                    break;
                case "by_fixed":
                        $discountPrice -= $schedule->getDiscountAmount();
                    break;
            }
            
            return $discountPrice;
        }
        
        public function getProductUrl($product, $additional = array())
        {
            $targetUrl = parent::getProductUrl($product, $additional);
            return Mage::getModel('amfollowup/urlmanager')
                    ->init($this->getHistory())
                    ->get($targetUrl);
        }
        
        function getItems(){
            return array();
        }
        
        public function getItemCount()
        {
            return count($this->getItems());
        }
        
        function loadProduct($_item){
            return $_item instanceof Mage_Catalog_Model_Product ? $_item : null;
        }
    }
?>