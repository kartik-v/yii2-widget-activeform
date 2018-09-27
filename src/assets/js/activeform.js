/*!
 * @package   yii2-widget-activeform
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @version   1.5.7
 *
 * Active Field Hints Display Module
 *
 * Author: Kartik Visweswaran
 * Copyright: 2015, Kartik Visweswaran, Krajee.com
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
var kvBs4InitForm = function () {
};
(function ($) {
    "use strict";
    var $h, ActiveFieldHint;
    $h = {
        NAMESPACE: '.kvActiveField',
        isEmpty: function (val, trim) {
            return val === undefined || val === [] || val === null || val === '' || trim && $.trim(val) === '';
        }
    };
    ActiveFieldHint = function (element, options) {
        var self = this;
        self.$element = $(element);
        $.each(options, function (key, val) {
            self[key] = val;
        });
        self.init();
    };
    ActiveFieldHint.prototype = {
        constructor: ActiveFieldHint,
        init: function () {
            var self = this, $el = self.$element, $block = $el.find('.kv-hint-block'), content = $block.html(),
                $hints = $el.find('.kv-hintable'), $span;
            $block.hide();
            if ($h.isEmpty(content)) {
                return;
            }
            if (!$h.isEmpty(self.contentCssClass)) {
                $span = $(document.createElement('span')).addClass(self.contentCssClass).append(content);
                $span = $(document.createElement('span')).append($span);
                content = $span.html();
                $span.remove();
            }
            $hints.each(function () {
                var $src = $(this);
                if ($src.hasClass('kv-type-label')) {
                    $src.removeClass(self.labelCssClass).addClass(self.labelCssClass);
                } else {
                    $src.removeClass('hide ' + self.iconCssClass).addClass(self.iconCssClass);
                }
                if ($src.hasClass('kv-hint-click')) {
                    self.listen('click', $src, content);
                }
                if ($src.hasClass('kv-hint-hover')) {
                    self.listen('hover', $src, content);
                }
            });
            if (self.hideOnEscape) {
                $(document).on('keyup', function (e) {
                    $hints.each(function () {
                        var $src = $(this);
                        if (e.which === 27) {
                            $src.popover('hide');
                        }
                    });
                });
            }
            if (self.hideOnClickOut) {
                $('body').on('click', function (e) {
                    $hints.each(function () {
                        var $src = $(this);
                        if (!$src.is(e.target) && $src.has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                            $src.popover('hide');
                        }
                    });
                });
            }
        },
        listen: function (event, $src, content) {
            var self = this, opts = {
                html: true,
                trigger: 'manual',
                content: content,
                title: self.title,
                placement: self.placement,
                container: self.container || false,
                animation: !!self.animation,
                delay: self.delay,
                selector: self.selector
            };
            if (!$h.isEmpty(self.template)) {
                opts.template = self.template;
            }
            if (!$h.isEmpty(self.viewport)) {
                opts.viewport = self.viewport;
            }
            $src.popover(opts);
            if (event === 'click') {
                self.raise($src, 'click', function (e) {
                    e.preventDefault();
                    $src.popover('toggle');
                });
                return;
            }
            self.raise($src, 'mouseenter', function () {
                $src.popover('show');
            });
            self.raise($src, 'mouseleave', function () {
                $src.popover('hide');
            });
        },
        raise: function ($elem, event, callback) {
            event = event + $h.NAMESPACE;
            $elem.off(event).on(event, callback);
        }
    };

    //ActiveFieldHint plugin definition
    $.fn.activeFieldHint = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this),
                data = $this.data('activeFieldHint'),
                options = typeof option === 'object' && option;

            if (!data) {
                $this.data('activeFieldHint',
                    (data = new ActiveFieldHint(this, $.extend({}, $.fn.activeFieldHint.defaults, options, $(this).data()))));
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.activeFieldHint.defaults = {
        labelCssClass: 'kv-hint-label',
        iconCssClass: 'kv-hint-icon',
        contentCssClass: 'kv-hint-content',
        hideOnEscape: false,
        hideOnClickOut: false,
        title: '',
        placement: 'right',
        container: 'body',
        delay: 0,
        animation: true,
        selector: false,
        template: '',
        viewport: ''
    };

    kvBs4InitForm = function () {
        var controls = ['.form-control', '.custom-control-input', '.custom-select', '.custom-range', '.custom-file-input'],
            validControls = controls.join(','),
            errorControls = '.has-error ' + controls.join(',.has-error '),
            successControls = '.has-success ' + controls.join(',.has-success '),
            resetControls = function ($form) {
                $form.find(validControls).removeClass('is-valid is-invalid');
            };
        $('form').on('afterValidateAttribute', function () {
            var $form = $(this);
            resetControls($form);
            if ($form.find('.has-error').length || $form.find('.has-success').length) {
                $form.find(errorControls).addClass('is-invalid');
                $form.find(successControls).addClass('is-valid');
            }
        }).on('reset', function () {
            var $form = $(this);
            setTimeout(function () {
                resetControls($form);
            }, 100);
        });
    };
})(window.jQuery);