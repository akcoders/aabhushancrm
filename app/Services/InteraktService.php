<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InteraktService
{
    public function configured(): bool { return filled(config('integrations.interakt.api_key')); }

    private function client(): PendingRequest
    {
        abort_unless($this->configured(), 422, 'Interakt is not configured. Add INTERAKT_API_KEY on the server.');
        return Http::baseUrl(rtrim(config('integrations.interakt.base_url'), '/'))
            ->withHeaders(['Authorization' => 'Basic '.config('integrations.interakt.api_key')])
            ->acceptJson()->asJson()->timeout(20)->retry(2, 300);
    }

    public function sendTemplate(string $mobile, string $template, string $language = 'en', array $bodyValues = [], ?string $mediaUrl = null, ?string $callback = null): array
    {
        $digits = preg_replace('/\D+/', '', $mobile);
        $countryCode = str_starts_with($digits, '91') ? '+91' : '+'.substr($digits, 0, max(1, strlen($digits) - 10));
        $phone = str_starts_with($digits, '91') ? substr($digits, 2) : substr($digits, -10);
        $templateData = ['name' => $template, 'languageCode' => $language, 'bodyValues' => array_values($bodyValues)];
        if ($mediaUrl) $templateData['headerValues'] = [$mediaUrl];
        $response = $this->client()->post('/message/', [
            'countryCode' => $countryCode, 'phoneNumber' => $phone, 'type' => 'Template',
            'callbackData' => $callback, 'template' => $templateData,
        ]);
        if ($response->failed()) throw new RuntimeException($response->json('message') ?: $response->body());
        return $response->json();
    }
}
