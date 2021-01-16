/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Customform
 */
var $j = jQuery.noConflict();
Event.observe(window, 'load', function() {
    amasty_ajaxValidatableForm(editForm);
});

var fieldTypesData;

function saveAndContinueEdit(urlTemplate) {
    var template = new Template(urlTemplate, /(^|.|\r|\n)({{(\w+)}})/);
    var url = template.evaluate({tab_id:form_tabsJsTabs.activeTab.id});
    $('edit_form').setAttribute('action', url);

    var result = editForm.submit();
    if(!result){
        setTimeout(function(){
        jQuery('.validation-advice:visible').each(function(key,item){
            var parent = jQuery(item).closest('td').get(0);
            jQuery(parent).css({paddingBottom: "18px"});
        });
        },200);
    }

    setTimeout(function(){

        jQuery('.validation-advice:hidden').each(function(key,item){
            var parent = jQuery(item).closest('td').get(0);
            var visibleAdvice = jQuery(parent).find('.validation-advice:visible');
            if(visibleAdvice.length<1){
                jQuery(item).remove();
                jQuery(parent).css({paddingBottom: ""});
            }
        });
    },1100);
}

var DefaultInputFactory = Class.create({
    formFieldData: null,
    fieldData: null,

    initialize: function(formFieldData) {
        this.formFieldData = formFieldData;
    },

    createDefaultInput: function(fieldTypeId, tuner) {
        this.fieldData = fieldTypesData[fieldTypeId];
        var element = null;
        var calendar = false;
        switch (this.fieldData.input_type) {
            case 'statictext':
            case 'text':
                element = this.createText();
                break;
            case 'textarea':
                element = this.createTextArea();
                break;
            case 'date':
                element = this.createDate();
                calendar = true;
                break;
            case 'select':
                element = this.createSelect();
                break;
            case 'boolean':
                element = this.createBoolean();
                break;
        }
        tuner.placeDefaultInput(element);
        if(calendar){
            Calendar.setup({
                inputField: element,
                ifFormat: formatDate,
                showsTime: false,
                button: 'filter_date_from_trig',
                align: 'Bl',
                singleClick : true
            });
        }
        return element;
    },

    createText: function() {
        var element = this.createBaseInput();

        return element;
    },

    createTextArea: function() {

        var element = document.createElement('textarea');
       // element.setAttribute('type', 'textarea');
        element.setAttribute('name', this.getNameAttribute());

        var validationClass = this.fieldData.frontend_class;
        if(this.fieldData.required){
            validationClass += ' required-entry';
        }
        if (validationClass && validationClass.length) {
            element.addClassName(validationClass);
        }

        return element;
    },

    createSelect: function() {
        var element = document.createElement('select');
        element.setAttribute('name', this.getNameAttribute());

        var options = this.fieldData.options;

        for (var key in options) {
            if (options.hasOwnProperty(key)) {
                jQuery(element).append('<option value="'+key+'">'+options[key]+'</option>');
            }
        }
        var defaultValue = jQuery(element).find('option[value="'+this.formFieldData.default_value+'"]').get(0);
        if( defaultValue != undefined){
            jQuery(defaultValue).attr('selected','selected');
        }

        return element;
    },

    createDate: function() {
        return this.createBaseInput();
    },

    createBoolean: function() {
        var element = document.createElement('select');
        element.setAttribute('name', this.getNameAttribute());
        jQuery(element)
            .append('<option value="1">Yes</option>')
            .append('<option value="0">No</option>');
        var defaultValue = jQuery(element).find('option[value="'+this.formFieldData.default_value+'"]').get(0);
        if( defaultValue != undefined){
            jQuery(defaultValue).attr('selected','selected');
        }
        return element;
    },

    createBaseInput: function() {
        var element = document.createElement('input');
        element.setAttribute('type', 'text');
        element.setAttribute('name', this.getNameAttribute());

        var validationClass = this.fieldData.frontend_class;
        if(this.fieldData.required){
            validationClass += ' required-entry';
        }
        if (validationClass && validationClass.length) {
            element.addClassName(validationClass);
        }

        return element;
    },

    getNameAttribute: function() {
        return 'line[' + this.formFieldData.line_id + '][form_field][' + this.formFieldData.id + '][default_value]';
    }
});

var DefaultValueTuner = Class.create({
    formFieldData: null,

    typeSelect: null,
    rewriteDefaultSwitch: null,
    container: null,

    defaultInputFactory: null,

    initialize: function(formFieldData) {
        this.formFieldData = formFieldData;
        this.typeSelect = $$('#line_'+formFieldData.line_id+' #field-type-' + formFieldData.id);
        this.typeSelect = this.typeSelect[0];
        this.container = $$('#line_'+formFieldData.line_id+' #default-value-tuner-' + formFieldData.id);
        this.container = this.container[0];
        this.rewriteDefaultSwitch = $$('#line_'+formFieldData.line_id+' #rewrite-default-value-' + formFieldData.id);
        this.rewriteDefaultSwitch = this.rewriteDefaultSwitch[0];

        this.defaultInputFactory = new DefaultInputFactory(formFieldData);

        this.bindEvents();
        this.updateFieldType();
    },

    bindEvents: function() {
        var tuner = this;
        this.typeSelect.observe('change', function() {
            tuner.updateFieldType();
        });


    },

    updateFieldType: function() {
        var tuner = this;
        var fieldTypeId = this.typeSelect.options[this.typeSelect.selectedIndex].getAttribute('value');
        var element = this.defaultInputFactory.createDefaultInput(fieldTypeId, this);

        var listner = function() {
            tuner.updateDefaultInputVisibility(element);
        }
        this.rewriteDefaultSwitch.stopObserving('click');
        this.rewriteDefaultSwitch.observe('click', listner);
        this.updateDefaultInputVisibility(element);
    },

    placeDefaultInput: function(element) {
        this.container.innerHTML = '';

        if (element) {
            this.rewriteDefaultSwitch.parentNode.show();
            this.container.appendChild(element);
        } else {
            this.rewriteDefaultSwitch.parentNode.hide();
        }
    },

    updateDefaultInputVisibility: function(element) {
        if(!element){
            this.container.hide();
            return;
        }
        this.container.show();
        if (this.rewriteDefaultSwitch.checked) {
            if(this.formFieldData.field_id == this.defaultInputFactory.fieldData.id){
                if(element.type != 'select-one'){
                    element.value = this.formFieldData['default_value'];
                }else
                if(this.formFieldData['default_value'] != null){
                    element.value = this.formFieldData['default_value'];
                }

            }else{
                if(element.type != 'select-one'){
                    element.value = '';
                }
            }

            element.disabled = false;
        } else {
            if(this.defaultInputFactory.fieldData.default_value !== null){
                element.value = this.defaultInputFactory.fieldData.default_value;
                element.defaultValue = this.defaultInputFactory.fieldData.default_value;
            }
            element.disabled = true;

        }
    }
});

var DynamicFormFields = Class.create(DynamicChildren, {
    initialize: function() {
        this.childrenClassName = 'form-field-box';
        this.childElementName = 'tr';
    },

    getTemplateHTML: function(data) {
        var html = $('template-form-field').innerHTML;

        var search = 'option value="' + data.field_id + '"';
        var replace = search + ' selected';
        html = html.replace(search, replace);

        return html;
    },

    afterAdd: function(data) {
        $('rewrite-default-value-' + data.id).checked = (data.rewrite_default_value == 1);

        var defaultValueTuner = new DefaultValueTuner(data);
        jQuery(this.container).closest('table').find('thead').show();
    }
});

var DynamicLines = Class.create(DynamicChildren, {
    initialize: function() {
        this.childElementName = 'div';
        this.container = $('lines-container');
        this.childrenClassName = 'line-box';
        this.removeButtonClassName = 'delete-line';
    },

    getTemplateHTML: function(data) {
        return $('template-line').innerHTML;
    },

    afterAdd: function(data) {
        this.createDynamicFormFields(data);
    },

    createDynamicFormFields: function(data) {
        var dynamicFormFields = new DynamicFormFields();
        dynamicFormFields.lineId = data.id;
        dynamicFormFields.container = $('form_fields_container_' + data.id);
        dynamicFormFields.removeButtonClassName = 'form-field-remove-' + data.id;

        for(var id in data.form_fields) {
            if (data.form_fields.hasOwnProperty(id)) {
                dynamicFormFields.add(data.form_fields[id]);
            }
        }

        Event.observe($('add_form_field_button_' + data.id), 'click', function() {
            dynamicFormFields.add({
                line_id: data.id,
                form_id: data.form_id
            });
        });
    }
});

var FormPreviewer = Class.create({
    originalActionUrl: null,
    inProgress: false,

    preview: function()
    {
        if (this.inProgress) {
            return;
        }

        this.inProgress = true;
        var $form = $('edit_form');

        this._prepareForm($form);
        this._prepareFrame($('preview-title').value);

        $form.submit();

        this._restoreForm($form);
        this.inProgress = false;
    },

    _prepareFrame: function(title)
    {
        var content = '<iframe name="form_preview" width="100%" height="700"></iframe>';

        this.dialog = Dialog.info(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            width: 1020,
            height: 710,
            zIndex: 1000,
            recenterAuto: true
        });
    },

    _prepareForm: function($form)
    {
        var previewUrl = $('preview-url').value;
        this.originalActionUrl = $form.getAttribute('action');
        $form.setAttribute('action', previewUrl);

        $form.setAttribute('target', 'form_preview');
    },

    _restoreForm: function($form)
    {
        $form.setAttribute('action', this.originalActionUrl);
        this.originalActionUrl = null;

        $form.setAttribute('target', '_self');
    }
});

var formPreviewer = new FormPreviewer();