<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class Media {
    const TAG = '[TMW-SEO-MEDIA]';

    /**
     * WPS LiveJasmin stores the "Main thumbnail" in custom meta fields.
     * Try the common keys here when featured image is missing.
     *
     * @var string[]
     */
    private const LIVEJASMIN_THUMB_META_KEYS = [
        'wps_lj_main_thumbnail',
        'wps_lj_main_thumb',
        'main_thumbnail',
    ];

    public static function boot() {
        add_action('set_post_thumbnail', [__CLASS__, 'on_set_thumb'], 10, 3);
        add_action('add_attachment', [__CLASS__, 'on_add_attachment']);
        add_action('save_post_' . Core::VIDEO_PT, [__CLASS__, 'on_save_video'], 10, 3);
    }

    public static function on_set_thumb($post_id, $thumb_id, $meta = null) {
        $post = get_post($post_id);
        if (!$post instanceof \WP_Post || $post->post_type !== Core::VIDEO_PT) {
            return;
        }
        self::fill_attachment_fields((int) $thumb_id, $post);
        $url = wp_get_attachment_image_url($thumb_id, 'full');
        if ($url) {
            update_post_meta($post_id, 'rank_math_facebook_image', esc_url_raw($url));
            update_post_meta($post_id, 'rank_math_twitter_image', esc_url_raw($url));
        }
        error_log(self::TAG . " set_post_thumbnail post#$post_id thumb#$thumb_id");
    }

    public static function on_add_attachment($att_id) {
        $att = get_post($att_id);
        if ($att && 'attachment' === $att->post_type && empty($att->post_title)) {
            wp_update_post(['ID' => $att_id, 'post_title' => basename($att->guid)]);
        }
    }

    public static function on_save_video($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        if (!is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }
        if (!$post || $post->post_type !== Core::VIDEO_PT) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $thumb_id = (int) get_post_thumbnail_id($post_id);
        if (!$thumb_id) {
            return;
        }

        self::fill_attachment_fields($thumb_id, $post);
    }

    private static function fill_attachment_fields(int $thumb_id, \WP_Post $video): void {
        if (!$thumb_id || !$video instanceof \WP_Post || $video->post_type !== Core::VIDEO_PT) {
            return;
        }

        $attachment = get_post($thumb_id);
        if (!$attachment instanceof \WP_Post || 'attachment' !== $attachment->post_type || !wp_attachment_is_image($attachment)) {
            return;
        }

        // Thumbnail ALT/title/caption handled by TMW_SEO\Image_Meta + Media\Image_Meta_Generator.

        $url = wp_get_attachment_image_url($thumb_id, 'full');
        if ($url) {
            update_post_meta($video->ID, 'rank_math_facebook_image', esc_url_raw($url));
            update_post_meta($video->ID, 'rank_math_twitter_image', esc_url_raw($url));
        }

        if (defined('TMW_DEBUG') && TMW_DEBUG) {
            error_log(self::TAG . " filled thumbnail meta for video {$video->ID} / attachment {$thumb_id}");
        }
    }
}
