const mix = require('laravel-mix');

mix.setPublicPath('src/static');

mix.js('src/app.js', '').vue();