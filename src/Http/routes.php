<?php
use Illuminate\Support\Facades\Route;

Route::group([
	'prefix' => 'mailtrapper-ui',
	'middleware' => ['web',\Actinity\Mailtrapper\Http\Middleware\RequireAuth::class],
	'namespace' => '\Actinity\Mailtrapper\Http\Controllers',
],function() {
	Route::get('mailtrapper.js','MailtrapperController@js');
	Route::get('mailtrapper.css','MailtrapperController@css');

	Route::get('inbox','MailtrapperController@index');
	Route::get('message/{id}','MailtrapperController@show');

	Route::delete('/','MailTrapperController@deleteAll');
});
