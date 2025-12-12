const mix = require('laravel-mix');
const path = require('path');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

  mix.ts('resources/js/main.ts', 'public/js')
  .vue({ version: 3 })
  .postCss('resources/css/app.css', 'public/css', [
      //
  ])
  .sourceMaps();

 // mix.browserSync('http://localhost/');

  mix.webpackConfig(module.exports = {
      resolve: {
        alias: {
          '@/pages': path.resolve(__dirname, './resources/js/pages'),
          '@/assets': path.resolve(__dirname, './resources/js/assets'),
        },
      },
    });
  
    
  mix.options({
      hmrOptions: {
          host: 'localhost',
          port: 8000
      }
  })  
