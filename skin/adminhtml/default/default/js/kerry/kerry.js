KerryAPI = Class.create();
KerryAPI.prototype =
{
    options: null,

    initialize: function (options) {
        this.options = options;
        console.log(this.options);
    },

    receiveShipment: function () {
        var self = this,
            totalPackages = $(self.options.totalPackages).getValue();
        $('loading-mask').setStyle({'z-index': '2000'});
        new Ajax.Request(
            self.options.receiveShipmentDataUrl,
            {
                method: 'post',
                postBody : 'totalPackages=' + totalPackages,
                onCreate: function() {$('loading-mask').show()}.bind(self),
                onComplete: function() {$('loading-mask').hide()}.bind(self),
                onFailure: function() {$('loading-mask').hide()}.bind(self),
                onSuccess: function(transport) {
                    var response = JSON.parse(transport.responseText);
                    if(response.status === 1){
                        self.callAPI(response.shipment);
                    }
                }.bind(self)
            }
        );
        return true;
    },
    
    callAPI: function (shipment) {
        var self = this;
        new Ajax.Request(
            self.options.accessAPIUrl + '?tot_pkg=' + $(self.options.totalPackages).getValue(),
            {
                method: 'post',
                contentType: 'application/json',
                postBody: JSON.stringify(shipment),
                onCreate: function() {$('loading-mask').show()}.bind(self),
                onComplete: function() {$('loading-mask').hide()}.bind(self),
                onFailure: function() {$('loading-mask').hide()}.bind(self),
                onSuccess: function(transport) {
                    var response = JSON.parse(transport.responseText);
                    window.location.reload();
                }.bind(self)
            }
        );
        return true;
    }
};