const mix = require('laravel-mix')
const path = require('path');

mix.webpackConfig({
  resolve: {
    alias: {
      '@': path.resolve(
        __dirname,
        'resources/assets/js'
      )
    }
  }
})

/*
 |--------------------------------------------------------------------------
 | Admin
 |--------------------------------------------------------------------------
 */

mix.js('resources/assets/js/app.js', 'public/assets/js/').vue()
  .sass('resources/assets/sass/omtbiz.scss', 'public/assets/css/')


if (!mix.inProduction()) {
  mix.webpackConfig({
    devtool: 'source-map'
  }).sourceMaps()
} else {
  mix.version()
}
