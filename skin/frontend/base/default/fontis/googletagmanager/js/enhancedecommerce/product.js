window.FGtmEnhancedEcommerceProduct = Class.create();

FGtmEnhancedEcommerceProduct.prototype = {
    pushedProductImpressions: [],
    initialize: function() {
        if (document.body.classList.contains('fontis-algolia-index-index')) {
            return;
        }

        document.observe('fontis_gtm_ee:site_ready', function(event) {
            FGtmEEProduct.pushImpressionsInViewport();
            FGtmEEProduct.setupProductLinkTracking();
            document.addEventListener('scroll', FGtmEEProduct.pushImpressionsInViewport);
        });
    },

    pushProductImpressions: function(elements) {
        newImpressions = [];
        elements.each(function(element, index) {
            if (!FGtmEEUtil.isElementVisible(element)) {
                return;
            }

            var FGtmEESettings = JsVarsHelper.readValue('FGtmEESettings'),
                productData = JSON.parse(element.readAttribute(FGtmEESettings.productDataAttribute)),
                productId = productData.id;

            // Only push product impressions once on each page load.
            if (FGtmEEProduct.pushedProductImpressions.indexOf(productId) !== -1) {
                return;
            }

            var productObj = FGtmEEUtil.getProductData(element, productData);

            newImpressions.push({
                'name': productObj.name,
                'id': productObj.id,
                'brand': productObj.brand,
                'category': productObj.category,
                'list': productObj.list,
                'position': index + 1,
                'price': productObj.price,
                'variant': productObj.variant
            });

            FGtmEEProduct.pushedProductImpressions.push(productId);
        });

        if (newImpressions.length === 0) {
            return;
        }

        window.dataLayer.push({
            'ecommerce' : {
                'impressions' : newImpressions
            },
            'event' : 'ImpressionsPushed'
        });
    },

    pushImpressionsInViewport: function() {
        if (typeof window.FGtmEEUtil === 'undefined' || !FGtmEEUtil.isSiteReady) {
            return;
        }

        FGtmEEProduct.pushProductImpressions($$(JsVarsHelper.readValue('FGtmEESettings').productSelector));
    },

    pushProductClicks: function(event) {
        if (Event.isRightClick(event)) {
            return;
        }

        var link = $(event.target),
            FGtmEESettings = JsVarsHelper.readValue('FGtmEESettings');

        if (link.readAttribute('class').indexOf(FGtmEESettings.productLinkSelector) === -1) {
            link = link.up(FGtmEESettings.productLinkSelector);
        }

        var position = null;
        $$(FGtmEESettings.productLinkSelector).each(function(element, index) {
            if (element === link) {
                position = index + 1;
                return false;
            }
        });

        var productData = JSON.parse(link.readAttribute(FGtmEESettings.productDataAttribute));
        var productObj = FGtmEEUtil.getProductData(link, productData);
        var dataLayerData = {
            'event': 'ProductClick',
            'ecommerce': {
                'click': {
                    'actionField': {'list': productObj.list},
                    'products': [{
                        'name': productObj.name,
                        'id': productObj.id,
                        'price': productObj.price,
                        'brand': productObj.brand,
                        'category': productObj.category,
                        'position': position,
                        'variant': productObj.variant
                    }]
                }
            }
        };

        dataLayer.push(dataLayerData);
    },

    setupProductLinkTracking: function() {
        if (typeof window.FGtmEEUtil === 'undefined' || !FGtmEEUtil.isSiteReady) {
            return;
        }

        Event.on(document, 'click', JsVarsHelper.readValue('FGtmEESettings').productLinkSelector, this.pushProductClicks);
    }
};

window.FGtmEEProduct = new FGtmEnhancedEcommerceProduct;
