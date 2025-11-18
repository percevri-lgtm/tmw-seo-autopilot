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

        $thumb_id = self::resolve_video_thumbnail_id($post);
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

        $focus_keyword = self::focus_keyword_for_video($video);
        $extras = self::extra_keywords_for_video($video);
        $primary = $focus_keyword !== '' ? $focus_keyword : wp_strip_all_tags($video->post_title);
        $secondary = $extras[0] ?? (Core::brand_order()[0] ?? '');

        $alt_text = $primary;
        if ($secondary && stripos($primary, $secondary) === false) {
            $alt_text = trim($primary . ' — ' . $secondary);
        }

        $seo_title = trim((string) get_post_meta($video->ID, 'rank_math_title', true));
        $clean_video_title = self::clean_video_title($seo_title !== '' ? $seo_title : $video->post_title);
        $attachment_title = $clean_video_title !== '' ? $clean_video_title : $alt_text;

        $caption = $focus_keyword !== ''
            ? sprintf('%s preview thumbnail for this live video.', $focus_keyword)
            : sprintf('%s preview thumbnail.', $clean_video_title ?: 'Video');

        $descriptor = $extras[0] ?? strtolower(Core::brand_order()[0] ?? '');
        $descriptor = $descriptor ?: 'webcam highlight';
        $description = $focus_keyword !== ''
            ? sprintf(
                '%s promo thumbnail showing a %s vibe. Short, PG-13 highlight reel preview.',
                $focus_keyword,
                $descriptor
            )
            : sprintf(
                'Promo thumbnail from %s. Short highlight reel preview.',
                $clean_video_title ?: 'this live video'
            );

        $updates = ['ID' => $thumb_id];

        $current_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        if ('' === trim((string) $current_alt) && $alt_text !== '') {
            update_post_meta($thumb_id, '_wp_attachment_image_alt', $alt_text);
        }

        if ('' === trim((string) $attachment->post_title) && $attachment_title !== '') {
            $updates['post_title'] = $attachment_title;
        }
        if ('' === trim((string) $attachment->post_excerpt) && $caption !== '') {
            $updates['post_excerpt'] = $caption;
        }
        if ('' === trim((string) $attachment->post_content) && $description !== '') {
            $updates['post_content'] = $description;
        }

        if (count($updates) > 1) {
            wp_update_post($updates);
        }

        error_log(self::TAG . " filled thumbnail meta for video {$video->ID} / attachment {$thumb_id}");
    }

    private static function resolve_video_thumbnail_id(\WP_Post $video): int {
        $thumb_id = (int) get_post_thumbnail_id($video->ID);
        if ($thumb_id) {
            return $thumb_id;
        }

        foreach (self::LIVEJASMIN_THUMB_META_KEYS as $key) {
            $raw = get_post_meta($video->ID, $key, true);
            if (empty($raw)) {
                continue;
            }

            $candidate_id = 0;
            $candidate_url = '';

            if (is_array($raw)) {
                $candidate_id = isset($raw['id']) ? (int) $raw['id'] : 0;
                $candidate_url = is_string($raw['url'] ?? '') ? $raw['url'] : '';
            } elseif (is_numeric($raw)) {
                $candidate_id = (int) $raw;
            } elseif (is_string($raw)) {
                $candidate_url = $raw;
            }

            if ($candidate_id > 0) {
                return $candidate_id;
            }

            if ($candidate_url !== '') {
                $resolved = attachment_url_to_postid($candidate_url);
                if ($resolved) {
                    return (int) $resolved;
                }
            }
        }

        return 0;
    }

    private static function focus_keyword_for_video(\WP_Post $video): string {
        $raw = trim((string) get_post_meta($video->ID, 'rank_math_focus_keyword', true));
        if ($raw !== '') {
            $parts = array_filter(array_map('trim', explode(',', $raw)));
            if (!empty($parts)) {
                return $parts[0];
            }
            return $raw;
        }

        $name = Core::detect_model_name_from_video($video);
        if ($name === '') {
            $name = wp_strip_all_tags($video->post_title);
        }

        return Core::video_focus($name);
    }

    private static function extra_keywords_for_video(\WP_Post $video): array {
        $meta = get_post_meta($video->ID, '_tmwseo_video_tag_keywords', true);
        if (is_array($meta) && !empty($meta)) {
            return array_values(array_filter(array_map('sanitize_text_field', $meta)));
        }

        $looks = Core::first_looks($video->ID);
        return Core::safe_model_tag_keywords($looks);
    }

    private static function clean_video_title(string $title): string {
        $clean = wp_strip_all_tags($title);
        $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $patterns = [
            'Top Models Webcam',
            'Top-Models.Webcam',
            $site,
        ];
        foreach ($patterns as $pattern) {
            if ($pattern) {
                $clean = trim(str_ireplace($pattern, '', $clean));
            }
        }

        return trim(preg_replace('#\s+-\s+$#', '', $clean));
    }
}
