(function($){
    $.fn.autocompleteCustomizer = function(options){
        var settings = $.extend({
            field: null,
            menuContainer:null,
            font: '{"Arial":"Arial, serif"}',
            fontsize: '{"12pt":"12pt"}',
            translate: '{}'
        }, options);
        var obj = null;
        var data = {};
        var names = {color:'color',
                bgcolor:'background-color',
                font:'font-family',
                fontsize:'font-size',
                italic:'font-style',
                bold:'font-weight'};
            
        var tagFilter = ['button','img'];
        var init = function(){
            obj = $(this);
            if(settings.field.val().length > 0) {
                data.value = JSON.parse(settings.field.val());
            } else {
                data.value = {};
            }
            data.currentClass = '';
            data.currentStyle = {};
            data.translate = JSON.parse(settings.translate);
            initMenu();
            initClick();
            initHover();
            prepare();
        };
        var initMenu = function(){
            data.menu = {};
            data.menu.color = $('<input />',{type:"text",'class':'ssa-color pick-a-color form-control',id:'ssa_color'});
            data.menu.bgcolor = $('<input />',{type:"text",'class':'ssa-bgcolor',id:'ssa_bgcolor'});
            data.menu.font = $('<select />',{'class':'ssa-font',id:'ssa_font'});
            data.menu.fontsize = $('<select />',{'class':'ssa-fontsize',id:'ssa_fontsize'}); 
            data.menu.italic = $('<input />',{type:"checkbox",'class':'ssa-italic',id:'ssa_italic',value:'italic'});
            data.menu.bold = $('<input />',{type:"checkbox",'class':'ssa-bold',id:'ssa_bold',value:'bold'});
            data.applyButton= $('<button />',{'class':'ssa-btn-apply'}).text(data.translate['apply']).prop('disabled',true);
            data.cancelButton = $('<button />',{'class':'ssa-btn-cancel'}).text(data.translate['cancel']);
            data.clearButton = $('<button />',{'class':'ssa-btn-clear'}).text(data.translate['clear']);
            var font = JSON.parse(settings.font);
            data.menu.font.append($('<option/>'));
            for(var f in font){
                data.menu.font.append($('<option/>',{value:f}).text(f));
            }
            var fontsize = JSON.parse(settings.fontsize);
            data.menu.fontsize.append($('<option/>'));
            for(var f in fontsize){
                data.menu.fontsize.append($('<option/>',{value:f}).text(f));
            }
            for(var key in data.menu){
                var label = $('<div />',{'class':'ssa-label ssa-label-'+key}).text(data.translate[key]);
                settings.menuContainer.append($('<div />',{'class':'ssa-row'}).append(label).append(data.menu[key]));
            }
            settings.menuContainer.append($('<div />',{'class':'ssa-row ssa-row-buttons'}).append(data.applyButton).append(data.cancelButton).append(data.clearButton));
            settings.menuContainer.append($('<div/>',{'class':'ssa-clear'}));
            data.menu.color.pickAColor({showSpectrum:true,showSavedColors:true,allowBlank:true,fadeMenuToggle:false});
            data.menu.bgcolor.pickAColor({showSpectrum:true,showSavedColors:true,allowBlank:true,fadeMenuToggle:false});
            data.cancelButton.click(function(){
                flush();
                return false;
            });
            data.clearButton.click(function(){
                flush();
                var css = {};
                for(var item in data.menu){
                    css[names[item]] = 'inherit';
                }
                for(var className in data.value){
                    obj.find('.' + className).css(css);
                }
                data.value = {};
                save(data.value);
                return false;
            });
        };
        var initClick = function(){
            obj.find('a,button,input').each(function(){
                $(this).attr('href','#').attr('onclick','');
            });
            obj.find(tagFilter.join(',')).each(function(){
                $(this).click(function(event){
                    event.stopPropagation();
                }).mouseover(function(event){
                    event.stopPropagation();
                }).addClass('ssa-notallowed');
            });
            obj.unbind('click');
            obj.click(function(event){
                var item = event.target || event.toElement;
                item = $(item);
                var cls = _getClass(item.attr('class'));
                flush();
                if(cls){
                    item.addClass('ssa-current');
                    edit(cls);
                    event.stopPropagation();
                }
                return false;
            });
        };
        var initHover = function(){
            obj.append(data.hint = $('<div/>',{id:'ssa_hint'}).append(data.hintMessage = $('<div/>',{id:'ssa_hint_msg'})));
            obj.mouseover(function(event){
                var item = event.target || event.toElement;
                item = $(item);
                var cls = _getClass(item.attr('class'));
                if(cls){
                    data.hintMessage.text((data.translate[cls]!=null)?data.translate[cls]:cls);
                    var p = item.position();
                    data.hint.css({top:p.top-30,left:p.left + item.width() - 30});
                    data.hint.fadeIn(100);
                    obj.find('.ssa-hover').removeClass('ssa-hover');
                    item.addClass('ssa-hover');
                }
              });
            obj.mouseleave(function(event){
                data.hint.stop();
                data.hint.fadeOut(100);
                obj.find('.ssa-hover').removeClass('ssa-hover');
            });
        };
        var _getClass = function(className){
            var cls = null;
            if(className && className.length > 0){
                var c = className.split(' ');
                for(var i = 0;i<c.length;i++){
                    if(c[i].length > 0 && c[i] != 'ssa-hover' && c[i] != 'ssa-current'){
                        cls = c[i];
                    }
                }
            }
            return cls;
        };
        var flush = function(){
            data.applyButton.unbind('click');
            data.applyButton.prop('disabled', true);
            if(data.currentClass){
                obj.find('.'+data.currentClass).css(data.currentStyle);
            }
            for(var key in data.menu){
                data.menu[key].unbind('change');
                if(key == 'italic' || key == 'bold'){
                    data.menu[key].prop('checked',false);
                }
            }
            obj.find('.ssa-current').removeClass('ssa-current');
            data.currentClass = null;
            data.currentStyle = {};
            data.menu.color.val('');
            data.menu.bgcolor.val('');
            data.menu.font.val('');
            data.menu.fontsize.val('');
        };
        var edit = function(className){
            data.currentClass = className;
            update(className);
            data.applyButton.click(function(){
                data.value[className] = {};
                for(var key in data.menu){
                    if(data.menu[key].val().length > 0){
                        if(key == 'italic' || key == 'bold'){
                            if(data.menu[key].is(':checked')){
                                data.value[className][key] = data.menu[key].val();
                            }
                        } else {
                            data.value[className][key] = data.menu[key].val();
                        }
                    }
                }
                data.currentStyle = {};
                save(data.value);
                
                if(data.applyComment == null){
                    settings.menuContainer.append(data.applyComment = $('<div />',{'class':'ssa-btn-apply-cooment'}).text(data.translate['apply-comment']));
                }
                return false;
            });
            data.applyButton.prop('disabled', false);
        };
        var save = function(params){
            settings.field.val(JSON.stringify(params));
        };
        var update = function(className){
            var node = obj.find('.' + className);
            data.menu.color.change(function(){
                _updateCss(node,names.color,'#' + $(this).val());
            });
            data.menu.bgcolor.change(function(){
                _updateCss(node,names.bgcolor,'#' + $(this).val());
            });
            data.menu.font.change(function(){
                _updateCss(node,names.font,$(this).val());
            });
            data.menu.fontsize.change(function(){
                _updateCss(node,names.fontsize,$(this).val());
            });
            data.menu.italic.change(function(){
                if($(this).is(':checked')){
                    _updateCss(node,names.italic,$(this).val());
                } else {
                    _updateCss(node,names.italic);
                }
            });
            data.menu.bold.change(function(){
                if($(this).is(':checked')){
                    _updateCss(node,names.bold,$(this).val());
                } else {
                    _updateCss(node,names.bold);
                }
            });
            for(var key in data.value[className]){
                if(key == 'color' || key == 'bgcolor'){
                    data.menu[key].val(data.value[className][key]).trigger('blur');
                    if(data.menu[key].is(":visible")){
                        data.menu[key].trigger('blur');
                    }
                } else if(key == 'italic' || key == 'bold'){
                    data.menu[key].prop('checked', true);
                } else {
                    data.menu[key].val(data.value[className][key]);
                }
            };
        };
        var _updateCss = function(node,css,value){
            var style = {};
            if(!value || value.length < 1){
                value = 'inherit';
            }
            if(!data.currentStyle[css]){
                data.currentStyle[css] = node.css(css);
            }
            style[css] = value;
            node.css(style);
        };
        var prepare = function(){
            for(var i in data.value){
                var style = {};
                for(var j in data.value[i]){
                    style[names[j]] = data.value[i][j];
                    if(j == 'color' || j == 'bgcolor'){
                        style[names[j]] = '#'+style[names[j]];
                    }
                }
                obj.find('.'+i).css(style);
            }
        };
    return this.each(init);
    };
}(jQuery));