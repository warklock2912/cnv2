<?php

class Magebuzz_Imagehome_Block_Products extends Mage_Core_Block_Template
{

    protected $_template = 'imagehome/products.phtml';

	public function _prepareLayout() {
		return parent::_prepareLayout();
    }
    public function getCategoryTitleFE()
    {
        return $this->getCategoryTitle();
    }
    public function getCategoryUrlFE()
    {
        return $this->getUrl($this->getCategoryUrl());
    }

    public function getCategoryProducts()
    {
        $categoryId  = $this->getCategoryId();
        if($categoryId){
            /** @var Mage_Catalog_Model_Category $category **/
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if($category->getId()){
                /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection **/
                $productCollection = $category->getProductCollection()
                    ->setOrder('product_sort','DESC')
                    ->setOrder('entity_id', 'DESC')
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addAttributeToFilter('is_sneaker', 1)
                    ->setCurPage(1)
                    ->setPageSize(12);
                if($productCollection->getSize()){
                    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
                    Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
                    return $productCollection;
                }
            }
        }
        return null;
    }

}