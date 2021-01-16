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
 * @copyright  Copyright (c) 2016 Fontis Pty. Ltd. (https://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_GoogleTagManager_Block_DataLayer_NoScript extends Fontis_GoogleTagManager_Block_DataLayer_Page
{
    /**
     * Generate the GTM iframe code for pages without Javascript enabled
     * @return string
     */
    protected function _getNoScriptSnippet()
    {
        // Get the container ID.
        $containerId = Mage::helper('googletagmanager')->getContainerId();
        $pageVariables = http_build_query($this->_getPageData());

        // Render the container snippet JavaScript.
        return "<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id=" . $containerId . "&event=unload&" . $pageVariables . "\"
height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>";
    }

    /**
     * Render Google Tag Manager code.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('googletagmanager')->isGtmEnabled()) {
            return $this->_getNoScriptSnippet();
        }  else {
            return $this->getEmptyContent();
        }
    }
}
