/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
jQuery.noConflict();
;(function($) {
    $.fn.searchSuite = function(options) {
        var settings = $.extend({
            text: "",
            value: "",
            attributes: false,
            categories: false
        }, options);
        var data = {};
        var obj = null;
        var init = function() {
            obj = $(this);
            obj.unbind('click');
            obj.click(onClick);
            obj.blur(onBlur);
            setValue();
            if(typeof String.prototype.trim !== 'function') { // ie fix 
                String.prototype.trim = function() {
                    return this.replace(/^\s+|\s+$/g, ''); 
                };
            }
            if (settings.attributes) {
                initAttributes();
            }
            if (settings.categories) {
                initCategories();
            }
            $('.reset-button').change();
            prepareSubmit();
        };
        var setValue = function() {
            if (settings.value.length != 0) {
                obj.val($('<div/>').html(settings.value).text());
                $('.reset-button').change();
            }
            else {
                obj.val(settings.text);
                obj.addClass('searchsuite-default-text');
            }
        };
        var updateValue = function(blur) {
            var val = obj.val();
            if(blur){
                if(val.length == 0){
                     obj.val(settings.text);
                     obj.addClass('searchsuite-default-text');
                }
            } else {
                if (val.length == 0) {
                    if (obj.is(":focus")) {
                        obj.removeClass('searchsuite-default-text');
                    } else {
                        obj.val(settings.text);
                        obj.addClass('searchsuite-default-text');
                    }
                } else if (val == settings.text) {
                    obj.val("");
                    obj.removeClass('searchsuite-default-text');
                }
            }
        };
        var onClick = function() {
            updateValue();
        };
        var onBlur = function() {
            updateValue(true);
        };
        var changeAttrValue = function(text, val) {
            data.curAV.text(text);
            $('#searchsuite_attributes').val(val);
            var width = data.width - data.curAV.width();
            if(data.curCV){
                width -= data.curCV.width();
            }
            obj.width(width);
            $('.reset-button').change();
        };
        var changeCatValue = function(text, val) {
            data.curCV.text(text);
            $('#searchsuite_categories').val(val);
            var width = data.width - data.curCV.width();
            if(data.curAV){
                width -= data.curAV.width();
            }
            obj.width(width);
            $('.reset-button').change();
        };
        var _initOptions = function(optionsCollection,container,onChange,onToggle){
            var optionCount = 0;
            optionsCollection.each(function() {
                var option = $(this);
                var text = option.text();
                container.append($('<li/>').append($('<a/>',{href:"#",tabindex:"-1"}).text(text).click(function() {
                    onChange(text, option.val());
                    onToggle();
                    container.find('a').each(function(){
                        $(this).attr('tabindex','-1');
                    });
                    obj.keyup();
                    return false;
                })));
                optionCount++;
                if (option.is(':selected')) {
                    onChange(text, option.val());
                }
            });
            _autoHideOptions();
        };
        var _autoHideOptions = function(){
             $(document).click(function(event) {
                var item = event.target || event.toElement;
                if ($(item).closest(".searchsuite-select-attr").length != 1) {
                    toggleAttrCont('hide');
                }
                if ($(item).closest(".searchsuite-select-cat").length != 1) {
                    toggleCatCont('hide');
                }
            });
        };
        var initAttributes = function() {
            data.attrContainer = $('#searchsuite_attr_dropdown');
            data.curAV = $('#searchsuite_attr_current_value');
            data.width = obj.width() + data.curAV.width();
            var options = $('#searchsuite_attributes option');
            _initOptions($('#searchsuite_attributes option'),data.attrContainer,changeAttrValue,toggleAttrCont);
            var sc = $('#searchsuite_attr_change');
            sc.click(function() {
                toggleAttrCont();
                var i = 4;
                var selected = $('#searchsuite_attributes option:selected');
                data.attrContainer.find('a').each(function(){
                    var item = $(this);
                    if(selected.length == 1){
                        if(i-4 == selected.index()){
                            item.trigger('focus');
                        }
                    } else if(i == 4){
                        item.trigger('focus');
                    }
                    item.attr('tabindex',i++);
                });
                return false;
            });
            $('.searchsuite-submit').attr('tabindex',options.length + 4);
        };
        var initCategories = function() {
            data.catContainer = $('#searchsuite_cat_dropdown');
            data.curCV = $('#searchsuite_cat_current_value');
            data.width = obj.width() + data.curCV.width();
            
            var options = $('#searchsuite_categories option');
            _initOptions(options,data.catContainer,changeCatValue,toggleCatCont);
            
            var sc = $('#searchsuite_cat_change');
            sc.click(function() {
                toggleCatCont();
                var i = 4;
                var selected = $('#searchsuite_categories option:selected');
                data.catContainer.find('a').each(function(){
                    var item = $(this);
                    if(selected.length == 1){
                        if(i-4 == selected.index()){
                            item.trigger('focus');
                        }
                    } else if(i == 4){
                        item.trigger('focus');
                    }
                    item.attr('tabindex',i++);
                });
                return false;
            });
            $('.searchsuite-submit').attr('tabindex',options.length + 4);
        };
        var prepareSubmit = function() {
            obj.closest('form').submit(function() {
                if (obj.val().length <= 1 || obj.val() == settings.text) {
                    return false;
                }
                return true;
            });
        };
        var _toggleCont = function(container,attr){
            if(attr == 'show'){
                container.show();
            } else if(attr == 'hide'){
                container.hide();
            } else {
                container.toggle();
            }
        };
        var toggleAttrCont = function(attr) {
            _toggleCont(data.attrContainer,attr);
        };
        var toggleCatCont = function(attr) {
            _toggleCont(data.catContainer,attr);
        };
        return this.each(init);
    };
}(jQuery));