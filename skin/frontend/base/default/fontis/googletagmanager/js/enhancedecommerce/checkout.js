window.FGtmEnhancedEcommerceCheckout = Class.create();

FGtmEnhancedEcommerceCheckout.prototype = {
    initialize: function() {},
    trackCheckoutStep: function(step, cartItems) {
        if (typeof window.FGtmEEUtil === 'undefined' || !FGtmEEUtil.isSiteReady) {
            return;
        }

        dataLayer.push({
            'event': 'checkout',
            'ecommerce': {
                'checkout': {
                    'actionField': {'step': step},
                    'products': cartItems
                }
            }
        });
    },

    pushCheckoutValidationError: function(message) {
        if (typeof window.FGtmEEUtil === 'undefined' || !FGtmEEUtil.isSiteReady) {
            return;
        }

        dataLayer.push({
            'event': 'CheckoutError',
            'text': message
        });
    }
};

window.FGtmEECheckout = new FGtmEnhancedEcommerceCheckout;
