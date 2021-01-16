CityUpdater = Class.create();
CityUpdater.prototype = {
    initialize: function(countryEl, regionEl, cityTextEl, citySelectEl, cities) {
        this.regionEl = $(regionEl);
        this.cityTextEl = $(cityTextEl);
        this.citySelectEl = $(citySelectEl);
        this.cities = cities;
        this.countryEl = $(countryEl);
        //if (this.citySelectEl.options.length<=1) {
        this.update();
        //	}

        this.regionEl.changeUpdater = this.update.bind(this);

        Event.observe(this.regionEl, 'change', this.update.bind(this));
        Event.observe(this.countryEl, 'change', this.update.bind(this));
        Event.observe(this.citySelectEl, 'change', this.updateCity.bind(this));
    },

    update: function() {
        if (this.cities[this.regionEl.value]) {
            var i, option, city, def;
            def = this.citySelectEl.getAttribute('defaultValue');
            if (this.cityTextEl) {
                if (!def) {
                    def = this.cityTextEl.value.toLowerCase();
                }
                ////need to comment this to avoid issue when saving address without touching city field
                //this.cityTextEl.value = '';
            }

            this.citySelectEl.options.length = 1;
            for (cityId in this.cities[this.regionEl.value]) {
                city = this.cities[this.regionEl.value][cityId];

                option = document.createElement('OPTION');
                option.value = city.code;
                option.text = city.name.stripTags();
                option.title = city.name;

                if (this.citySelectEl.options.add) {
                    this.citySelectEl.options.add(option);
                } else {
                    this.citySelectEl.appendChild(option);
                }

                if (cityId==def || (city.name && city.name==def) ||
                    (city.name && city.code.toLowerCase()==def)
                    ) {
                    this.citySelectEl.value = city.code;
                }
            }

            if (this.cityTextEl) {
                this.cityTextEl.style.display = 'none';
            }
            this.citySelectEl.style.display = '';
        }
        else {
            this.citySelectEl.options.length = 1;
            if (this.cityTextEl) {
                this.cityTextEl.style.display = '';
            }
            this.citySelectEl.style.display = 'none';
            Validation.reset(this.citySelectEl);
        }
    },

    updateCity: function() {
        var sIndex = this.citySelectEl.selectedIndex;
//        this.cityTextEl.value = this.citySelectEl.options[sIndex].value;
        this.cityTextEl.value = this.citySelectEl.options[sIndex].label;
    }
}

SubdistrictUpdater = Class.create();
SubdistrictUpdater.prototype = {
    initialize: function(countryEl, regionEl, citySelectEl, subdistrictTextEl, subdistrictEl, zipEl, subdistricts) {
        this.countryEl = $(countryEl);
        this.regionEl = $(regionEl);
        this.citySelectEl = $(citySelectEl);
        this.subdistrictTextEl = $(subdistrictTextEl);
        this.subdistrictEl = $(subdistrictEl);
        this.zipEl = $(zipEl);
        this.subdistricts = subdistricts;
        //if (this.citySelectEl.options.length<=1) {
        this.update();
        //	}

        this.citySelectEl.changeUpdater = this.update.bind(this);
        Event.observe(this.regionEl, 'change', this.update.bind(this));
        Event.observe(this.countryEl, 'change', this.update.bind(this));
        Event.observe(this.citySelectEl, 'change', this.update.bind(this));
        Event.observe(this.subdistrictEl, 'change', this.updateSubdistrict.bind(this));
    },

    update: function() {
        if (this.subdistricts[this.citySelectEl.value]) {
            var i, option, subdistrict, def;
            def = this.subdistrictEl.getAttribute('defaultValue');
            if (this.subdistrictTextEl) {
                if (!def) {
                    def = this.subdistrictTextEl.value.toLowerCase();
                }
            }

            this.subdistrictEl.options.length = 1;
            for (subdistrictId in this.subdistricts[this.citySelectEl.value]) {
                subdistrict = this.subdistricts[this.citySelectEl.value][subdistrictId];

                option = document.createElement('OPTION');
                option.value = subdistrict.code;
                option.text = subdistrict.name.stripTags();
                option.title = subdistrict.name;

                if (this.subdistrictEl.options.add) {
                    this.subdistrictEl.options.add(option);
                } else {
                    this.subdistrictEl.appendChild(option);
                }

                if (subdistrictId==def || (subdistrict.name && subdistrict.name==def) ||
                    (subdistrict.name && subdistrict.code.toLowerCase()==def)
                    ) {
                    this.subdistrictEl.value = subdistrict.code;
                }
            }

            if (this.subdistrictTextEl) {
                this.subdistrictTextEl.style.display = 'none';
            }
            this.subdistrictEl.style.display = '';
        }
        else {
            this.subdistrictEl.options.length = 1;
            if (this.subdistrictTextEl) {
                this.subdistrictTextEl.style.display = '';
            }
            this.subdistrictEl.style.display = 'none';
            Validation.reset(this.subdistrictEl);
        }
    },

    updateSubdistrict: function() {
        var sIndex = this.subdistrictEl.selectedIndex;
        this.subdistrictTextEl.value = this.subdistrictEl.options[sIndex].value;

        if (sIndex) {
            var selectedSubdistrict = this.subdistricts[this.citySelectEl.value][this.subdistrictTextEl.value];
            var zipcode = '';
            if (selectedSubdistrict != undefined) {
                zipcode = selectedSubdistrict.zipcode;
            }

            if (zipcode && zipcode != 'null') {
                this.updateZipcode(zipcode);
                //this.zipEl.value = zipcode;
            }
            else {
                this.updateZipcode('');
                //this.zipEl.value = '';
            }
        }
        else {
            //this.zipEl.value = '';
            this.updateZipcode('');
            this.subdistrictTextEl.value = '';
        }

    },

    /*
     * this function make sure that we can update zipcode when creating order in backend
     * prefer to find better solution later
     */
    updateZipcode: function(zipcode) {
        $('postcode').value = zipcode;
    }
}

RegionUpdater = Class.create();
RegionUpdater.prototype = {
    initialize: function (countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl)
    {
        this.countryEl = $(countryEl);
        this.regionTextEl = $(regionTextEl);
        this.regionSelectEl = $(regionSelectEl);
        this.zipEl = $(zipEl);
        this.config = regions['config'];
        delete regions.config;
        this.regions = regions;

        this.disableAction = (typeof disableAction=='undefined') ? 'hide' : disableAction;
        this.zipOptions = (typeof zipOptions=='undefined') ? false : zipOptions;

        if (this.regionSelectEl.options.length<=1) {
            this.update();
        }

        Event.observe(this.countryEl, 'change', this.update.bind(this));
    },

    _checkRegionRequired: function()
    {
        var label, wildCard;
        var elements = [this.regionTextEl, this.regionSelectEl];
        var that = this;
        if (typeof this.config == 'undefined') {
            return;
        }
        var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

        elements.each(function(currentElement) {
            Validation.reset(currentElement);
            label = $$('label[for="' + currentElement.id + '"]')[0];
            if (label) {
                wildCard = label.down('em') || label.down('span.required');
                if (!that.config.show_all_regions) {
                    if (regionRequired) {
                        label.up().show();
                    } else {
                        label.up().hide();
                    }
                }
            }

            if (label && wildCard) {
                if (!regionRequired) {
                    wildCard.hide();
                    if (label.hasClassName('required')) {
//                        label.removeClassName('required');
                    }
                } else if (regionRequired) {
                    wildCard.show();
                    if (!label.hasClassName('required')) {
                        label.addClassName('required');
                    }
                }
            }

            if (!regionRequired) {
                if (currentElement.hasClassName('required-entry')) {
//                    currentElement.removeClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    currentElement.hasClassName('validate-select')) {
//                    currentElement.removeClassName('validate-select');
                }
            } else {
                if (!currentElement.hasClassName('required-entry')) {
                    currentElement.addClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    !currentElement.hasClassName('validate-select')) {
                    currentElement.addClassName('validate-select');
                }
            }
        });
    },

    update: function()
    {
        if (this.regions[this.countryEl.value]) {
            var i, option, region, def;

            def = this.regionSelectEl.getAttribute('defaultValue');
            if (this.regionTextEl) {
                if (!def) {
                    def = this.regionTextEl.value.toLowerCase();
                }
                this.regionTextEl.value = '';
            }

            this.regionSelectEl.options.length = 1;
            for (regionId in this.regions[this.countryEl.value]) {
                region = this.regions[this.countryEl.value][regionId];

                option = document.createElement('OPTION');
                option.value = regionId;
                option.text = region.name.stripTags();
                option.title = region.name;

                if (this.regionSelectEl.options.add) {
                    this.regionSelectEl.options.add(option);
                } else {
                    this.regionSelectEl.appendChild(option);
                }

                if (regionId==def || (region.name && region.name.toLowerCase()==def) ||
                    (region.name && region.code.toLowerCase()==def)
                    ) {
                    this.regionSelectEl.value = regionId;
                }
            }

            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = 'none';
                }

                this.regionSelectEl.style.display = '';
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = true;
                }
                this.regionSelectEl.disabled = false;
            }
            this.setMarkDisplay(this.regionSelectEl, true);
        } else {
            this.regionSelectEl.options.length = 1;
            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = '';
                }
                this.regionSelectEl.style.display = 'none';
                Validation.reset(this.regionSelectEl);
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = false;
                }
                this.regionSelectEl.disabled = true;
            } else if (this.disableAction=='nullify') {
                this.regionSelectEl.options.length = 1;
                this.regionSelectEl.value = '';
                this.regionSelectEl.selectedIndex = 0;
                this.lastCountryId = '';
            }
            this.setMarkDisplay(this.regionSelectEl, false);
        }

        this._checkRegionRequired();
        // Make Zip and its label required/optional
        var zipUpdater = new ZipUpdater(this.countryEl.value, this.zipEl);
        zipUpdater.update();
    },

    setMarkDisplay: function(elem, display){
        elem = $(elem);
        var labelElement = elem.up(0).down('label > span.required') ||
            elem.up(1).down('label > span.required') ||
            elem.up(0).down('label.required > em') ||
            elem.up(1).down('label.required > em');
        if(labelElement) {
            inputElement = labelElement.up().next('input');
            if (display) {
                labelElement.show();
                if (inputElement) {
                    inputElement.addClassName('required-entry');
                }
            } else {
                labelElement.hide();
                if (inputElement) {
//                    inputElement.removeClassName('required-entry');
                }
            }
        }
    }
}

ZipUpdater = Class.create();
ZipUpdater.prototype = {
    initialize: function(country, zipElement)
    {
        this.country = country;
        this.zipElement = $(zipElement);
    },

    update: function()
    {
        // Country ISO 2-letter codes must be pre-defined
        if (typeof optionalZipCountries == 'undefined') {
            return false;
        }

        // Ajax-request and normal content load compatibility
        if (this.zipElement != undefined) {
            Validation.reset(this.zipElement)
            this._setPostcodeOptional();
        } else {
            Event.observe(window, "load", this._setPostcodeOptional.bind(this));
        }
    },

    _setPostcodeOptional: function()
    {
        this.zipElement = $(this.zipElement);
        if (this.zipElement == undefined) {
            return false;
        }

        // find label
        var label = $$('label[for="' + this.zipElement.id + '"]')[0];
        if (label != undefined) {
            var wildCard = label.down('em') || label.down('span.required');
        }

        // Make Zip and its label required/optional
        if (optionalZipCountries.indexOf(this.country) != -1) {
//            while (this.zipElement.hasClassName('required-entry')) {
//                this.zipElement.removeClassName('required-entry');
//            }
//            if (wildCard != undefined) {
//                wildCard.hide();
//            }
        } else {
            this.zipElement.addClassName('required-entry');
            if (wildCard != undefined) {
                wildCard.show();
            }
        }
    }
}
