import './bootstrap';
import './animations';
import './nav';
import './fava-chat';

import Alpine from 'alpinejs';
import dateRangePicker from './date-range';
import singleDatePicker from './single-date';

window.Alpine = Alpine;
Alpine.data('dateRangePicker', dateRangePicker);
Alpine.data('datePicker', singleDatePicker);
Alpine.start();
