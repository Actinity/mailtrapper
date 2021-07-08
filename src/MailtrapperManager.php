<?php
namespace Actinity\Mailtrapper;

use Illuminate\Mail\MailManager;

class MailtrapperManager
    extends MailManager
{
    protected function createTrapperTransport()
    {
        return new Transport();
    }
}
