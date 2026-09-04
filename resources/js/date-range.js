import flatpickr from 'flatpickr';

/**
 * Alpine component backing a single-calendar date-range filter.
 * Registered as Alpine.data('dateRangePicker', ...) in app.js and used via
 * x-data="dateRangePicker('{{ $dateFrom }}', '{{ $dateTo }}')".
 *
 * The underlying <input x-ref="input"> is left fully owned by flatpickr
 * (no wire:model / value attribute) so a Livewire re-render triggered by
 * another filter never clobbers the picker's own DOM state. Selections are
 * pushed to the Livewire component explicitly via $wire.set once a full
 * range (start + end) is chosen.
 */
export default function dateRangePicker(initialFrom = '', initialTo = '') {
    return {
        fp: null,

        init() {
            this.fp = flatpickr(this.$refs.input, {
                mode: 'range',
                dateFormat: 'M j, Y',
                appendTo: document.body,
                defaultDate: initialFrom && initialTo ? [initialFrom, initialTo] : [],
                onChange: (selectedDates, dateStr, instance) => {
                    if (selectedDates.length !== 2) return;

                    this.$wire.set('dateFrom', instance.formatDate(selectedDates[0], 'Y-m-d'));
                    this.$wire.set('dateTo', instance.formatDate(selectedDates[1], 'Y-m-d'));
                },
            });
        },

        clear() {
            this.fp?.clear();
        },
    };
}
