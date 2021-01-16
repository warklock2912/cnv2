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

class Fontis_GoogleTagManager_Block_DataLayer_Personal extends Fontis_GoogleTagManager_Block_DataLayer
{
    /**
     * Render Google Tag Manager code for personalised visitor data.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('googletagmanager')->isGtmEnabled()) {
            return $this->getEmptyContent();
        }

        $personalDataLayer = Mage::helper('googletagmanager/provider_personal')->getDataLayer();

        if (!$personalDataLayer) {
            return $this->getEmptyContent();
        }

        $html = '<form>' . "\n";
        $html .= '<input type="hidden" id="gtm-datalayer-personal-encoded-data" value="' . $this->quoteEscape(Zend_Json::encode($personalDataLayer)) . '">';
        $html .= '</form>';
        return $html;
    }
}
