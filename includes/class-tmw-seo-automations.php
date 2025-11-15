<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class Automations {
    const TAG = '[TMW-SEO-AUTO]';

    public static function boot() {
        add_action('save_post', [__CLASS__, 'on_save'], 20, 3);
    }

    public static function on_save(int $post_ID, \WP_Post $post, bool $update) {
        if ($post->post_type !== Core::VIDEO_PT) {
            return;
        }
        if ($post->post_status !== 'publish') {
            return;
        }
        if (did_action('tmw_seo_generated_for_' . $post_ID)) {
            return;
        }
        do_action('tmw_seo_pre_generate', $post_ID);
        $res = Core::generate_for_video($post_ID, ['strategy' => 'template']);
        do_action('tmw_seo_post_generate', $post_ID, $res);
        error_log(self::TAG . " save_post video#$post_ID => " . json_encode(['ok' => $res['ok'] ?? false]));
        do_action('tmw_seo_generated_for_' . $post_ID);
    }
}
