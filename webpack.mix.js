const mix = require('laravel-mix');

mix.setPublicPath('src/static');

mix.js('resources/app.js', '').vue()
	.sass('resources/sass/app.scss','');