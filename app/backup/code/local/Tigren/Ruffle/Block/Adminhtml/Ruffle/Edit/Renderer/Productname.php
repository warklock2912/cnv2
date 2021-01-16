<?php

 class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Productname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

   public function render(Varien_Object $row){
     $productId = $row->getData('product_id');
     $options = $row->getData('product_options');
     $childSelected = $this->getChildProduct($productId, $options);
     // if($childSelected->getData()){
     //   return $childSelected->getName();
     // }
     if($childSelected){
      return $childSelected->getName();
     }
   }

   public function getChildProduct($parentProductId,$optionData)
   {
     $product = Mage::getModel('catalog/product')->load($parentProductId);
     if($optionData){
     $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);
     }else{
       $childProduct = $product;
       if(!$childProduct){
        return 0;
       }
     }
     return $childProduct;
   }

 }