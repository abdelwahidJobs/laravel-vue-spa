const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 2 })
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'), // Changed back to standard
        require('autoprefixer'),
    ]);


mix.browserSync('127.0.0.1:8000');