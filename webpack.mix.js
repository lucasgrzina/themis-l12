let mix = require('laravel-mix');

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
	mix.js('resources/assets/js/app.js', 'public/js')
   .vue()
   /*.styles([
    'resources/assets/css/bootstrap.min.css',
    'resources/assets/css/AdminLTE.min.css',
    'resources/assets/css/skin-blue-light.css',
	], 'public/css/vendors.css')*/
   .sass('resources/assets/sass/vendors.scss', 'public/css');
   //.sass('resources/assets/sass/app.scss', 'public/css');
