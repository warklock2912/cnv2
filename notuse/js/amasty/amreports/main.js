var amReports = new Class.create();

amReports.prototype = {
    tableTemplate : '<div class=\"grid\">\n    <div class=\"hor-scroll\">\n    <table id="sorttable" cellspacing=\"0\" class=\"data\">\n<thead>\n\t<tr class=\"headings\">\n\t\t{header}\n\t<\/tr>\n<\/thead>\n<tbody>\n\t{content}\n\t<\/tbody>\n<tfoot>\n\t{totals}\n\t\n<\/tfoot>\n<\/table>\n<\/div>\n<\/div>',
    initialize: function(url, currency) {
        this.reportsUrl = url;
        this.currency = currency;
        this.compareCount = 0;
        this.json_answer = [];
        this.resultTable = [];
        this.currentChart = false;
        this.requestCount = 1;
        this.currentChartData = [];
        this.currentDataProvider = '';
        this.excludeTotalArgs = ['store_id','product_id','period'];
        this.moneyFields = ['base_grand_total','base_tax_amount','base_shipping_amount','base_discount_amount','base_total_invoiced'];
        var self = this;
        new Ajax.Request(url, {
                    method: 'post',
                    asynchronous : false,
                    parameters: 'action=getTranslate',
                    onSuccess: function (transport) {
                        var translateArray = JSON.parse(transport.responseText);
                        Object.keys(translateArray).forEach(function(key) {
                            Translator.add(key,translateArray[key]);
                        });
                    }
                });
        window.onload = function() {
            var value = document.getElementById('json_answer').value;
            if (value!='') {
                var json_answer = JSON.parse(value);
                for (var i = 0; i <= json_answer.length - 1; i++) {
                    self.applyResponse(json_answer[i]);
                }
            }
        }
    },

    applyResponse: function(ajaxResponse)
    {
        if (ajaxResponse=='') {
            this.showMessage('No Results by your request ','notice');
            document.getElementById('chartdiv').innerHTML = '';
            document.getElementById('resultTable').innerHTML = '';
            document.getElementById('chartselector').innerHTML = '';

            return false;
        }
        this.enableExport();
        if (this.requestCount != 1) {
            this.addDataToGraph(ajaxResponse, document.getElementById('currentGraphField').value, this.requestCount);
            this.json_answer.push(ajaxResponse);
            document.getElementById('json_answer').value = JSON.stringify(this.json_answer);
            this.createCompareTable();
        } else {
            this.json_answer.push(ajaxResponse);
            document.getElementById('json_answer').value = JSON.stringify(this.json_answer);
            switch (document.getElementById('report_type').value) {
                case 'Bestsellers':
                    this.addPieGraph();
                    this.createTable();
                    break;
                case 'Couponcode':
                    this.addColumnChart('coupon_code','count', 'Coupon Code');
                    this.createTable();
                    this.addDataSetSelector('coupon_code');
                    break;
                case 'Country':
                    this.createTable();
                    this.loadGoogleMap();
                    break;
                case 'Newreturn':
                    this.createTable();
                    this.addNewReturnSelector();
                    this.createNewReturnGraph();
                    break;
                case 'Sales':
                case 'Salesbyproduct':
                    this.createTable();
                    this.createSalesGraph();
                    this.addDataSetSelector('period');
                    break;
                case 'Salesbyhour':
                case 'Salesbyweek':
                    this.createTable();
                    this.createHourGraph();
                    this.addDataSetSelector('period');
                    break;
                case 'Postcode':
                    this.createTable();
                    this.createHourGraph();
                    this.addDataSetSelector();
                    break;
                case 'Profit':
                    this.createTable();
                    this.createSalesGraph();
                    this.addDataSetSelector('period');
                    break;
                default :
                    this.changeDataSet();
                    this.addDataSetSelector();
                    break;
            }
        }
        this.requestCount++;
    },

    getItems: function()
    {
        var self = this;
        var serialized = $('edit_form').serialize();
        var dateFrom = document.getElementsByName("DateFrom[]");
        document.getElementById('messages').innerHTML = '';
        this.json_answer = [];
        this.requestCount = 1;
        if (typeof dateFrom != 'undefined') {
            for (var i = 0; i <= dateFrom.length - 1; i++) {
                new Ajax.Request(this.reportsUrl, {
                    method: 'post',
                    'asynchronous' : false,
                    parameters: serialized + '&action=getReport&multiDate=' + i,
                    onSuccess: function (transport) {
                        self.applyResponse(JSON.parse(transport.responseText))
                    }
                });
            }
        }
    },

    getReport: function() {
        var dateFrom = document.getElementsByName("DateFrom[]");
        var dateTo = document.getElementsByName("DateTo[]");
        var passed = true;
        if (!dateFrom) {
            this.getItems()
        } else {
            for (var i=0;i<=dateFrom.length-1;i++) {
                passed = Validation.validate(dateFrom[i].id);
                if (!passed) break;
            }
            for (var i=0;i<=dateTo.length-1;i++) {
                if (!passed) break;
                passed = Validation.validate(dateTo[i].id);
            }
            var allValidated = document.getElementsByClassName('required-entry');
            for (var i = 0; i < allValidated.length; ++i) {
                if (!passed) break;
                var item = allValidated[i];
                passed = Validation.validate(item.id);
            }

            if (passed) {
                this.getItems();
            }
        }
    },

    changeDataSet: function(title, column) {
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            var pushArray = [];
            for (var j=0;j<=this.json_answer.length-1;j++) {
                if (j==0) pushArray['period'] = this.json_answer[j][i][title];
                var index = j+1;
                if (typeof this.json_answer[j][i] !=='undefined') {
                    pushArray['data'+index] = this.json_answer[j][i][column];
                }
            }
            temp[i] = pushArray;
        }
        this.currentChartData = temp;
        this.currentChart.dataProvider = temp;
        if ( this.moneyFields.indexOf(column) !=-1 ) {
            if (this.compareCount<1) {
                this.currentChart.valueAxes[0].title = Translator.translate(column);
                this.currentChart.graphs[0].title = Translator.translate(column);
            }
            for (var i=1;i<=this.currentChart.graphs.length;i++) {
                this.currentChart.graphs[i-1].balloonText =  "[[value]] "+this.currency;
            }
        } else {
            if (this.compareCount<1) {
                this.currentChart.valueAxes[0].title = Translator.translate(column);
                this.currentChart.graphs[0].title = Translator.translate(column);
            }
            for (var i=1;i<=this.currentChart.graphs.length;i++) {
                this.currentChart.graphs[i-1].balloonText =  "[[value]]";
            }
        }

        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    addDataToGraph: function(data, column, number) {
        //this.createDefaultGraph();
        var g = new AmCharts.AmGraph();
        g.title = 'data' + number;
        g.valueField = 'data' + number;
        this.currentChart.addGraph(g);
        var temp = this.currentChartData;
        temp = this.joinArrays(data, temp, number, document.getElementById('currentGraphField').value , 'period');
        temp.sort(function(a, b) {
            var keyA = new Date(a.period),
                keyB = new Date(b.period);
            // Compare the 2 dates
            if(keyA < keyB) return -1;
            if(keyA > keyB) return 1;
            return 0;
        });
        this.currentChartData = temp;
        this.currentChart.dataProvider = temp;
        //this.currentChart.graphs[1].balloonText =  "[[value]] "+this.currency;
        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    joinArrays: function(json_answer, currentArray, joinIndex, joinField, uniqueField)
    {
        var temp = {};
        for (var i=0;i<=currentArray.length-1;i++) {
            if (typeof temp[currentArray[i][uniqueField]] == 'undefined') {
                temp[currentArray[i][uniqueField]] = currentArray[i];
                if (typeof json_answer[i]!= 'undefined' && typeof json_answer[i][joinField] != 'undefined') {
                    temp[currentArray[i][uniqueField]]['data'+joinIndex] = json_answer[i][joinField];
                }
            }
        }
        var result = [];
        for (var key in temp) {
            if (temp.hasOwnProperty(key)) {
                result.push(temp[key]);
            }
        }
        return result;
    },

    addPieGraph: function() {
        document.getElementById('chartdiv').style.height = '1000px';
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                title: this.json_answer[0][i]['product_name'],
                value: Math.ceil(this.json_answer[0][i]['qty_ordered'])
            } );
        }
        AmCharts.makeChart( "chartdiv", {
            "type": "pie",
            "theme": "light",
            "dataProvider": temp,
            "valueField": "value",
            "titleField": "title",
            "outlineAlpha": 0.4,
            "export": {
                "enabled": true
            }
        } );
    },

    addColumnChart: function(title, value, outputName) {
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                period: this.json_answer[0][i][title],
                data1: Math.ceil(this.json_answer[0][i][value])
            } );
        }
        var currentProvider = this.currentDataProvider;
        this.currentChart = AmCharts.makeChart("chartdiv",
            {
                "type": "serial",
                "colors": [
                    "#68c074",
                    "#FCD202",
                    "#B0DE09",
                    "#0D8ECF",
                    "#2A0CD0",
                    "#CD0D74",
                    "#CC0000",
                    "#00CC00",
                    "#0000CC",
                    "#DDDDDD",
                    "#999999",
                    "#333333",
                    "#990000"
                ],
                "startDuration": 1,
                "fontFamily": "Arial",
                "fontSize": 12,
                "theme": "default",
                "categoryAxis": {
                    "gridPosition": "start"
                },
                "categoryField": 'period',
                "graphs": [
                    {
                        "balloonText": "[[value]]",
                        "fillAlphas": 1,
                        "id": "AmGraph",
                        "title": Translator.translate(currentProvider),
                        "type": "column",
                        "valueField": "data1"
                    }
                ],
                "valueAxes": [
                    {
                        "id": "ValueAxis-1",
                        "title": Translator.translate(currentProvider)
                    }
                ],
                "legend": {
                    "useGraphSettings": true
                },
                "titles": [
                    {
                        "id": "Title-1",
                        "size": 15,
                        "text": outputName
                    }
                ],
                "dataProvider": temp
            }
        );
    },

    changeMultiplyDataSet: function(name) {
        var namePart = name.split('_');
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                period: this.json_answer[0][i].period,
                data1: Math.ceil(this.json_answer[0][i][namePart[0]]),
                data2: Math.ceil(this.json_answer[0][i][namePart[1]])
            } );
        }
        this.currentChart.dataProvider = temp;
        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    addNewReturnSelector: function() {
        this.currentDataProvider = 'new_return';
        var selector = '<fieldset> <h1>'+Translator.translate('Graph')+'</h1>';
        var fieldsToselect = {
            newUser_returnUser:Translator.translate('Number of Orders From New And Returning Customers'),
            newPaid_returnPaid: Translator.translate('Amount Spent By New And Returning Customers')
        };
        var firstRun = 0;
        var select = '';
        for(var index in fieldsToselect) {
            if (fieldsToselect.hasOwnProperty(index)) {
                if (firstRun==0) {
                    select = 'checked="checked"';
                } else {
                    select = '';
                }
                selector += '<label class="amreports-label"><input type="radio" id="currentGraphField" ' +
                'class="amreports-radio" name="tempRadio" onclick="amReports.changeMultiplyDataSet(this.value)"' +
                select+' value="'+index+'">'+fieldsToselect[index]+'</label>';
                firstRun++;
            }
        }
        selector += '</fieldset>';
        document.getElementById('chartselector').innerHTML = selector;
    },

    addDataSetSelector: function(title) {
        this.currentDataProvider = title;
        var selector = '<fieldset> <h1>'+Translator.translate('Graph')+'</h1>';
        var row = this.json_answer[0][0];
        var firstRun = 0;
        var select = '';
        for (var j=0;j<Object.keys(row).length;j++) {
            if (Object.keys(row)[j]!=title) {
                if (firstRun==0) {
                    select = 'checked="checked"';
                    this.currentChart.graphs[0].title = Translator.translate(Object.keys(row)[j]);
                } else {
                    select = '';
                }
                firstRun = 1;
                selector += '<label class="amreports-label"><input type="radio" id="currentGraphField" class="amreports-radio" name="tempRadio" onclick="amReports.changeDataSet(\''
                +title+'\',this.value)"'+select+' value="'+Object.keys(row)[j]+'">'+Translator.translate(Object.keys(row)[j])+'</label>';
            }
        }
        selector += '</fieldset>';
        document.getElementById('chartselector').innerHTML = selector;
    },

    createDefaultGraph: function() {
        this.currentChart = AmCharts.makeChart("chartdiv",
            {
                "type": "serial",
                "categoryField": "period",
                "dataDateFormat": "YYYY-MM-DD",
                "categoryAxis": {
                    "parseDates": true
                },
                "chartCursor": {},
                "chartScrollbar": {},
                "trendLines": [],
                "guides": [],
                "graphs": [
                    {
                        "bullet": "round",
                        "id": "AmGraph-1",
                        "title": "",
                        "valueField": "data1"
                    }
                ],
                "valueAxes": [
                    {
                        "id": "ValueAxis-1"
                    }
                ],
                "allLabels": [],

                "legend": {
                    "useGraphSettings": true
                }
            }
        );
        this.currentChart.pathToImages = "http://www.amcharts.com/lib/3/images/";
    },

    createSalesGraph: function() {
        this.createDefaultGraph();
        var e = document.getElementById("Period");
        var Period = e.options[e.selectedIndex].value;
        switch (Period) {
            case 'TO_DAYS':
                this.currentChart.dataDateFormat = "YYYY-MM-DD";
                break;
            case 'MONTH':
                this.currentChart.dataDateFormat = "YYYY-MM";
                break;
            case 'YEAR':
                this.currentChart.dataDateFormat = "YYYY";
                break;
        }
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                period: this.json_answer[0][i]['period'],
                data1: Math.ceil(this.json_answer[0][i]['count'])
            } );
        }
        this.currentChartData = temp;
        this.currentChart.dataProvider = temp;
        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    createHourGraph: function() {
        this.createDefaultGraph();
        this.currentChart.categoryAxis.parseDates = false;
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                period: this.json_answer[0][i]['period'],
                data1: Math.ceil(this.json_answer[0][i]['count'])
            } );
        }
        this.currentChartData = temp;
        this.currentChart.dataProvider = temp;
        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    createNewReturnGraph: function() {
        this.createDefaultGraph();
        var temp = [];
        for(var i=0;i<=this.json_answer[0].length-1;i++ ) {
            temp.push( {
                period: this.json_answer[0][i].period,
                data1: Math.ceil(this.json_answer[0][i]['newUser']),
                data2: Math.ceil(this.json_answer[0][i]['returnUser'])
            } );
        }
        this.currentChart.graphs[0].title = Translator.translate('New Customers');
        var g = new AmCharts.AmGraph();
        g.title = Translator.translate('Returning Customers');
        g.valueField = 'data2';
        this.currentChart.addGraph(g);
        this.currentChartData = temp;
        this.currentChart.dataProvider = temp;
        this.currentChart.validateData();
        this.currentChart.animateAgain();
    },

    loadGoogleMap: function() {
        var self = this;
        if(google) {
            google.load('visualization', '1.1', {
                packages: ['geochart'],
                callback: function() {
                    var temp = [ [Translator.translate('Country'), Translator.translate('total_item_count')] ];
                    for(var i=0;i<=self.json_answer[0].length-1;i++ ){
                        temp.push( [
                            self.json_answer[0][i]['country'],
                            Math.ceil(self.json_answer[0][i]['total_item_count']) ]
                         );
                    }
                    var data = google.visualization.arrayToDataTable(temp);
                    var options = {};
                    var chart = new google.visualization.GeoChart(document.getElementById('chartdiv'));
                    chart.draw(data, options);
                }
            } )
        }
    },

    createCompareTable: function() {
        var template = this.tableTemplate;
        var header = '';
        var content = '';
        var resTotals = [];
        for (var j=0;j<this.json_answer.length;j++) {
            var totals = [];
            for (var i=0;i<this.json_answer[j].length;i++) {
                var row = this.json_answer[j][i];
                if (i==0 && j==0) {
                    var translateHeader = this.translateHeader(row);
                    header +=translateHeader;
                }
                var index = 0;
                for(var k in row) {
                    if (k!='period') {
                        var number = parseFloat(row[k]);
                        if (isNaN(totals[index])) totals[index] = 0;
                        totals[index] += number;
                    } else {
                        if (i==0) totals[index] = row[k];
                        if (i==this.json_answer[j].length-1) {
                            this.currentChart.graphs[j].title = totals[index] + ' - ' +row[k];
                            this.currentChart.validateData();
                            this.currentChart.animateAgain();
                            totals[index] = totals[index] + ' - ' +row[k];
                        }
                    }
                    index++;
                }
            }
            resTotals[j] = totals;
        }
        //calc sum
        var sumTotals = [];
        for (var j=0;j<this.json_answer.length;j++) {
            for (var i = 0; i < resTotals[j].length; i++) {
                if (typeof sumTotals[i] == "undefined") sumTotals[i] = 0;
                sumTotals[i] += resTotals[j][i];
            }
        }

        for (var j=0;j<this.json_answer.length;j++) {
            content += '<tr>';
            for (var i=0;i<resTotals[j].length;i++) {
                if (typeof resTotals[j][i] !='string' && isNaN(resTotals[j][i])) resTotals[j][i] = 0;
                var percent = (resTotals[j][i] / sumTotals[i] * 100).toFixed(2);
                if (!isNaN(percent)) {
                    percent = ' ('+percent+'%)';
                    content += '<td>'+Number(resTotals[j][i].toFixed(2))+percent+'</td>';
                } else {
                    percent = '';
                    content += '<td>'+resTotals[j][i]+percent+'</td>';
                }
            }
            content += '</tr>';
        }

        template = template.replace('{header}', header );
        template = template.replace('{content}', content );
        template = template.replace('{totals}', '' );
        document.getElementById('resultTable').innerHTML = template;
        sorttable.makeSortable(document.getElementById('sorttable'));
    },

    createTable: function () {
        var template = this.tableTemplate;
        var header = '';
        var content = '';
        var totals = [];
        var footer = '';

        for (var i=0;i<this.json_answer[this.json_answer.length -1].length;i++) {
            var row = this.json_answer[this.json_answer.length -1][i];
            if (i==0) {
                var translateHeader = this.translateHeader(row);
                header +=translateHeader;
            }
            content += '<tr>';
            for (var j=0;j<Object.keys(row).length;j++) {
                if (typeof this.resultTable[i]!= 'undefined' && typeof this.resultTable[i][j]!= 'undefined') {
                    if (typeof this.resultTable[i]=== 'undefined' ) this.resultTable[i] = [];
                    this.resultTable[i][j] = row[Object.keys(row)[j]];
                    if (!isNaN(row[Object.keys(row)[j]])) {
                        content += '<td>'+Number(Number(row[Object.keys(row)[j]]).toFixed(2))+'</td>';
                    } else {
                        content += '<td>'+row[Object.keys(row)[j]]+'</td>';
                    }
                } else {
                    if(j==0) {
                        var id = row[Object.keys(row)[j]];
                    }
                    if (typeof this.resultTable[id]=== 'undefined' ) this.resultTable[id] = [];
                    if (typeof this.resultTable[id][j]=== 'undefined' ) this.resultTable[id][j] = [];
                    this.resultTable[id][j] = row[Object.keys(row)[j]];
                    if (!isNaN(row[Object.keys(row)[j]])) {
                        content += '<td id="'+id+'">'+Number(Number(row[Object.keys(row)[j]]).toFixed(2))+'</td>';
                    } else {
                        content += '<td id="'+id+'">'+row[Object.keys(row)[j]]+'</td>';
                    }

                }
                if (this.excludeTotalArgs.indexOf(Object.keys(row)[j]) ==-1 ) {
                    var number = parseFloat(row[Object.keys(row)[j]]);
                    if (isNaN(totals[j])) totals[j] = 0;
                    if (!isNaN(number))
                        totals[j] = number  + totals[j];
                } else {
                    if (isNaN(totals[j])) totals[j] = '';
                }
            }
            content += '</tr>';
        }

        for (var i=0;i<totals.length;i++) {
            if (parseFloat(totals[i])) {
                footer += '<td>'+Number(Number(totals[i]).toFixed(2))+'</td>';
            } else {
                footer += '<td></td>';
            }

        }
        template = template.replace('{header}', header );
        template = template.replace('{content}', content );
        template = template.replace('{totals}', '<tr class="totals">\n\t\t'+footer+'</tr>' );
        document.getElementById('resultTable').innerHTML = template;
        sorttable.makeSortable(document.getElementById('sorttable'));
    },

    translateHeader: function(translateArray) {
        var result = '';
        Object.keys(translateArray).forEach(function(key) {
            result +='<th class=" no-link">'+Translator.translate(key)+'</th>';
        });
        return result;
    },

    createDeleteHref: function() {
        var deleteHref = document.createElement("a");
        deleteHref.href = '#';
        deleteHref.className = 'amreports-deleteField';
        deleteHref.onclick = function() {
            var select1 = this.previousSibling;
            var select2 = select1.previousSibling;
            var br = select2.previousSibling;
            select1.remove();
            select2.remove();
            br.remove();
            this.remove();
            return false;
        };
        return deleteHref;
    },

    createProfitAction: function() {
        //Create array of options to be added
        var actions = ["+","-","/","*"];
        var selectPlus = document.createElement("select");
        selectPlus.className = 'amreports-formulaAction';
        selectPlus.name = 'ProfitFormula[]';
        for (var i = 0; i < actions.length; i++) {
            var option = document.createElement("option");
            option.value = actions[i];
            option.text = actions[i];
            selectPlus.appendChild(option);
        }
        return selectPlus;
    },

    createProfitSelect: function() {
        var select = document.getElementById("ProfitFormula").cloneNode(true);
        select.className = 'amreports-formulaSelect';
        return select;
    },

    addFormula: function() {
        var select = this.createProfitSelect();
        //Create and append select list
        var selectPlus = this.createProfitAction();
        var deleteHref = this.createDeleteHref();
        var br = document.createElement("BR");
        document.getElementById("ProfitFormula").parentElement.appendChild(br);
        document.getElementById("ProfitFormula").parentElement.appendChild(selectPlus);
        document.getElementById("ProfitFormula").parentElement.appendChild(select);
        document.getElementById("ProfitFormula").parentElement.appendChild(deleteHref);
        var plus = document.getElementById("plusField").cloneNode(true);
        document.getElementById("plusField").remove();
        document.getElementById("ProfitFormula").parentElement.appendChild(plus);
        return false;
    },

    generateFormulaFields: function(values) {
        var self = this;
        document.observe("dom:loaded", function() {
            //Create array of options to be added
            var actions = ["+", "-", "/", "*"];
            for (var i = 1; i <= values.split(',').length; ++i) {
                var current = values.split(',')[i - 1];
                if (i == 1) {
                    document.getElementById("ProfitFormula").value = current;
                    continue;
                }
                if (actions.indexOf(current) >= 0) {
                    var selectPlus = self.createProfitAction();
                    selectPlus.value = current;
                    var br = document.createElement("BR");
                    document.getElementById("ProfitFormula").parentElement.appendChild(br);
                    document.getElementById("ProfitFormula").parentElement.appendChild(selectPlus);
                } else {
                    var deleteHref = self.createDeleteHref();
                    var select = self.createProfitSelect();
                    select.value = current;
                    document.getElementById("ProfitFormula").parentElement.appendChild(select);
                    document.getElementById("ProfitFormula").parentElement.appendChild(deleteHref);
                    var plus = document.getElementById("plusField").cloneNode(true);
                    document.getElementById("plusField").remove();
                    document.getElementById("ProfitFormula").parentElement.appendChild(plus);
                }
            }
            return false;
        })
    },

    createCompareDelete: function() {
        var deleteHref = document.createElement("a");
        deleteHref.href = '#';
        deleteHref.id = this.compareCount;
        deleteHref.className = 'amreports-deleteField';
        deleteHref.onclick = function() {
            document.getElementById("DateFrom"+this.id).parentElement.parentElement.parentElement.parentElement.remove();
            amReports.compareCount--;
            this.remove();
            return false;
        };
        return deleteHref;
    },

    reorderCompares: function(num) {

    },

    addCompare: function(valueFrom,valueTo) {
        this.compareCount++;
        var dateFrom = document.getElementById("DateFrom0").cloneNode(true);
        dateFrom.id = 'DateFrom'+this.compareCount;
        if (valueFrom) {
            dateFrom.value = valueFrom;
        }
        var dateTo = document.getElementById("DateTo0").cloneNode(true);
        dateTo.id = 'DateTo'+this.compareCount;
        dateTo.style.marginLeft = "9px";
        if (valueTo) {
            dateTo.value = valueTo;
        }
        var tr = document.createElement("tr");
        var td = document.createElement("td");
        td.className = 'label';
        tr.appendChild(td);
        var label = document.createElement('label');
        label.innerHTML = Translator.translate('Compare Date From');
        tr.appendChild(td);
        td.appendChild(label);
        td = document.createElement("td");
        td.className = 'report-td';

        var div = document.createElement("div");
        div.className = 'value';
        td.appendChild(div);

        var leftdiv = document.createElement("div");
        leftdiv.className = 'value';
        leftdiv.style.float = 'left';
        tr.appendChild(td);
        div.appendChild(leftdiv);
        leftdiv.appendChild(dateFrom);
        var dateTrig = document.getElementById("DateFrom0_trig").cloneNode(true);
        dateTrig.id = 'DateFrom'+this.compareCount+'_trig';
        //dateTrig.style.marginLeft = "3px";
        leftdiv.appendChild(dateTrig);
        this.insertAfter(tr, document.getElementById("DateTo"+(this.compareCount-1)).parentElement.parentElement.parentElement.parentElement);
        Calendar.setup({
            inputField: 'DateFrom'+this.compareCount,
            ifFormat: "%e/%m/%Y",
            showsTime: false,
            button: 'DateFrom'+this.compareCount+'_trig',
            align: "Bl",
            singleClick : true
        });

        var lastElem = tr;
        leftdiv = document.createElement("div");
        leftdiv.className = 'value';
        leftdiv.style.float = 'left';
        leftdiv.appendChild(dateTo);
        dateTrig = document.getElementById("DateTo0_trig").cloneNode(true);
        dateTrig.id = 'DateTo'+this.compareCount+'_trig';
        //dateTrig.style.marginLeft = "3px";
        leftdiv.appendChild(dateTrig);
        deleteHref = this.createCompareDelete();

        div.appendChild(leftdiv);
        div.appendChild(deleteHref);
        this.insertAfter(tr, lastElem);
        Calendar.setup({
            inputField: 'DateTo'+this.compareCount,
            ifFormat: "%e/%m/%Y",
            showsTime: false,
            button: 'DateTo'+this.compareCount+'_trig',
            align: "Bl",
            singleClick : true
        });
        return false;
    },

    showMessage: function(txt, type) {
        var html = '<ul class="messages"><li class="'+type+'-msg"><ul><li>' + txt + '</li></ul></li></ul>';
        document.getElementById('messages').innerHTML = html;
    },

    insertAfter: function(newElement,targetElement) {
        var parent = targetElement.parentNode;
        if(parent.lastchild == targetElement) {
            parent.appendChild(newElement);
        } else {
            parent.insertBefore(newElement, targetElement.nextSibling);
        }
    },
    isFloat: function(n) {
        return n === Number(n) && n % 1 !== 0;
    },
    enableExport: function() {
        document.getElementById('export').style.display = 'block';
    },
    export: function() {
        window.open('data:application/vnd.ms-excel,' + document.getElementById('sorttable').innerHTML);
        e.preventDefault();
    },
    pdfExport: function() {
        var l = {
            orientation: 'l',
            unit: 'pt',
            format: 'a3',
            compress: true,
            fontSize: 8,
            lineHeight: 1,
            autoSize: true,
            printHeaders: true
        };
        var pdf = new jsPDF(l, '', '', '');
        pdf.setFontSize(12);
        // source can be HTML-formatted string, or a reference
        // to an actual DOM element from which the text will be scraped.
        source = jQuery('#resultTable .hor-scroll')[0];

        // we support special element handlers. Register them with jQuery-style
        // ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
        // There is no support for any other type of selectors
        // (class, of compound) at this time.
        specialElementHandlers = {
            // element with id of "bypass" - jQuery style selector
            '#bypassme': function(element, renderer) {
                // true = "handled elsewhere, bypass text extraction"
                return true
            }
        };
        margins = {
            top: 80,
            bottom: 60,
            left: 40,
            width: 1024
        };
        // all coords and widths are in jsPDF instance's declared units
        // 'inches' in this case
        pdf.fromHTML(
            source, // HTML string or DOM elem ref.
            margins.left, // x coord
            margins.top, {// y coord
                'width': margins.width, // max width of content on PDF
                'elementHandlers': specialElementHandlers
            },
            function(dispose) {
                // dispose: object with X, Y of the last line add to the PDF
                //          this allow the insertion of new lines after html
                pdf.save('pdfExport.pdf');
            }
            , margins);
    },
    exportTableToCSV: function($table, filename) {
        var $rows = $table.find('tr:has(td)'),

        // Temporary delimiter characters unlikely to be typed by keyboard
        // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

        // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

        // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                    var $row = jQuery(row),
                        $cols = $row.find('td');

                    return $cols.map(function (j, col) {
                        var $col = jQuery(col),
                            text = $col.text();

                        return text.replace(/"/g, '""'); // escape double quotes

                    }).get().join(tmpColDelim);

                }).get().join(tmpRowDelim)
                    .split(tmpRowDelim).join(rowDelim)
                    .split(tmpColDelim).join(colDelim) + '"',

        // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        jQuery(this)
            .attr({
                'download': filename,
                'href': csvData,
                'target': '_blank'
            });
    }
};