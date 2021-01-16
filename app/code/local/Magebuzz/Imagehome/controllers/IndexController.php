<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function productsAction()
    {
        $categoryId = $this->getRequest()->getParam('categoryId');
        $categoryTitle= $this->getRequest()->getParam('categoryTitle');
        $categoryUrl= $this->getRequest()->getParam('categoryUrl');
        if($categoryId){
            /** @var Magebuzz_Imagehome_Block_Products $products **/
            $products = $this->getLayout()->createBlock('imagehome/products', 'image.home.products')
                ->setCategoryId($categoryId)
                ->setCategoryTitle($categoryTitle)
                ->setCategoryUrl($categoryUrl);

            $this->getResponse()->setBody($products->toHtml());
        }
    }
    public function bannerAction()
    {
        $todayStartOfDayDate = Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate = Mage::app()->getLocale()->date()->setTime('23:59:59')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
             $bannerId=$this->getRequest()->getParam('bannerId');
             $bannerTitle=$this->getRequest()->getParam('bannerTitle');
             $bannerUrl=$this->getRequest()->getParam('bannerUrl');
             if($bannerId)
             {
                $banner=Mage::getResourceModel('bannerads/bannerads_collection')
                     ->addFieldToFilter('status', 1)
                     ->addFieldToFilter('from_date', array('or' => array(0 => array('date' => TRUE, 'to' => $todayEndOfDayDate), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')
                     ->addFieldToFilter('to_date', array('or' => array(0 => array('date' => TRUE, 'from' => $todayStartOfDayDate), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')
                     ->addFieldToFilter('block_id',$bannerId)->getFirstItem();
                if($banner->getId())
                {
                    $block = $this->getLayout()->createBlock('bannerads/blockdata')->setTemplate('imagehome/banner.phtml')
                        ->setBanneradsData($banner)
                        ->setBannerTitleCustom($bannerTitle)
                        ->setBannerUrlCustom($bannerUrl);
                    $this->getResponse()->setBody($block->toHtml());
                }
             }
    }

}
