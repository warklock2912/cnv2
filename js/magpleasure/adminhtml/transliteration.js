/**
 * MagPleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Common
 * @copyright  Copyright (c) 2012-2015 MagPleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
var MpAdminhtmlTransliteration = Class.create();
MpAdminhtmlTransliteration.prototype = {
    initialize:function (params) {
        this.data = {};
        for (key in params) {
            this[key] = this.data[key] = params[key];
        }
    },
    transliterate: function(title, id, processCallback){
        if (this.url && $(id)){

            $(id).addClassName('loading');

            var title = encodeURIComponent(title);
            var url = this.url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));

            /*
             *  Load Dialog Content
             */
            new Ajax.Request(
                url,
                {
                    method: 'post',
                    parameters: {
                        title: title
                    },
                    loaderArea: false,
                    onSuccess: (function(transport){
                        if (transport && transport.responseText) {
                            try {
                                var response = eval('(' + transport.responseText + ')');
                                if (response.slug){

                                    if (typeof(processCallback) == 'function'){
                                        $(id).value = processCallback(response.slug);
                                    } else {
                                        $(id).value = response.slug;
                                    }
                                }
                            } catch (e) {

                            }
                        }
                    }).bind(this),
                    onFailure: function(){
                        $(id).value = title;
                    },
                    onComplete: function(){
                        $(id).removeClassName('loading');
                    }
                }
            );
        }
    }
};