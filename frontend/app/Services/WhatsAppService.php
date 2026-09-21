<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneId;
    private string $apiUrl;

    public function __construct()
    {
        $this->token   = config('whatsapp.token');
        $this->phoneId = config('whatsapp.phone_id');
        $this->apiUrl  = "https://graph.facebook.com/v19.0/{$this->phoneId}/messages";
    }

    public function sendOtp(string $phone, string $code): bool
    {
        $phone = $this->formatPhone($phone);

        $response = Http::withToken($this->token)
            ->post($this->apiUrl, [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => [
                    'body' => "Tu codigo de verificacion para Mi Chuzito es: *{$code}*\n\nEste codigo expira en 10 minutos.\nNo lo compartas con nadie."
                ],
            ]);

        if (!$response->successful()) {
            Log::error('WhatsApp OTP error', [
                'phone'    => $phone,
                'response' => $response->json(),
            ]);
            return false;
        }

        Log::info('WhatsApp OTP enviado', ['phone' => $phone]);
        return true;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10 && !str_starts_with($phone, '57')) {
            $phone = '57' . $phone;
        }

        return $phone;
    }
}