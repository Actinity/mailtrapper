<?php
namespace Actinity\Mailtrapper;

use Illuminate\Support\Facades\DB;

class Transport
    extends \Illuminate\Mail\Transport\Transport
{
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        DB::table('mailtrapper')
            ->insert([
                'subject' => $message->getSubject() ?: 'No subject',
                'body' => $message->getBody(),
                'to' => implode(",",array_keys($message->getTo())),
                'from' => implode(",",$message->getFrom()),
                'created_at' => now(),
            ]);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}
