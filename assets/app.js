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

flatpickr(".js-datepicker", {
    dateFormat: "d/m/Y", // matches 'dd/MM/yyyy' in PHP
    allowInput: true,
    altInput: true,
    altFormat: "d/m/Y",
});
