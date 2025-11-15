<?php
namespace TMW_SEO\Providers;

use TMW_SEO\Core;

if (!defined('ABSPATH')) {
    exit;
}

class OpenAI {
    const TAG = '[TMW-SEO-OAI]';

    protected $fallback;

    public function __construct() {
        $this->fallback = new Template();
    }

    public static function is_enabled(): bool {
        return (bool) (defined('TMW_SEO_OPENAI_KEY') && TMW_SEO_OPENAI_KEY);
    }

    public function generate_video(array $ctx): array {
        $payload = $this->request('video', $ctx);
        return $payload ?: $this->fallback->generate_video($ctx);
    }

    public function generate_model(array $ctx): array {
        $payload = $this->request('model', $ctx);
        return $payload ?: $this->fallback->generate_model($ctx);
    }

    protected function request(string $type, array $ctx): ?array {
        if (!self::is_enabled()) {
            return null;
        }
        $body = [
            'model' => defined('TMW_SEO_OPENAI_MODEL') ? TMW_SEO_OPENAI_MODEL : 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a WordPress SEO generator returning structured JSON.'],
                ['role' => 'user', 'content' => wp_json_encode(['type' => $type, 'ctx' => $ctx])],
            ],
            'temperature' => 0.3,
        ];
        $resp = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . TMW_SEO_OPENAI_KEY,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 45,
        ]);
        if (is_wp_error($resp)) {
            error_log(self::TAG . ' request error: ' . $resp->get_error_message());
            return null;
        }
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        $decoded = json_decode($content, true);
        if (empty($decoded['title'])) {
            error_log(self::TAG . ' invalid response; falling back');
            return null;
        }
        $decoded['keywords'] = array_slice((array) ($decoded['keywords'] ?? []), 0, 5);
        $decoded['content'] = wp_kses_post($decoded['content'] ?? '');
        return $decoded;
    }
}
