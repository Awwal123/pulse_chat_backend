<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SendlibService
{
    public function send(
        string $to,
        string $subject,
        string $html
    ): void {
        Http::withToken(config('services.sendlib.api_key'))
            ->post('https://sendlib.samueltuoyo.com/api/send', [
                'to' => $to,
                'from' => config('services.sendlib.from_email'),
                'subject' => $subject,
                'html' => $html,
            ])
            ->throw();
    }
}