import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener('turbo:load', function() {
    flatpickr(".js-datepicker", {
        enableTime: true,
        dateFormat: "d-m-Y H:i",
        time_24hr: true,
        defaultHour: 11,
        defaultMinute: 0,
        allowInput: true
    });
});
