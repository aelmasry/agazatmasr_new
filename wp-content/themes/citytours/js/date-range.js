/**
 * Date-range behaviour for paired bootstrap-datepicker inputs (check-in/check-out,
 * departure/return). Highlights the selected period on both calendars, previews
 * it while hovering the end calendar, opens the end calendar after a start date
 * is picked and shows the number of nights.
 *
 * Pairs are attached automatically for any form containing
 *   input[name=date_from] + input[name=date_to]          (hotels)
 *   input[name=DepartureDate] + input[name=ReturnDate]   (flights)
 * Forms with their own constraint logic call CTDateRange.attach() themselves
 * with { constrain: false } and are skipped by the auto-attach (data flag).
 */
(function ($) {
    'use strict';

    var DAY_MS = 864e5;

    function ymd(date) {
        if (!date) {
            return '';
        }
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return date.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d;
    }

    function addDays(date, days) {
        var next = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        next.setDate(next.getDate() + days);
        return next;
    }

    function nightsBetween(from, to) {
        return Math.round((Date.UTC(to.getFullYear(), to.getMonth(), to.getDate()) -
            Date.UTC(from.getFullYear(), from.getMonth(), from.getDate())) / DAY_MS);
    }

    function picker($input) {
        return $input.data('datepicker') || null;
    }

    // bootstrap-datepicker 1.3 has no clearDates(); update() re-reads the empty value.
    function clearInput($input) {
        $input.val('');
        if (picker($input)) {
            $input.datepicker('update');
        }
    }

    function getDate($input) {
        var dp = picker($input);
        var date = dp ? $input.datepicker('getDate') : null;
        if (date && !isNaN(date.getTime())) {
            return date;
        }
        var value = $input.val();
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            var p = value.split('-');
            return new Date(+p[0], +p[1] - 1, +p[2]);
        }
        return null;
    }

    // Normalise any beforeShowDay return value to an object (mirrors the plugin).
    function normalise(result) {
        if (result === undefined || result === null) {
            return {};
        }
        if (typeof result === 'boolean') {
            return {enabled: result};
        }
        if (typeof result === 'string') {
            return {classes: result};
        }
        return $.extend({}, result);
    }

    // Local date shown by a <td class="day"> in the current view of a picker.
    function cellDate(dp, $td) {
        var view = new Date(dp.viewDate);
        var month = view.getUTCMonth();
        if ($td.hasClass('old')) {
            month -= 1;
        } else if ($td.hasClass('new')) {
            month += 1;
        }
        return new Date(view.getUTCFullYear(), month, parseInt($td.text(), 10));
    }

    function attach($start, $end, options) {
        $start = $($start).first();
        $end = $($end).first();
        if (!$start.length || !$end.length || !$.fn.datepicker || $start.data('ctDateRange')) {
            return;
        }

        var opts = $.extend({
            minNights: 1,       // minimum gap between start and end (0 = same day allowed)
            constrain: true,    // keep end >= start + minNights and clear invalid end dates
            autoOpenEnd: true,  // open the end calendar after a start date is picked
            nightsLabel: null   // function (nights) -> text shown under the calendars
        }, options);

        var base = {
            startDate: 'today',
            language: 'ar',
            autoclose: true,
            todayHighlight: true
        };

        // Native date inputs would open a second (browser) picker on top.
        $start.add($end).each(function () {
            if (this.type === 'date') {
                this.type = 'text';
            }
            if (!picker($(this))) {
                $(this).datepicker(base);
            }
        });

        var hoverDate = null;

        function rangeClasses(date) {
            var from = getDate($start);
            var to = getDate($end) || hoverDate;
            var key = ymd(date);
            var classes = [];
            if (from && key === ymd(from)) {
                classes.push('range-start');
            }
            if (from && to && to > from) {
                if (key === ymd(to)) {
                    classes.push(getDate($end) ? 'range-end' : 'range-end range-preview');
                } else if (key > ymd(from) && key < ymd(to)) {
                    classes.push(getDate($end) ? 'in-range' : 'in-range range-preview');
                }
            }
            return classes.join(' ');
        }

        // Wrap whatever beforeShowDay the page already configured so its
        // enabled/disabled rules stay in charge; we only add classes. Set it on
        // the raw options (_o) too: setStartDate() rebuilds `o` from `_o`.
        $start.add($end).each(function () {
            var dp = picker($(this));
            var original = dp.o.beforeShowDay;
            dp._o.beforeShowDay = dp.o.beforeShowDay = function (date) {
                var result = normalise(original && original !== $.noop ? original.call(this, date) : undefined);
                var extra = rangeClasses(date);
                if (extra) {
                    result.classes = result.classes ? result.classes + ' ' + extra : extra;
                }
                return result;
            };
            dp.picker.addClass('ct-range-picker');
        });

        function refreshSummary() {
            if (typeof opts.nightsLabel !== 'function') {
                return;
            }
            var from = getDate($start);
            var to = getDate($end) || hoverDate;
            var text = from && to && to > from ? opts.nightsLabel(nightsBetween(from, to)) : '';
            $start.add($end).each(function () {
                var $picker = picker($(this)).picker;
                var $summary = $picker.children('.ct-range-summary');
                if (!$summary.length) {
                    $summary = $('<div class="ct-range-summary" aria-live="polite"></div>').appendTo($picker);
                }
                $summary.text(text).toggle(!!text);
            });
        }

        function redraw() {
            $start.add($end).each(function () {
                var dp = picker($(this));
                if (dp) {
                    dp.fill();
                }
            });
            refreshSummary();
        }

        function syncEnd() {
            var from = getDate($start);
            if (!from || !opts.constrain) {
                return;
            }
            var earliest = addDays(from, opts.minNights);
            $end.datepicker('setStartDate', earliest);
            var to = getDate($end);
            if (to && to < earliest) {
                clearInput($end);
            }
        }

        // A formatted Invalid Date shows as "NaN-NaN-NaN"; never leave that in a field.
        function dropInvalid() {
            $start.add($end).each(function () {
                if (/NaN/.test(this.value)) {
                    clearInput($(this));
                }
            });
        }

        $start.add($end).on('changeDate change', dropInvalid);

        $start.on('changeDate', function () {
            syncEnd();
            hoverDate = null;
            redraw();
            if (opts.autoOpenEnd && getDate($start) && !getDate($end) && $end.is(':visible')) {
                // Let autoclose hide the start calendar first.
                setTimeout(function () {
                    $end.trigger('focus');
                }, 0);
            }
        });

        $end.on('changeDate', function () {
            hoverDate = null;
            redraw();
        });

        $start.add($end).on('show', refreshSummary);

        // Hover preview on the end calendar while no end date is chosen yet.
        var endPicker = picker($end);
        endPicker.picker
            .on('mouseenter', '.datepicker-days td.day:not(.disabled)', function () {
                if (getDate($end) || !getDate($start)) {
                    return;
                }
                var hovered = cellDate(endPicker, $(this));
                // fill() rebuilds the cells, so mouseenter fires again on the
                // next mouse move over the same day; skip redundant redraws.
                if (hoverDate && ymd(hoverDate) === ymd(hovered)) {
                    return;
                }
                hoverDate = hovered;
                redraw();
            })
            .on('mouseleave', '.datepicker-days tbody', function () {
                if (hoverDate) {
                    hoverDate = null;
                    redraw();
                }
            });

        $start.data('ctDateRange', true);
        $end.data('ctDateRange', true);
        dropInvalid();
        syncEnd();
        redraw();
    }

    // The site UI is Arabic-only (the <html lang> attribute is not reliable here).
    function nightsText(nights) {
        if (nights === 1) {
            return 'ليلة واحدة';
        }
        if (nights === 2) {
            return 'ليلتان';
        }
        return nights + (nights <= 10 ? ' ليالٍ' : ' ليلة');
    }

    function autoAttach(root) {
        $(root || document).find('form').each(function () {
            var $form = $(this);
            var $from = $form.find('input[name="date_from"]:not([type="hidden"])');
            var $to = $form.find('input[name="date_to"]:not([type="hidden"])');
            if ($from.length && $to.length) {
                attach($from, $to, {minNights: 1, nightsLabel: nightsText});
            }
            var $dep = $form.find('input[name="DepartureDate"]');
            var $ret = $form.find('input[name="ReturnDate"]');
            if ($dep.length && $ret.length) {
                attach($dep, $ret, {minNights: 0, autoOpenEnd: false});
            }
        });
    }

    window.CTDateRange = {
        attach: attach,
        autoAttach: autoAttach,
        nightsText: nightsText
    };

    // Page templates initialise their pickers in their own ready handlers, which
    // were registered before this footer script, so they have run by now.
    $(function () {
        autoAttach(document);
    });
})(jQuery);
