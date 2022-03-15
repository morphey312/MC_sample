const mix = require('laravel-mix');
const config = require('./webpack.config');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/voip.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .version();

mix.js('resources/js/api-sw.js', 'public');

mix.js('resources/js/eh-login.js', 'public/js');

mix.webpackConfig(config);
