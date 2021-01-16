<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


require_once 'Mage/Catalog/controllers/Seo/SitemapController.php';

class Amasty_SeoHtmlSitemap_Seo_SitemapController extends Mage_Catalog_Seo_SitemapController
{
    /**
     * Display categories listing
     *
     */
    public function categoryAction()
    {
        /** @var $helper Amasty_SeoHtmlSitemap_Helper_Data */
        $helper = Mage::helper('amseohtmlsitemap');

        if (Mage::getStoreConfig($helper::CONFIG_CATEGORIES_REDIRECT_DEFAULT)) {
            $this->_redirect(Mage::helper('amseohtmlsitemap/url')->getSitemapUrl(false));
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        if (Mage::helper('catalog/map')->getIsUseCategoryTreeMode()) {
            $update->addHandle(strtolower($this->getFullActionName()).'_tree');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }

    /**
     * Display products listing
     *
     */
    public function productAction()
    {
        /** @var $helper Amasty_SeoHtmlSitemap_Helper_Data */
        $helper = Mage::helper('amseohtmlsitemap');

        if (Mage::getStoreConfig($helper::CONFIG_PRODUCTS_REDIRECT_DEFAULT)) {
            $this->_redirect(Mage::helper('amseohtmlsitemap/url')->getSitemapUrl(false));
        }

        $this->loadLayout();
        $this->renderLayout();
    }
}