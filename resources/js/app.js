import './bootstrap';
import './animations';
import './nav';

import Alpine from 'alpinejs';
import dateRangePicker from './date-range';

window.Alpine = Alpine;
Alpine.data('dateRangePicker', dateRangePicker);
Alpine.start();
