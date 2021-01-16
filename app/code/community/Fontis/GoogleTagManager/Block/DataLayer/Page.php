<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_GoogleTagManager_Block_DataLayer_Page extends Fontis_GoogleTagManager_Block_DataLayer
{
    /**
     * Generate JavaScript for the container snippet.
     *
     * @return string
     */
    protected function _getContainerSnippet()
    {
        // Get the container ID.
        $containerId = Mage::helper('googletagmanager')->getContainerId();
        $pageVariables = http_build_query($this->_getPageData());

        // Render the container snippet JavaScript.
        return "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','" . $containerId . "');</script>";
    }

    /**
     * Generate JavaScript for the data layer.
     *
     * @return string|null
     */
    protected function _getDataLayer()
    {
        // Initialise our data source.
        $data = $this->_getPageData();

        // Generate the data layer JavaScript.
        if (!empty($data)) {
            return "<script>window.dataLayer = window.dataLayer || [];\nwindow.dataLayer.push(" . Zend_Json::encode($data) . ");</script>\n";
        } else {
            return '';
        }
    }

    /**
     * Get page data for use in the data layer.
     *
     * @return array
     */
    protected function _getPageData()
    {
        $items = Mage::getConfig()->getNode('frontend/dataLayer')->asArray();

        foreach ($items as $key => $item) {
            if (Mage::helper($item)->isEnabled()) {
                $pageArray[$key] = Mage::helper($item)->getData();
            }
        }

        $data = array();
        if (isset($pageArray)) {
            $data = Mage::helper('tokens')->tokeniseArray($pageArray);
        }

        $data['pageType'] = Mage::helper('googletagmanager')->getPageType();
        $data['secure'] = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false';
        return $data;
    }

    /**
     * Render Google Tag Manager code.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('googletagmanager')->isGtmEnabled()) {
            $containerSnippet = $this->_getContainerSnippet();
            $dataLayer = $this->_getDataLayer();
            return  $dataLayer . $containerSnippet;
        }  else {
            return $this->getEmptyContent();
        }
    }
}
