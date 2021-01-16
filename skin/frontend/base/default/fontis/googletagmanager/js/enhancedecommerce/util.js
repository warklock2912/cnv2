window.FGtmEnhancedEcommerceUtil = Class.create();

/**
 * Events - prefixed with "fontis_gtm_ee:"
 *
 * - site_ready
 */

FGtmEnhancedEcommerceUtil.prototype = {
    isSiteReady: false,

    initialize: function() {
        document.addEventListener('DOMContentLoaded', this.setupTracking);
        document.addEventListener('pagecache-content-loaded', this.setupTracking);
    },

    dispatchEnhancedEcommerceEvent: function(name, data) {
        document.fire("fontis_gtm_ee:" + name, data);
    },

    // http://stackoverflow.com/questions/2332811/capitalize-words-in-string
    capitaliseWords: function(value) {
        return value.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    },

    isElementVisible: function(element) {
        var viewportTop = document.viewport.getScrollOffsets()['top'];
        var viewportBottom = viewportTop + document.viewport.getHeight();

        var elementTop = element.cumulativeOffset()['top'];
        var elementBottom = elementTop + element.getHeight();

        return ((elementBottom <= viewportBottom) && (elementTop >= viewportTop));
    },

    getProductData: function(element, data) {
        var bodyClasses = $w(document.body.className);
        return {
            'id': data.id,
            'name': data.name,
            'price': data.price,
            'brand': data.brand,
            'variant': data.variant,
            'category': data.category,
            'list': bodyClasses.length ? this.capitaliseWords(bodyClasses[0].gsub('-', ' ')) : '',
            'url': element.readAttribute('href')
        };
    },

    setupTracking: function(event) {
        if (typeof JsVarsHelper.readValue('FGtmEESettings') === 'undefined') {
            return;
        }

        if (typeof window.dataLayer === 'undefined') {
            return;
        }

        if (typeof FCache !== 'undefined' && !FCache.checkReady(this, event)) {
            return;
        }

        FGtmEEUtil.isSiteReady = true;
        FGtmEEUtil.dispatchEnhancedEcommerceEvent('site_ready');
    }
};

window.FGtmEEUtil = new FGtmEnhancedEcommerceUtil;
