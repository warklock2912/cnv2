<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Block_Sitemap extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime' => 600,
            'cache_tags'     => array(Amasty_SeoHtmlSitemap_Helper_Data::CACHE_TAG),
            'cache_key'      => Amasty_SeoHtmlSitemap_Helper_Data::CACHE_TAG . '_' . Mage::app()->getStore()->getId()
        ));
    }

	public function _beforeToHtml()
    {
        /** @var $helper Amasty_SeoHtmlSitemap_Helper_Data */
        $helper = Mage::helper('amseohtmlsitemap');

        /** @var $dataModel Amasty_SeoHtmlSitemap_Model_Sitemap */
        $dataModel = Mage::getModel('amseohtmlsitemap/sitemap');

        $data = array(
            'links'         => $dataModel->getLinks(),
            'linksTitle'    => trim((string) Mage::getStoreConfig($helper::CONFIG_LINKS_TITLE)),
            'linksColumns'  => Mage::getStoreConfig($helper::CONFIG_LINKS_COLUMN_NUMBER),
            'title'         => trim((string) Mage::getStoreConfig($helper::CONFIG_PAGE_TITLE_PATH)),
            'search'        => Mage::getStoreConfig($helper::CONFIG_SHOW_SEARCH_FIELD),
        );

        //category collection
        if (Mage::getStoreConfig($helper::CONFIG_SHOW_CATEGORIES_PATH)) {
            $categoriesGrid    = Mage::getStoreConfig($helper::CONFIG_CATEGORIES_SHOW_AS);

            $data['categoriesColumns']  = Mage::getStoreConfig($helper::CONFIG_CATEGORIES_COLUMN_NUMBER);
            $data['categoriesTitle']    = trim((string) Mage::getStoreConfig($helper::CONFIG_CATEGORIES_TITLE));
            $data['categoriesGrid']     = $categoriesGrid;
            $data['categories']         = $dataModel->getCategories((Amasty_SeoHtmlSitemap_Model_Source_Gridtype::TYPE_LIST != $categoriesGrid));
        }

        //product collection
        if (Mage::getStoreConfig($helper::CONFIG_SHOW_PRODUCTS_PATH)) {

            $data['productsLetterSplit']    = Mage::getStoreConfig($helper::CONFIG_PRODUCTS_SPLIT_BY_LETTER);
            $data['productsTitle']          = trim((string) Mage::getStoreConfig($helper::CONFIG_PRODUCTS_TITLE));
            $data['productsColumns']        = Mage::getStoreConfig($helper::CONFIG_PRODUCTS_COLUMN_NUMBER);
            $data['products']               = $dataModel->getProducts(Mage::getStoreConfig($helper::CONFIG_PRODUCTS_HIDE_OUT_OF_STOCK), Mage::getStoreConfig($helper::CONFIG_PRODUCTS_SPLIT_BY_LETTER));
        }

        //pages
        if (Mage::getStoreConfig($helper::CONFIG_SHOW_CMS_PAGES_PATH)) {
            $data['pages']              = $dataModel->getPages();
            $data['pagesTitle']         = trim((string) Mage::getStoreConfig($helper::CONFIG_CMS_PAGES_TITLE));
            $data['pagesColumns']       = Mage::getStoreConfig($helper::CONFIG_CMS_COLUMN_NUMBER);
        }

        //landing pages
        if (Mage::getStoreConfig($helper::CONFIG_SHOW_LANDING_PATH)) {
            $data['landingPages']       = $dataModel->getLandingPages();
            $data['landingTitle']       = trim((string) Mage::getStoreConfig($helper::CONFIG_LANDING_TITLE));
            $data['landingColumns']     = Mage::getStoreConfig($helper::CONFIG_LANDING_COLUMN_NUMBER);
        }

        //photo gallery
        if (Mage::getStoreConfig($helper::CONFIG_SHOW_GALLERY_PATH)) {
            $data['galleryColumns']  = Mage::getStoreConfig($helper::CONFIG_GALLERY_COLUMN_NUMBER);
            $data['galleryTitle']    = trim((string)Mage::getStoreConfig($helper::CONFIG_GALLERY_TITLE));
            $data['gallery']         = $dataModel->getGallery();
        }

        $this->addData($data);

        parent::_beforeToHtml();
    }
}