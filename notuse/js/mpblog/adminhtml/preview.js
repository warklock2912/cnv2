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
 * @package    Magpleasure_Blog
 * @copyright  Copyright (c) 2012-2015 MagPleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

var MpBlogPreview = Class.create();
MpBlogPreview.prototype = {
    initialize: function (params) {
        for (key in params) {
            this[key] = params[key];
        }
    },
    preview: function(){

        var data = {
            header: $(this.header_id).value,
            content: $(this.content_id).value,
            post_thumbnail: null,
            list_thumbnail: $(this.list_thumbnail_id).value,
            width: this.width,
            height: this.height
        };

        new Ajax.Request(
            this.url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')), {
                method: 'post',
                parameters: data,
                onComplete: (function(transport){
                    if (transport && transport.responseText) {
                        try {
                            var response = eval('(' + transport.responseText + ')');
                            if (!response.error) {
                                this.openWindow(response.html);
                            }
                        } catch (e) {
                            response = {};
                        }
                    }
                }).bind(this)
            });
    },
    openWindow: function(content){

        var myWindow=window.open('','','scrollbars=1|1,width='+this.width+',height='+this.height);
        myWindow.document.write(content);
        myWindow.focus();

    }
};