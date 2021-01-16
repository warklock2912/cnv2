/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Customform
 */

var DynamicChildren = Class.create({
    templateSyntax : /(^|.|\r|\n)({{(\w+)}})/,
    newItemIndex: 0,
    maxSortOrder: 0,

    // Have to override values:
    childElementName: 'tr',
    childrenClassName: '',
    removeButtonClassName: '',
    container: null,

    //optional parameters
    sortingField: 'sort_order',

    initialize: function () {

    },

    add: function(data,load) {
        if (!data.id) {
            data.id = '_' + this.newItemIndex;
            this.newItemIndex++;
        }

        if (!data[this.sortingField]) {
            data[this.sortingField] = this.maxSortOrder + 10;
        }

        var currentSortOrder = parseInt(data[this.sortingField]);
        if (currentSortOrder > this.maxSortOrder) {
            this.maxSortOrder = currentSortOrder;
        }

        var template = new Template(this.getTemplateHTML(data), this.templateSyntax);
        var content = template.evaluate(data)
        Element.insert(this.container, content);
        this.bindRemoveButtons();
        this.afterAdd(data,load);


    },

    getTemplateHTML: function(data) {

    },

    afterAdd: function(data) {

    },

    bindRemoveButtons : function(){
        var buttons = $$('.' + this.removeButtonClassName);
        for(var i=0;i<buttons.length;i++){
            if(!$(buttons[i]).binded){
                $(buttons[i]).binded = true;
                Event.observe(buttons[i], 'click', this.remove.bind(this));
            }
        }
    },

    remove : function(event){
        var element = $(Event.findElement(event, this.childElementName));
        // !!! Button already
        // have table parent in safari
        // Safari workaround
        element.ancestors().each(function(parentItem){
            if (parentItem.hasClassName(this.childrenClassName)) {
                element = parentItem;
                throw $break;
            } else if (parentItem.hasClassName('box')) {
                throw $break;
            }
        });

        if(element){
            var elementFlags = element.getElementsByClassName('delete-flag');
            if(elementFlags[0]){
                elementFlags[0].value=1;
            }

            var inputs = element.querySelectorAll('input');
            var len = inputs.length;
            for(var i=1; i< len; i++){
                var item = inputs[i];
                if(item.type !== 'hidden'){
                    item.remove();
                }
            }

            element.hide();
            if(jQuery(this.container).closest('table').find('tbody tr:visible').length == 0){
                jQuery(this.container).closest('table').find('thead').hide();
            }
        }
    }
});
