<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    protected $request;

    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $front->addRouter('amseohtmlsitemap', $this);
        return $this;
    }


    public function match(Zend_Controller_Request_Http $request)
    {
        $storeUrl = Mage::getStoreConfig('amseohtmlsitemap/sitemap_fontend_url');
        $realUrl = $request->getPathInfo();
        $this->request = $request;
        if (($realUrl == "/" . $storeUrl) && (stripos($realUrl, ".html"))) {
            $this->forwardAmseohtmlsitemap();
        } elseif (($realUrl == "/" . $storeUrl . "/")) {
            $this->forwardAmseohtmlsitemap();
        } elseif (stripos($realUrl, $storeUrl)) {
            $this->forwardAmseohtmlsitemap();
        }
        if (($realUrl == "/" . $storeUrl) && (!stripos($realUrl, ".html"))) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl(Mage::getStoreConfig('amseohtmlsitemap/sitemap_fontend_url') . "/"))->sendResponse();
            exit;
        }
    }

    protected function forwardAmseohtmlsitemap()
    {
        $reservedKey = "seohtmlsitemap";
        $realModule = 'Amasty_SeoHtmlSitemap';

        $this->request->setPathInfo($reservedKey);
        $this->request->setModuleName('amseohtmlsitemap');
        $this->request->setRouteName('amseohtmlsitemap');
        $this->request->setControllerName('index');
        $this->request->setActionName('index');
        $this->request->setControllerModule($realModule);

        $file = Mage::getModuleDir('controllers', $realModule) . DS
            . 'IndexController.php';
        include $file;

        //compatibility with 1.3
        $class = $realModule . '_IndexController';
        $controllerInstance = new $class(
            $this->request, $this->getFront()->getResponse()
        );

        $this->request->setDispatched(true);
        $controllerInstance->dispatch('index');
    }
}