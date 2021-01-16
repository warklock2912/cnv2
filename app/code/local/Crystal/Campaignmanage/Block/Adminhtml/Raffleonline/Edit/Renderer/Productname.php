<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Renderer_Productname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function dd($data) {
        echo "<pre>";

        print_r($data);

        echo "</pre>";
    }

    public function render(Varien_Object $row)
    {
        $productId = $row->getData('product_id');
        $options = $row->getData('option');
        $optionsArray = array(255 => $options);
        $productName = '';

        if($options != 0){
            $productName = $this->getChildProduct($productId, $optionsArray)->getName();
        }else{
            $productName = $this->getProductName($productId);
        }

        return $productName;
    }

    private function getProduct($productID) {
        $product = Mage::getModel('catalog/product')->load($productID);
        return $product;
    }

    private function getProductName($productID) {
        $product = $this->getProduct($productID);
        if (!empty($product)) {
            return $product->getName();
        }
        return "product not found";
    }

    public function getChildProduct($parentProductId, $optionData)
    {
        $product = Mage::getModel('catalog/product')->load($parentProductId);
        if ($optionData) {
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($optionData, $product);
        } else {
            $childProduct = $product;
            if (!$childProduct) {
                return 0;
            }
        }
        return $childProduct;
    }

}
