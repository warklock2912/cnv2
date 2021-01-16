/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
jQuery.noConflict();
;(function($){
    $.fn.searchSuiteAutocomplete = function(options){
        var settings = $.extend({
            url: "",
            delay: "",
            minlength: 2,
            cache: true,
            animation: 'default'
        }, options);
        var data = {};
        var obj = null;
        var init = function(){
            data.cache = [];
            data.container = $('#search_autocomplete');
            chechAttr();
            obj = $(this);
            obj.unbind('keyup');
            obj.unbind('blur');
            initResetButton();
            obj.keyup(onKeyup);
            obj.focus(onFocus);
            obj.searchSuiteAutocompleteAnimationProvider('init',settings.animation);
            $(document).click(function(event) {
                var item = event.target || event.toElement;
                if (item.id != 'search' && $(item).closest('#search_autocomplete').length != 1) {
                    togglePopup('hide');
                }
            });
        };
        var onFocus = function(e){
            if(obj.val() == data.currentQuery){
                showResult(cache(_getKey(data.currentQuery)));
                e.stopPropagation();
            }
        };
        var onKeyup = function(e){
            if(e.keyCode != 13){
                var value = obj.val().trim();
                if(value.length >= settings.minlength && validate(value)){
                    search(obj.val());
                } else {
                    stop();
                }
                updateResetButton();
            } else {
                stop();
            }
        };
        var search = function(value){
            if(!value){
                if(!data.value){
                    return ;
                }
                if(data.currentQuery == value){
                    return ;
                }
                value = data.value;
            }
            stop();
            var key = _getKey(value);
            var params = {q: value};
            if(data.category){
                params.cat = data.category.val();
            }
            if(data.attribute){
                params.a = data.attribute.val();
            }
            if(cache(key) != null){
                showResult(cache(key));
            } else {
                data.currentQuery = value;
                data.timer = setTimeout(function(){
                    obj.searchSuiteAutocompleteAnimationProvider('start');
                    data.hAjax = $.ajax({
                        url: settings.url,
                        type: 'get',
                        data: params,
                        dataType: 'json',
                        success: function(response){
                            cache(key,response);
                            showResult(response);
                        },
                        complete: function(){
                            if(!cache(key)){
                                cache(key,null);
                            }
                        },
                        error:function(r,t,e){}
                    }).always(function(){
                        stop();
                    });
                },settings.delay);
            }
        };
        var stop = function(){
            if(data.timer){
                clearTimeout(data.timer);
            }
            if(data.hAjax != null){
                data.hAjax.abort();
            }
            obj.searchSuiteAutocompleteAnimationProvider('stop');
        };
        var validate = function(query){
            
            data.valid = false;
            if(obj.hasClass('searchsuite-default-text')){
                return data.valid;
            }
            var words = query.split(' ');
            for(var word in words){
                if(word.length >= settings.minlength){
                    data.valid = true;
                    break;
                }
            }
            return data.valid;
        };
        var cache = function(key, value){
            if(!settings.cache){
                return null;
            }
            if(value != null){
                data.cache[key] = value;
            } else {
                return (data.cache[key] != undefined)?data.cache[key]:null;
            }
        };
        var chechAttr = function(){
            var cat = $('#searchsuite_categories');
            if(cat.length > 0){
                data.category = cat;
            }
            var attr = $('#searchsuite_attributes');
            if(attr.length > 0){
                data.attribute = attr;
            }
        };
        var showResult = function(inputData){
            var json;
            if(typeof(inputData) == 'string'){
                json = cache(inputData);
            } else {
                json = inputData;
            }
            if(json){
                if(json.content){
                    data.container.html(json.content);
                    togglePopup('show');
                } else {
                    togglePopup('hide');
                    return;
                }
                if(json.callback){
                    (new Function(json.callback))();
                }
                var form = $('.searchsuite-form-search');
                var w = form.width() - $('#searchautocomplete-search-1').width();
                data.container.attr('style', 'left:' + w + 'px !important');
            }
        };
        var togglePopup = function(attr){
            if(attr == 'show'){
                data.container.show(0);
            } else if(attr == 'hide'){
                data.container.hide(0);
            } else {
                data.container.toggle();
            }
        };
        var initResetButton = function(){
            data.resetButton = $('<input class="reset-button" value="x" type="button" />');
            data.resetButton.click(function(){
                obj.val('');
                updateResetButton();
                obj.trigger('click');
            }).change(function(){
                updateResetButton();
            });
            data.resetButton.insertBefore(obj)
        };
        var updateResetButton = function(){
            if(obj.val().length >= settings.minlength && !obj.hasClass('searchsuite-default-text')){
                data.resetButton.show();
            } else {
                data.resetButton.hide();
                togglePopup('hide');
            }
            data.resetButton.css({left:obj.width()});
        };
		var _getKey = function(value){
			var key = value.toLowerCase();
            if(data.category){
                key += '_' + data.category.val();
            }
            if(data.attribute){
                key += '_' + data.attribute.val();
            }
			return key;
		};
        return this.each(init);
    };
    $.fn.searchSuiteAutocompleteSuggest = function(){
        
        var init = function(){
            var obj = $(this);
            obj.click(function(){
                $('#search').val(obj.text()).keyup();
            });
        };
        return this.each(init);
    };
    
    var autocompleteMethods = {
        init:function(name){
            if(name){
                this.name = name;
            } else {
                this.name = 'default';
            }
        },
        start:function(){
            if(this.name == 'default'){
                this.oldPosition = this.obj.css('background-position-x');
                this.obj.addClass('spinner');
                var xpos = this.obj.outerWidth()-25;
                this.obj.css({'background-position-x':xpos});
            } else if(this.name == 'nprogress'){
                NProgress.start();
                NProgress.set(0.1);
                this.timer = setInterval(function(){
                    NProgress.inc();
                },400);
            }
        },
        stop:function(){
            if(this.name == 'default'){
                this.obj.removeClass('spinner');
                this.obj.css({'background-position-x':this.oldPosition});
            } else if(this.name == 'nprogress'){
                clearTimeout(this.timer);
                NProgress.done();
            }
        }
    };
    $.fn.searchSuiteAutocompleteAnimationProvider = function(method){
        this.obj = $(this);
        if ( autocompleteMethods[method] ) {
            return autocompleteMethods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } 
    };
}(jQuery));