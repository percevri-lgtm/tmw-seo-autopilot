<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

class OpenAI {
    public static function is_enabled(): bool {
        return defined('TMW_SEO_OPENAI') || defined('OPENAI_API_KEY');
    }
    private function api_key(): string {
        return defined('TMW_SEO_OPENAI') ? TMW_SEO_OPENAI : (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
    }

    public function generate_video(array $ctx): array {
        $raw = $this->generate([
            'name' => $ctx['name'],
            'site' => $ctx['site'],
            'primary' => $ctx['hook'],
            'looks' => $ctx['looks'],
        ], 'video');
        if (empty($raw['title'])) {
            return (new Template())->generate_video($ctx);
        }
        return [
            'title' => $raw['title'],
            'meta' => $raw['meta'],
            'keywords' => array_merge([$ctx['focus']], array_slice($ctx['extras'], 0, 4)),
            'content' => $raw['content'],
        ];
    }

    public function generate_model(array $ctx): array {
        $raw = $this->generate([
            'name' => $ctx['name'],
            'site' => $ctx['site'],
            'primary' => $ctx['focus'],
            'looks' => $ctx['looks'],
        ], 'model');
        if (empty($raw['title'])) {
            return (new Template())->generate_model($ctx);
        }
        return [
            'title' => $raw['title'],
            'meta' => $raw['meta'],
            'keywords' => array_merge([$ctx['focus']], array_slice($ctx['extras'], 0, 4)),
            'content' => $raw['content'],
        ];
    }

    public function generate(array $ctx, string $type = 'model'): array {
        if (!$this->api_key()) {
            return [];
        }
        $prompt = sprintf(
            "Write concise SEO for a %s page. Name: %s\nSite: %s\nPrimary look: %s\nOther looks: %s\nReturn JSON with keys: title, meta, focus (array), content (HTML, 300-500 words with Intro, Bio, FAQ).",
            $type,
            $ctx['name'],
            $ctx['site'],
            $ctx['primary'],
            implode(', ', (array) $ctx['looks'])
        );

        $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key(),
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 25,
            'body' => wp_json_encode([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a concise SEO writer. Keep content safe for work.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.5,
            ]),
        ]);

        if (is_wp_error($res)) {
            error_log('[TMW-SEO-GEN] OpenAI error: ' . $res->get_error_message());
            return [];
        }

        $json = json_decode(wp_remote_retrieve_body($res), true);
        $text = $json['choices'][0]['message']['content'] ?? '';
        $payload = json_decode($text, true);
        if (!is_array($payload) || empty($payload['title'])) {
            return [];
        }
        $payload['title'] = sanitize_text_field($payload['title']);
        $payload['meta']  = sanitize_text_field($payload['meta']);
        $payload['focus'] = array_map('sanitize_text_field', (array)$payload['focus']);
        $payload['content'] = wp_kses_post($payload['content']);

        return $payload;
    }
}
