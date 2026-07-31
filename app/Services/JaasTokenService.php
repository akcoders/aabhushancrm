<?php

namespace App\Services;

use App\Models\VideoCallSale;
use RuntimeException;

class JaasTokenService
{
    public function configured(): bool
    {
        return filled(config('integrations.jitsi.jaas_app_id'))
            && filled(config('integrations.jitsi.jaas_key_id'))
            && filled($this->privateKey());
    }

    public function joinConfig(VideoCallSale $call, array $participant, bool $moderator): array
    {
        if (! $this->configured()) {
            return [
                'provider' => 'meet-jit-si',
                'configured' => false,
                'domain' => config('integrations.jitsi.domain', 'meet.jit.si'),
                'room_name' => $call->room_name,
                'jwt' => null,
            ];
        }

        $appId = config('integrations.jitsi.jaas_app_id');
        return [
            'provider' => 'jaas',
            'configured' => true,
            'domain' => '8x8.vc',
            'room_name' => $appId.'/'.$call->room_name,
            'jwt' => $this->token($call->room_name, $participant, $moderator),
        ];
    }

    private function token(string $room, array $participant, bool $moderator): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'kid' => config('integrations.jitsi.jaas_key_id'), 'typ' => 'JWT'];
        $payload = [
            'aud' => 'jitsi', 'iss' => 'chat', 'sub' => config('integrations.jitsi.jaas_app_id'),
            'room' => $room, 'nbf' => $now - 10, 'exp' => $now + 7200,
            'context' => [
                'user' => [
                    'id' => (string) ($participant['id'] ?? 'guest'),
                    'name' => $participant['name'] ?? 'Guest customer',
                    'email' => $participant['email'] ?? '',
                    'avatar' => '', 'moderator' => $moderator ? 'true' : 'false',
                ],
                'features' => ['livestreaming' => false, 'recording' => false, 'transcription' => false, 'outbound-call' => false],
                'room' => ['regex' => false],
            ],
        ];
        $unsigned = $this->encode($header).'.'.$this->encode($payload);
        $key = openssl_pkey_get_private($this->privateKey());
        if (! $key || ! openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The configured JaaS private key could not sign a meeting token.');
        }
        return $unsigned.'.'.$this->base64Url($signature);
    }

    private function privateKey(): ?string
    {
        if ($encoded = config('integrations.jitsi.jaas_private_key_base64')) return base64_decode($encoded, true) ?: null;
        if ($path = config('integrations.jitsi.jaas_private_key_path')) {
            $resolved = str_starts_with($path, '/') ? $path : base_path($path);
            return is_readable($resolved) ? file_get_contents($resolved) : null;
        }
        return null;
    }

    private function encode(array $value): string { return $this->base64Url(json_encode($value, JSON_UNESCAPED_SLASHES)); }
    private function base64Url(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
}
