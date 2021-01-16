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
 * @copyright  Copyright (c) 2015 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

var gtmPushDataLayer = function(event) {
    if (typeof FCache !== 'undefined' && !FCache.checkReady(this, event)) {
        return false;
    }

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(gtmGatherPersonalDataLayer());
    window.dataLayer.push({'event' : 'ready'});
};

var gtmGatherPersonalDataLayer = function() {
    var formElement = $("gtm-datalayer-personal-encoded-data");
    if (formElement) {
        return JSON.parse(formElement.getValue());
    } else {
        return {};
    }
};

document.addEventListener('DOMContentLoaded', gtmPushDataLayer);
document.addEventListener('pagecache-content-loaded', gtmPushDataLayer);
