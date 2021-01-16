<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Model_Sitemap extends Mage_Core_Model_Abstract
{
	protected $_path;
	protected $_file;
	protected $_date;
	protected $_xml = array();
	protected $_baseUrl;
	protected $_storeId;
	protected $_iterator = 1;
    protected $_excludeUrls;

	public function _construct()
	{
		parent::_construct();
		$this->_init('amseogooglesitemap/sitemap');
	}

	public function getExternalLinks($storeId)
	{
		$profiles = $this->getResourceCollection()->addFieldToFilter('stores', $storeId);

		$links = array();
		foreach ($profiles as $profile) {
			$collection = explode(chr(13), $profile->getExtraLinks());
			if (count($collection) > 0) {
				foreach ($collection as $link) {
					$link         = trim($link);
					$links[$link] = $link;
				}
			}
		}

		return $links;
	}

	public function run()
	{
        $this->_removeOldFiles();
		$this->generateXml();
	}

	public function getSitemapFileName()
	{
		$filename = pathinfo($this->getFolderName());

		return $filename['basename'];
	}

    /**
     * Return full file name with path
     *
     * @return string
     */
    public function getPreparedFilename()
    {
        return $this->getPath() . $this->getSitemapFilename();
    }

    /**
     * Generate XML file
     *
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
        $this->_xml     = array();
        $this->_storeId = $this->getStores();

        $this->_date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $this->_baseUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        $this->_generateCategories();
        $this->_generateProducts();
        $this->_generateCms();
        $this->_generateExtra();
        $this->_generateTags();

        if (Mage::helper('core')->isModuleEnabled('Amasty_Xlanding')) {
            $this->_generateLanding();
        }

        if (Mage::helper('core')->isModuleEnabled('Amasty_Shopby')) {
            $this->_generateBrands();
        }

        if (Mage::helper('core')->isModuleEnabled('Magpleasure_Blog')) {
            $this->_generateBlog();
        }

        $pieces = array();
        $this->_iterator = 0;
        $isChunk = $this->getMaxItems() > 0 && count($this->_xml) > $this->getMaxItems();
        if ($isChunk) {
            $split           = array_chunk($this->_xml, $this->getMaxItems(), false);
            foreach ($split as $chunk) {
                $pieces = array_merge($pieces, $this->_writePortion($chunk, false));
            }
        } else {
            $pieces = $this->_writePortion($this->_xml, true);
            $this->_renameFirstFile($pieces);
        }

        $this->_writeIndexFile($pieces);

        $this->setLastRun(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    public function parsePlaceholder($product)
    {
        $txt = $this->getProductsCaptionsTemplate();
        if ($txt == '') {
            return $txt;
        }

        $vars = array();
        preg_match_all('/{([a-zA-Z:\_0-9]+)}/', $txt, $vars);
        if (! $vars[1]) {
            return $txt;
        }
        $vars = $vars[1];

        foreach ($vars as $var) {
            $value = '';
            switch ($var) {
                case 'product_name':
                    $value = $product->getName();
                    break;
            }
            $txt = str_replace('{' . $var . '}', $value, $txt);
        }

        return $txt;
    }

    protected function _beforeSave()
    {
        $io       = new Varien_Io_File();
        $realPath = $io->getCleanPath($this->getPath());

        /**
         * Check path is allow
         */
        if (! $io->allowedPath($realPath, Mage::getBaseDir())) {
            Mage::throwException(Mage::helper('sitemap')->__('Please define correct path'));
        }
        /**
         * Check exists and writeable path
         */
        if (! $io->fileExists($realPath, false)) {
            Mage::throwException(Mage::helper('sitemap'
            )->__('Please create the specified folder "%s" before saving the sitemap.',
                Mage::helper('core')->htmlEscape($this->getPreparedFilename())
            )
            );
        }

		if (! $io->isWriteable($realPath)) {
			Mage::throwException(Mage::helper('sitemap')->__('Please make sure that "%s" is writable by web-server.',
					$this->getPreparedFilename()
				)
			);
		}

        /**
         * Check allow filename
         */
        if (! preg_match('#\.xml$#', $this->getSitemapFilename())) {
            $this->setSitemapFilename($this->getSitemapFilename() . '.xml');
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', Mage::getBaseDir()), '', $realPath), '/') . '/');

        return parent::_beforeSave();
    }

    protected function _removeOldFiles()
    {
        $io       = new Varien_Io_File();
        $realPath = $io->getCleanPath($this->getPath());
        $fileName = $this->getSitemapFilename();
        $pos = strpos($fileName, ".xml");
        $noExtensionFileName = substr($fileName, 0, $pos);
        $fullFilePath = $realPath . $noExtensionFileName . "_*";
        array_map("unlink", glob($fullFilePath));
    }

    /**
     * Return real file path
     *
     * @return string
     */
    protected function getPath()
    {
        if (is_null($this->_filePath)) {
            $dirname = pathinfo($this->getFolderName());
            if ($dirname['dirname'] == '.') {
                $this->_filePath = str_replace('//', '/', Mage::getBaseDir() . '/');
            } else {
                $this->_filePath = str_replace('//', '/', Mage::getBaseDir() . '/' . $dirname['dirname'] . '/');
            }
        }

        return $this->_filePath;
    }

	protected function getDirUrl()
	{
		$dirname = pathinfo($this->getFolderName());

		return $dirname['dirname'] . '/';
	}

    protected function _writeIndexFile($pieces)
	{
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $this->getPath()));

		$this->_date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
		$this->_baseUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

		$name = $this->getSitemapFileName();
		$io->streamOpen($name);
		$io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$io->streamWrite('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		foreach ($pieces as $url) {
			$item = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>',
				htmlspecialchars($this->_baseUrl . $url),
				$this->_date
			);
			$io->streamWrite($item . "\n");
		}
		$io->streamWrite('</sitemapindex>');
		$io->streamClose();
	}

	protected function _writePortion($chunk, $index = false)
	{
		$pieces = array();
		$path = $this->getPath();
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));

		$name = $this->getSitemapFileName();
		$this->_iterator++;
		if (! $index) {
			$name = str_replace('.xml', '', $name);
			$name .= '_' . $this->_iterator . '.xml';
		}

		$fullPath = $this->getDirUrl() . $name;
		$pieces[] = $fullPath;

		$io->streamOpen($name);
		$io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ');
		if ($this->getProductsThumbs() || $this->getCategoriesThumbs()) {
			$io->streamWrite('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
		}
		$io->streamWrite('>');

		for ($i = 0; $i < count($chunk); $i ++) {
            if (false === strpos($chunk[$i], '<loc></loc>')) {
                $io->streamWrite($chunk[$i] . "\n");
                if (isset($chunk[$i + 1])) {
                    $fileSize = $this->_testFileSize($fullPath, $chunk[$i + 1]);
                    if ($this->getMaxFileSize() && $fileSize > ($this->getMaxFileSize() * 1024)) {
                        $newArray = array_slice($chunk, $i + 1);
                        $pieces = array_merge($pieces, $this->_writePortion($newArray, false));
                        break;
                    }
                }
            }
		}
		$io->streamWrite('</urlset>');
		$io->streamClose();

		return $pieces;
	}

	protected function _renameFirstFile(&$chunks)
	{
		$path = $this->getPath();
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));

		$name = $this->getSitemapFileName();
		$newFileName = str_replace('.xml', '', $name);
		$newFileName .= '_1.xml';

		$chunks[0] = $this->getDirUrl() . $newFileName;

		$io->cp($name, $newFileName);
		unlink($path . $name);
	}

	protected function _testFileSize($testFile, $line)
	{
		$mediaDir = Mage::getBaseDir('media') . '/';
		$fileName = 'am_sitemap_test' . rand(1, 1000) . '.xml';
		$io       = new Varien_Io_File();
		$io->cp($testFile, $mediaDir . $fileName);

		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $mediaDir));
		$io->streamOpen($fileName ,'a+');
		$io->streamWrite($line);
		$io->streamWrite('</urlset>');
		$io->streamClose();

		$fileSize = filesize($mediaDir . $fileName);
		unlink($mediaDir . $fileName);

		return $fileSize;
	}

	protected function _generateCategories()
	{
		if (! $this->getCategories()) {
			return;
		}

		$changefreq = $this->getCategoriesFrequency();
		$priority   = $this->getCategoriesPriority();

		/** @var Amasty_SeoToolKit_Model_Data $toolKitModel */
        Mage::app()->setCurrentStore($this->_storeId);
        $toolKitModel = Mage::getModel('amseotoolkit/data');
        $collection   = $toolKitModel->getCategoryCollection();
        $storeUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        $collection->load();

        foreach ($collection as $item) {
            if (empty($item['url'])) {
                continue;
            }

            if ($this->_isUrlToExclude($item['url'])) {
                continue;
            }

			$xmlLine   = '<url><loc>%s</loc><priority>%.2f</priority><changefreq>%s</changefreq>';
			$xmlParams = array(
				htmlspecialchars($storeUrl . $item['url']),
				$priority,
				$changefreq
			);

			if ($this->getCategoriesThumbs() && $item->getImage()) {
				$xmlLine .= '<image:image>';
				if ($this->getCategoriesCaptions()) {
					$xmlLine .= '<image:title>%s</image:title>';
					$xmlParams[] = $item->getName();
				}
				$xmlLine .= '<image:loc>%s</image:loc></image:image>';
				$thumb       = Mage::getBaseUrl('media') . 'catalog/category/' . $item->getImage();
				$xmlParams[] = htmlspecialchars($thumb);
			}

            if ($this->getCategoriesModified()) {
                $xmlLine .= '<lastmod>%s</lastmod>';
				$updateTime = strtotime($item->getUpdatedAt());
				$xmlParams[] = date('c', $updateTime);
            }

			$xmlLine .= '</url>';
			$this->_xml[] = vsprintf($xmlLine, $xmlParams);

		}
        Mage::app()->setCurrentStore(0);

		unset($collection);
	}

	protected function _generateProducts()
	{
		if (! $this->getProducts()) {
			return;
		}

		$changefreq = $this->getProductsFrequency();
		$priority   = $this->getProductsPriority();

		/** @var Amasty_SeoToolKit_Model_Data $toolKitModel */
		$toolKitModel = Mage::getModel('amseotoolkit/data');
		$collection   = $toolKitModel->getProductCollection($this->_storeId);

        $collection->setPageSize(100);
        $pages = $collection->getLastPageNumber();
        $currentPage = 1;

        $collection->load();

        do {
            Mage::app()->setCurrentStore(0);
            $collection->setCurPage($currentPage);
            $collection->load();
            Mage::app()->setCurrentStore($this->_storeId);

            foreach ($collection as $item) {
                $productUrl = $item->getProductUrl(false);
                if ($this->_isUrlToExclude($productUrl)) {
                    continue;
                }
                $xmlLine   = '<url><loc>%s</loc><priority>%.2f</priority><changefreq>%s</changefreq>';
                $xmlParams = array(
                    htmlspecialchars($productUrl),
                    $priority,
                    $changefreq
                );

                if ($this->getProductsThumbs()) {
					if ($image = $item->getImage()) {
						$image = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
					}
                    if ($image) {
                        $xmlLine .= '<image:image>';
                        if ($this->getProductsCaptions()) {
                            $label = $item->getThumbnailLabel();
                            if ($label == '') {
                                $label = $this->parsePlaceholder($item);
                            }
                            $label       = htmlspecialchars($label);
                            if (!empty($label)) {
                                $xmlLine .= '<image:title>%s</image:title>';
                                $xmlParams[] = $label;
                            }
                        }
                        $xmlLine .= '<image:loc>%s</image:loc></image:image>';
                        $xmlParams[] = htmlspecialchars($image);
                    }
                }

                if ($this->getProductsModified()) {
                    $xmlLine .= '<lastmod>%s</lastmod>';
					$updateTime = strtotime($item->getUpdatedAt());
					$xmlParams[] = date('c', $updateTime);
                }
                $xmlLine .= '</url>';
                $this->_xml[] = vsprintf($xmlLine, $xmlParams);
            }

            $currentPage++;
            $collection->clear();
        } while ($currentPage <= $pages);
        Mage::app()->setCurrentStore(0);

		unset($collection);
	}

	protected function _generateCms()
	{
		if (! $this->getPages()) {
			return;
		}

		$changefreq = $this->getPagesFrequency();
		$priority   = $this->getPagesPriority();
        $collection = Mage::getModel('cms/page')->getCollection();
        $collection->addStoreFilter($this->_storeId);
		$collection->getSelect()
			->where('is_active = 1');
		;

        foreach ($collection as $item) {
            $pageUrl = $this->_baseUrl . $item->getIdentifier();
			if (($this->getExcludeCmsAliases() != '' && strpos($this->getExcludeCmsAliases(), $item->getIdentifier()) !== false)
                || $this->_isUrlToExclude($pageUrl)
            ) {
				continue;
			}

			$xmlLine   = '<url><loc>%s</loc><priority>%.2f</priority><changefreq>%s</changefreq>';
			$xmlParams = array(
				htmlspecialchars($pageUrl),
				$priority,
				$changefreq
			);

			if ($this->getPagesModified()) {
				$xmlLine .= '<lastmod>%s</lastmod>';
				$updateTime = strtotime($item->getUpdateTime());
				$xmlParams[] = date('c', $updateTime);
			}
			$xmlLine .= '</url>';
			$this->_xml[] = vsprintf($xmlLine, $xmlParams);

		}
		unset($collection);
	}

	protected function _generateExtra()
	{

		if (! $this->getExtra()) {
			return;
		}

		$collection = explode(chr(13), $this->getExtraLinks());

		$changefreq = $this->getExtraFrequency();
		$priority   = $this->getExtraPriority();

		foreach ($collection as $item) {
			$this->_xml[] = sprintf('<url><loc>%s</loc><changefreq>%s</changefreq><priority>%.2f</priority></url>',
				htmlspecialchars(trim($item)),
				$changefreq,
				$priority
			);
		}
		unset($collection);
	}

	protected function _generateTags()
	{
		if (! $this->getTags()) {
			return;
		}

		$changefreq = $this->getTagsFrequency();
		$priority   = $this->getTagsPriority();

		$tags = Mage::getModel('tag/tag')->getResourceCollection()
			->addPopularity()
			->addStatusFilter(Mage::getModel('tag/tag')->getApprovedStatus())
			->addStoreFilter($this->_storeId)
			->setActiveFilter()
			->load();

		foreach ($tags as $tag) {
            $tagUrl = $this->_baseUrl . 'tag/product/list/tagId/' . $tag->getTagId();
            if ($this->_isUrlToExclude($tagUrl)) {
                continue;
            }
			$this->_xml[] = sprintf('<url><loc>%s</loc><changefreq>%s</changefreq><priority>%.2f</priority></url>',
				htmlspecialchars($tagUrl),
				$changefreq,
				$priority
			);
		}
		unset($tags);
	}

	protected function _generateLanding()
	{
		if (! $this->getLanding()) {
			return;
		}

		$changefreq = $this->getLandingFrequency();
		$priority   = $this->getLandingPriority();

		$landingPages = Mage::getModel('amlanding/resource_cms_page')->getCollection($this->_storeId);

		foreach ($landingPages as $page) {
            $title = $page->getTitle();
            if (isset($title)) {
                $landingUrl = $this->_baseUrl . $page->getUrl();
                if ($this->_isUrlToExclude($landingUrl)) {
                    continue;
                }
                $this->_xml[] = sprintf('<url><loc>%s</loc><changefreq>%s</changefreq><priority>%.2f</priority></url>',
                    htmlspecialchars($landingUrl),
                    $changefreq,
                    $priority
                );
            }
		}

		unset($landingPages);
	}

	protected function _generateBrands()
	{
		if (! $this->getBrands()) {
			return;
		}

		$attrCode = trim(Mage::getStoreConfig('amshopby/brands/attr'));
		if (!$attrCode) {
			return;
		}

		$changefreq = $this->getBrandsFrequency();
		$priority   = $this->getBrandsPriority();

		$brandPages = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
		$helper = Mage::helper('amshopby/url');

		foreach ($brandPages->getSource()->getAllOptions() as $page) {
			if ($page['value']) {
				$url = $helper->getOptionUrl($attrCode, $page['value']);
				if ($this->_isUrlToExclude($url)) {
					continue;
				}
				$this->_xml[] = sprintf('<url><loc>%s</loc><changefreq>%s</changefreq><priority>%.2f</priority></url>',
					$url,
					$changefreq,
					$priority
				);
			}
		}

		unset($brandPages);
	}

    protected function _generateBlog()
    {
        if (!$this->getBlog()) {
            return;
        }

        $blogLinks = Mage::getModel('mpblog/sitemap')->generateLinks();

        $changefreq = $this->getBlogFrequency();
        $priority   = $this->getBlogPriority();

        foreach ($blogLinks as $link) {
            if ($link['url']) {
                if ($this->_isUrlToExclude($link['url'])) {
                    continue;
                }
                $this->_xml[] = sprintf('<url><loc>%s</loc><changefreq>%s</changefreq><priority>%.2f</priority></url>',
                    str_replace('index.php/', '', $link['url']),
                    $changefreq,
                    $priority
                );
            }
        }
    }

    protected function _isUrlToExclude($url)
    {
        $isToExclude = false;

        if (empty($this->_excludeUrls)) {
            $this->_excludeUrls = $this->getExcludeUrls();
        }

        if ($this->_excludeUrls != '' && strpos($this->_excludeUrls, $url) !== false) {
            $isToExclude = true;
        }

        return $isToExclude;
    }
}