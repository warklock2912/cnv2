/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Customform
 */
var $j = jQuery.noConflict();
function saveAndContinueEdit(urlTemplate) {
    var template = new Template(urlTemplate, /(^|.|\r|\n)({{(\w+)}})/);
    var url = template.evaluate({tab_id: form_tabsJsTabs.activeTab.id});
    $('edit_form').setAttribute('action', url);

    editForm.submit();
}
var currentDefaultFiled = null;
function chooseDefaultField(inputType) {
    var defaultText = $('default_value_text').parentNode.parentNode.hide();
    var defaultTextArea = $('default_value_textarea').parentNode.parentNode.hide();
    var defaultTextDate = $('default_value_date').parentNode.parentNode.hide();
    var defaultTextYesNo = $('default_value_yesno').parentNode.parentNode.hide();
    switch (inputType) {
        case 'statictext':
        case 'text':
            defaultText.show();
            currentDefaultFiled = defaultText;
            break;
        case 'textarea':
            defaultTextArea.show();
            currentDefaultFiled = defaultTextArea;
            break;
        case 'boolean':
            defaultTextYesNo.show();
            currentDefaultFiled = null;
            break;
        case 'date':
            defaultTextDate.show();
            currentDefaultFiled = null;
            break;
    }
}

function updateVisibilityMaxLength(inputType) {
    var maxLengthFiled = $('max_length').parentNode.parentNode.hide();
    switch (inputType) {
        case 'text':
        case 'textarea':
            maxLengthFiled.show();
            break;
    }
}

function updateVIsibilitiRequired(inputType){
    var $required = jQuery('#required').parents('tr').show();
    switch (inputType) {
        case 'statictext':
            $required.hide();
            break;
    }
}

function updateVisibility(load) {
    var $inputType = $('input_type');
    var inputType = $inputType.options[$inputType.selectedIndex].value;
    // var $inputValidation = $('frontend_class').parentNode.parentNode;
    if(inputType == 'multiselect'){
        dynamicOptions.isMultiselectOptions = true;
    } else {
        dynamicOptions.isMultiselectOptions = false;
    }
    var $optionsPanel = $('matage-options-panel');

    chooseDefaultField(inputType);
    updateVisibilityMaxLength(inputType);
    updateVIsibilitiRequired(inputType);
    updateDefaultClass(jQuery('#max_length').val());
    /*  if (amcustomform_isValidatable(inputType)) {
     $inputValidation.show();
     } else {
     $inputValidation.hide();
     }*/

    if (amcustomform_isOptionsAllowed(inputType)) {
        var options = jQuery($optionsPanel).find('.option-row');
        if(options.length){
            if(!load){
                options.remove();
            }

        }
        $optionsPanel.show();
    } else {
        $optionsPanel.hide();
    }
}

function updateDefaultClass(value) {
    var $input = jQuery(currentDefaultFiled).find('input');
    var $textarea = jQuery(currentDefaultFiled).find('textarea');
    var $defaultField = ($input.length) ? $input : $textarea;
    if ($defaultField.length != 1) {
        return;
    }
    $defaultField.removeClass('validate-length');
    $defaultField.removeClass(function(index, css){
        return (css.match (/maximum-length-[0-9]+/g) || []).join(' ');
    });
    if (value > 0) {
        $defaultField.addClass('validate-length');
        $defaultField.addClass('maximum-length-' + value);
    }
}

Event.observe(window, 'load', function () {
    amasty_ajaxValidatableForm(editForm);

    updateVisibility(true);

    $('input_type').observe('change', function () {
        updateVisibility();
    });
    jQuery('#max_length').focusout(function () {
        updateDefaultClass(this.value);
    })

    jQuery('#max_length').keypress(function (e) {
        var code = e.keyCode || e.which;
        if ( (47 < code &&  code < 58) || code == 8) {
            return true;
        }
        return false;
    })
});

