<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */

class Amasty_SeoHtmlSitemap_Helper_Data extends Mage_Core_Helper_Abstract
{
    // General
    const CONFIG_PAGE_TITLE_PATH                = 'amseohtmlsitemap/general/title';
    const CONFIG_META_DESCRIPTION_PATH          = 'amseohtmlsitemap/general/meta_description';
	const CONFIG_LAYOUT_PATH                    = 'amseohtmlsitemap/general/layout';
    const CONFIG_SHOW_SEARCH_FIELD              = 'amseohtmlsitemap/general/show_search_field';

    // Categories
	const CONFIG_SHOW_CATEGORIES_PATH           = 'amseohtmlsitemap/categories/show_categories';
	const CONFIG_CATEGORIES_TITLE               = 'amseohtmlsitemap/categories/categories_title';
	const CONFIG_CATEGORIES_REDIRECT_DEFAULT    = 'amseohtmlsitemap/categories/redirect';
	const CONFIG_CATEGORIES_SHOW_AS             = 'amseohtmlsitemap/categories/show_as';
	const CONFIG_CATEGORIES_COLUMN_NUMBER       = 'amseohtmlsitemap/categories/column_number';

    // Products
    const CONFIG_SHOW_PRODUCTS_PATH             = 'amseohtmlsitemap/products/show_products';
    const CONFIG_PRODUCTS_TITLE                 = 'amseohtmlsitemap/products/products_title';
    const CONFIG_PRODUCTS_REDIRECT_DEFAULT      = 'amseohtmlsitemap/products/redirect';
    const CONFIG_PRODUCTS_COLUMN_NUMBER         = 'amseohtmlsitemap/products/column_number';
    const CONFIG_PRODUCTS_SPLIT_BY_LETTER       = 'amseohtmlsitemap/products/split_by_letter';
    const CONFIG_PRODUCTS_HIDE_OUT_OF_STOCK     = 'amseohtmlsitemap/products/hide_out_of_stock';

    // CMS Pages
    const CONFIG_SHOW_CMS_PAGES_PATH            = 'amseohtmlsitemap/cms/show_cms_pages';
    const CONFIG_CMS_PAGES_TITLE                = 'amseohtmlsitemap/cms/cms_title';
    const CONFIG_EXCLUDE_CMS_PAGES              = 'amseohtmlsitemap/cms/exclude_cms_pages';
    const CONFIG_CMS_COLUMN_NUMBER              = 'amseohtmlsitemap/cms/column_number';
    const CONFIG_EXCLUDE_CMS_PAGES_PATH         = 'amseohtmlsitemap/cms/exclude_cms_pages_values';

    // Landing Pages
    const CONFIG_SHOW_LANDING_PATH              = 'amseohtmlsitemap/landing/show_landing_pages';
    const CONFIG_LANDING_TITLE                  = 'amseohtmlsitemap/landing/landing_title';
    const CONFIG_LANDING_COLUMN_NUMBER          = 'amseohtmlsitemap/landing/column_number';

    // Additional links
	const CONFIG_LINKS_PATH                     = 'amseohtmlsitemap/additional/additional_links';
	const CONFIG_LINKS_TITLE                    = 'amseohtmlsitemap/additional/links_title';
    const CONFIG_LINKS_COLUMN_NUMBER            = 'amseohtmlsitemap/additional/column_number';

	// Photo Gallery
	const CONFIG_SHOW_GALLERY_PATH              = 'amseohtmlsitemap/gallery/show_gallery';
	const CONFIG_GALLERY_TITLE                  = 'amseohtmlsitemap/gallery/gallery_title';
	const CONFIG_GALLERY_COLUMN_NUMBER          = 'amseohtmlsitemap/gallery/column_number';

    // Other
	const CONFIG_URL                            = 'amseohtmlsitemap/sitemap_fontend_url';
	const CACHE_TAG                             = 'amseo_htmlsitemap';

	/**
	 * Get page layout template
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getLayoutTemplate()
	{
		$layoutType      = Mage::getStoreConfig(self::CONFIG_LAYOUT_PATH);
		$defaultTemplate = null;
		foreach (Mage::getSingleton('page/config')->getPageLayouts() as $layout) {
			if ($layout->getCode() == $layoutType) {
				return $layout->getTemplate();
			}

			if ($layout->getIsDefault()) {
				$defaultTemplate = $layout->getTemplate();
			}
		}

		if (! $defaultTemplate) {
			throw new Exception('Error during getting default page payout');
		}

		return $defaultTemplate;
	}
}