// tailwind.config.js
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        // Add other template paths if needed
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('flowbite/plugin')
    ],
}