<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @return Magebuzz_Imagehome_Model_Imagehome
     */
    function getImagehome($id) {
        /** @var Magebuzz_Imagehome_Model_Imagehome $Imagehomes **/
        $Imagehomes = Mage::getModel('imagehome/imagehome')->load($id);
        return $Imagehomes;
    }

    function getImagehomes() {
        $Imagehomes = Mage::getModel('imagehome/imagehome')->getCollection()->getFirstItem();
        return $Imagehomes;
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoriesOptions()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $categories **/
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->addLevelFilter(2)
            ->addAttributeToFilter('name', 'Featured styles');
        /** @var Mage_Catalog_Model_Category $category **/
        $category = $categories->getFirstItem();
        return $category;
    }
    public function getBannerOptions()
    {
        $banners=Mage::getResourceModel('bannerads/bannerads_collection');
        return $banners;

    }
    public function processCutomBlock($Imagehome_grid)
    {
        $grids_data=json_decode($Imagehome_grid);
        foreach ($grids_data as $key=>$grid)
        {
            $grid_data=get_object_vars($grid);
            if($grid_data['category'] != "" || $grid_data['banner'] !="")
            {
                $new_grid=$grid;
                unset($grids_data[$key]);
                array_unshift($grids_data, $new_grid);
                ////custom grid go to the top
            }
        }
        return json_encode($grids_data);
    }

}
