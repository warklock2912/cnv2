/**
 * gridstack.js 0.3.0-dev
 * http://troolee.github.io/gridstack.js/
 * (c) 2014-2016 Pavel Reznikov, Dylan Weiss
 * gridstack.js may be freely distributed under the MIT license.
 * @preserve
 */



var $_adj = jQuery.noConflict();






(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'lodash', 'gridstack', 'jquery-ui/data', 'jquery-ui/disable-selection', 'jquery-ui/focusable',
            'jquery-ui/form', 'jquery-ui/ie', 'jquery-ui/keycode', 'jquery-ui/labels', 'jquery-ui/jquery-1-7',
            'jquery-ui/plugin', 'jquery-ui/safe-active-element', 'jquery-ui/safe-blur', 'jquery-ui/scroll-parent',
            'jquery-ui/tabbable', 'jquery-ui/unique-id', 'jquery-ui/version', 'jquery-ui/widget',
            'jquery-ui/widgets/mouse', 'jquery-ui/widgets/draggable', 'jquery-ui/widgets/droppable',
            'jquery-ui/widgets/resizable'], factory);
    } else if (typeof exports !== 'undefined') {
        try {
            $_adj = require('jquery');
        } catch (e) {
        }
        try {
            _ = require('lodash');
        } catch (e) {
        }
        try {
            GridStackUI = require('gridstack');
        } catch (e) {
        }
        factory($_adj, _, GridStackUI);
    } else {
        factory($_adj, _, GridStackUI);
    }
})(function ($, _, GridStackUI) {

    var scope = window;

    /**
     * @class JQueryUIGridStackDragDropPlugin
     * $_adj UI implementation of drag'n'drop gridstack plugin.
     */
    function JQueryUIGridStackDragDropPlugin(grid) {
        GridStackUI.GridStackDragDropPlugin.call(this, grid);
    }

    GridStackUI.GridStackDragDropPlugin.registerPlugin(JQueryUIGridStackDragDropPlugin);

    JQueryUIGridStackDragDropPlugin.prototype = Object.create(GridStackUI.GridStackDragDropPlugin.prototype);
    JQueryUIGridStackDragDropPlugin.prototype.constructor = JQueryUIGridStackDragDropPlugin;

    JQueryUIGridStackDragDropPlugin.prototype.resizable = function (el, opts) {
        el = $(el);
        if (opts === 'disable' || opts === 'enable') {
            el.resizable(opts);
        } else if (opts === 'option') {
            var key = arguments[2];
            var value = arguments[3];
            el.resizable(opts, key, value);
        } else {
            el.resizable(_.extend({}, this.grid.opts.resizable, {
                start: opts.start || function () {
                },
                stop: opts.stop || function () {
                },
                resize: opts.resize || function () {
                }
            }));
        }
        return this;
    };

    JQueryUIGridStackDragDropPlugin.prototype.draggable = function (el, opts) {
        el = $(el);
        if (opts === 'disable' || opts === 'enable') {
            el.draggable(opts);
        } else {
            el.draggable(_.extend({}, this.grid.opts.draggable, {
                containment: this.grid.opts.isNested ? this.grid.container.parent() : null,
                start: opts.start || function () {
                },
                stop: opts.stop || function () {
                },
                drag: opts.drag || function () {
                }
            }));
        }
        return this;
    };

    JQueryUIGridStackDragDropPlugin.prototype.droppable = function (el, opts) {
        el = $(el);
        if (opts === 'disable' || opts === 'enable') {
            el.droppable(opts);
        } else {
            el.droppable({
                accept: opts.accept
            });
        }
        return this;
    };

    JQueryUIGridStackDragDropPlugin.prototype.isDroppable = function (el, opts) {
        el = $(el);
        return Boolean(el.data('droppable'));
    };

    JQueryUIGridStackDragDropPlugin.prototype.on = function (el, eventName, callback) {
        $(el).on(eventName, callback);
        return this;
    };

    return JQueryUIGridStackDragDropPlugin;
});


ko.components.register('dashboard-grid', {
    viewModel: {
        createViewModel: function (controller, componentInfo) {
            var ViewModel = function (controller, componentInfo) {
                var grid = null;

                this.widgets = controller.widgets;

                this.afterAddWidget = function (items) {
                    if (grid == null) {
                        grid = $(componentInfo.element).find('.grid-stack').gridstack({
                            auto: false
                        }).data('gridstack');
                    }

                    var item = _.find(items, function (i) {
                        return i.nodeType == 1
                    });
                    grid.addWidget(item);
                    ko.utils.domNodeDisposal.addDisposeCallback(item, function () {
                        grid.removeWidget(item);
                    });
                };
            };

            return new ViewModel(controller, componentInfo);
        }
    },
    template: {element: 'gridstack-template'}
});

$(function () {
    var Controller = function (widgets) {
        var self = this;

        this.widgets = ko.observableArray(widgets);

        this.addNewWidget = function () {
            this.widgets.push({
                x: 0,
                y: 0,
                width: Math.floor(1 + 3 * Math.random()),
                height: Math.floor(1 + 3 * Math.random()),
                auto_position: true
            });
            return false;
        };

        this.deleteWidget = function (item) {
            self.widgets.remove(item);
            return false;
        };
        self.save = function (form) {
            console.log(ko.utils.stringifyJson(self.widgets));
        };
    };

    var widgets = [
        {x: 0, y: 0, width: 2, height: 2},
        {x: 2, y: 0, width: 4, height: 2},
        {x: 6, y: 0, width: 2, height: 4},
        {x: 1, y: 2, width: 4, height: 2}
    ];

    var controller = new Controller(widgets);
    ko.applyBindings(controller);
});
