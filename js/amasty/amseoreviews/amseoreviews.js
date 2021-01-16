Event.observe(document, 'dom:loaded', hide_reviews_tab);
Event.observe(document, 'dom:loaded', add_anchor_to_reviews);
Event.observe(document, 'dom:loaded', add_anchor_link_to_reviews);
var amseoreviews_scroll_element = null;

function add_anchor_link_to_reviews() {
    var list = $$('.rating-links a:first');
    [].forEach.call(list, function (li) {
        li.observe('click', function (event) {
            if (amseoreviews_scroll_element) {
                $(amseoreviews_scroll_element).scrollTo();
            }
        });
    });
}

function add_anchor_to_reviews() {
    var list = $$('.product-view > .box-reviews#customer-reviews');
    [].forEach.call(list, function (li) {
        li.insert({
            top: "<a name='customer-reviews'></a>"//new Element('a', {name: 'customer-reviews'})
        });
        amseoreviews_scroll_element = li;
    });
}

function hide_reviews_tab() {
    var list = $$("dt.tab > span");
    [].forEach.call(list, function (li) {
        if (li.textContent.indexOf("Reviews") > -1) {
            li.style.display = "none";
        }
    });
}

