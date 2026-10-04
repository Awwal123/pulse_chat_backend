<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendMessage(string $chatId, string $message): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => config('services.openwa.api_key'),
            'Content-Type' => 'application/json',
        ])->post(
            config('services.openwa.base_url')
                .'/api/sessions/'
                .config('services.openwa.session_id')
                .'/messages/send-text',
            [
                'chatId' => $chatId,
                'text' => $message,
            ]
        );

        if ($response->failed()) {
            throw new \Exception(
                'Failed to send WhatsApp message: '.$response->body()
            );
        }

        return $response->json();
    }
}