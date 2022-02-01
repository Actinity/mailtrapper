<?php

namespace Actinity\Mailtrapper\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class MailtrapperController extends Controller
{
	public function js()
	{
		$content = file_get_contents(__DIR__."/../../static/app.js");
		return response()->make($content,200,['Content-Type' => 'text/javascript']);
	}

	public function index()
	{
		if(config('mail.default') !== 'trapper') { abort(403); }

		return [
			'can_empty' => auth()->user() && auth()->user()->can('mailtrapper:empty'),
			'messages' => DB::table('mailtrapper')
				->select([
					'id',
					DB::raw('unix_timestamp(created_at) as created_at'),
					'subject',
					'from',
					'to'
				])
				->take(20)
				->orderBy('created_at','desc')
				->get()
		];
	}

	public function show($id)
	{
		if(config('mail.default') !== 'trapper') { abort(403); }

		$message = DB::table('mailtrapper')
			->where('id','=',$id)
			->first();

		if(!$message) {
			abort(404);
		}

		$snippet = '
        <script>window.onload=function() {window.parent.postMessage({mailtrapperId:'.$id.',mailtrapperHeight:document.body.clientHeight},"*")};</script>
        ';

		$content = str_replace('</body>',$snippet.'</body>',$message->body);

		return response()->make($content,200,['X-Mailtrapper' => 'mailtrapper']);
	}

	public function deleteAll()
	{
		if(!auth()->user() || !auth()->user()->can('mailtrapper:empty')) {
			abort(403);
		}

		DB::table('mailtrapper')->truncate();
		return response()->noContent();

	}
}
