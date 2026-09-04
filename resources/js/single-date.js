import flatpickr from 'flatpickr';

/**
 * Alpine component backing a single flatpickr date field, e.g. Quotation Date /
 * Expiry Date on the quotation form. Registered as Alpine.data('datePicker', ...)
 * in app.js and used via x-data="datePicker('quotation_date', '{{ $quotation_date }}')".
 *
 * Like dateRangePicker (date-range.js), the input is fully owned by flatpickr
 * (no wire:model) so a Livewire re-render triggered by another field on the
 * form never fights the picker for control of the DOM. The chosen date is
 * pushed to the named Livewire property explicitly via $wire.set.
 */
export default function singleDatePicker(fieldName, initial = '') {
    return {
        fp: null,

        init() {
            this.fp = flatpickr(this.$refs.input, {
                altInput: true,
                altFormat: 'F J, Y',
                dateFormat: 'Y-m-d',
                defaultDate: initial || null,
                appendTo: document.body,
                onChange: (selectedDates, dateStr) => {
                    this.$wire.set(fieldName, dateStr);
                },
            });
        },
    };
}
