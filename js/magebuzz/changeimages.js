function isGiftcardType() {
    if ($('selectbanners').getValue() == 0)
    {
        $j('.form-list tr').slice(3, 5).show();
        $j('.form-list tr').slice(2, 3).hide();
    } else {
        $j('.form-list tr').slice(3, 5).hide();
        $j('.form-list tr').slice(2, 3).show();
    }
}

Event.observe(window, 'load', function () {
    if ($('selectbanners')) {
        Event.observe($('selectbanners'), 'change', isGiftcardType);
        isGiftcardType();
    }
});