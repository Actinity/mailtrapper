<?php
namespace Actinity\Mailtrapper;

use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;

class MailtrapperMailServiceProvider
    extends MailServiceProvider
{
    public function registerIlluminateMailer()
    {
        $this->app->singleton('mail.manager',function($app) {
            return new MailtrapperManager($app);
        });

        $this->app->bind('mailer', function ($app) {
            return $app->make('mail.manager')->mailer();
        });
    }
}
