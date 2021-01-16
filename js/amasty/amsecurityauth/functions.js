
function verifyCode(url, userId, secret, code) {
    new Ajax.Request(url, {
        parameters: {
            user_id: userId,
            secret: secret,
            code: code
        },
        onSuccess: function (response) {
            if (response.responseJSON.result == true) {
                $('is_configured').value = 1;
            }
            $('code-verification-message').innerHTML = '<span style="color: #' + response.responseJSON.color + ';font-weight: bold">' + response.responseJSON.message + '</span>' + response.responseJSON.additional;
        }
    });
}

document.observe("dom:loaded", function() {
    Validation.add('validate-is-configured','Please check verification code',function(the_field_value) {
        if ($('amsecurityauth_active').checked == false
            || $('is_configured').value == 1
        ) {
            return true;
        }
        return false;
    });

    $('amsecurityauth_active').observe('change', function() {
        if ($('amsecurityauth_active').checked) {
            $("amsecurityauth_configuration").removeClassName('no-display');
            $("amsecurityauth_configuration").show();
        } else {
            $("amsecurityauth_configuration").hide();
        }
    });

});
