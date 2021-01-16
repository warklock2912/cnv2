/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Customform
 */

function amcustomform_isMultipleOptions(inputType) {
    return (inputType == 'multiselect' || inputType == 'checkbox');
}

function amcustomform_isOptionsAllowed(inputType) {
    return (inputType == 'select' || inputType == 'multiselect' || inputType == 'checkbox' || inputType == 'radio');
}

function amcustomform_isSingleDefaultValue(inputType) {
    return (inputType == 'text' || inputType == 'textarea' || inputType == 'date' || inputType == 'boolean' || inputType == 'statictext');
}

function amcustomform_isValidatable(inputType) {
    return inputType == 'text';
}

function amasty_ajaxValidatableForm(form) {
    form.validationUrl = $('validation-url').value;
    form._processValidationResult = function(transport) {
        var response = transport.responseText.evalJSON();
        if (response.error){
            if (response.attribute && $(response.attribute)) {
                $(response.attribute).setHasError(true, editForm);
                Validation.ajaxError($(response.attribute), response.message);
                if (!Prototype.Browser.IE){
                    $(response.attribute).focus();
                }
            }
            else if ($('messages')) {
                $('messages').innerHTML = '<ul class="messages"><li class="error-msg"><ul><li>' + response.message + '</li></ul></li></ul>';
            }
        }
        else{
            form._submit();
        }
    };
}