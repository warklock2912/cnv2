var htmlSitemap = function () {
	this.currentTextValue = '';
	this.init = function () {
		this.initSearch();
	};

	this.initSearch = function () {
		var instance = this;

		$$('.htmlsitemap-search-input').each(function (e) {
			e.observe('keyup', function (el) {
				var val = el.target.value;
				if (instance.currentTextValue != val.toLocaleLowerCase()) {
					instance.contentSearch(val);
				}
			});

			$$('.am-sitemap-wrap a').each(function (e) {
				e.defaultTextContent = e.innerText ? e.innerHTML : e.textContent;
			});
		});
	};

	this.contentSearch = function (text) {
		var instance = this;
		text = text.replace(/^\s+/, '').replace(/\s+$/, '');

		instance.currentTextValue = text.toLowerCase();

		$$('.am-always-visible').each(function (e) {
			e.removeClassName('am-always-visible');
		});

		$$('.am-sitemap-wrap a').each(function (e, a) {
			e.textContent = e.defaultTextContent;
			if (instance.currentTextValue.replace(/\s+/, '') != '') {
				if (e.defaultTextContent.toLowerCase().indexOf(instance.currentTextValue) == -1) {
				/*	console.log(e.textContent, instance.currentTextValue);*/
					if (! e.up().hasClassName('am-always-visible')) {
						e.up().hide();
					}
				} else {
					e.up().show();
					e.highlight(instance.currentTextValue, 'text-highlight');

					var leaf = e.up('li.tree-leaf');
					while (leaf) {
						leaf.show();
						leaf.addClassName('am-always-visible');
						leaf = leaf.up('li.tree-leaf');
					}

					instance.showAllLeafs(e.up('li.tree-leaf'));
				}
			} else {
				e.up().show();
			}
		});
	};

	this.showAllLeafs = function (e) {
		var instance = this;
		if (! e) {
			return false;
		}

		var leafs = e.select('li.tree-leaf');
		leafs.each(function (el) {
			el.show();
			el.addClassName('am-always-visible');
			instance.showAllLeafs(el);
		})
	}
};

document.observe("dom:loaded", function () {
	var sitemapInstance = new htmlSitemap();
	sitemapInstance.init();
});