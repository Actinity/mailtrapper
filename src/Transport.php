<?php
namespace Actinity\Mailtrapper;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class Transport
    implements TransportInterface
{
	public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
		$from = count($message->getFrom()) ? $message->getFrom()[0]->toString() : "Unspecified";
		$to = [];
		if(count($message->getTo())) {
			foreach($message->getTo() as $address) {
				$to[] = $address->getAddress();
			}

			$to = implode(',',$to);
		}

        DB::table('mailtrapper')
            ->insert([
                'subject' => $message->getSubject() ?: 'No subject',
                'body' => $message->getHtmlBody() ?: $message->getTextBody(),
                'to' => $to,
                'from' => $from,
                'created_at' => now(),
            ]);

		return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

	public function __toString(): string
	{
		return 'trapper';
	}
}
