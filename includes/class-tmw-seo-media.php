<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class Media {
    const TAG = '[TMW-SEO-MEDIA]';

    public static function boot() {
        add_action('set_post_thumbnail', [__CLASS__, 'on_set_thumb'], 10, 3);
        add_action('add_attachment', [__CLASS__, 'on_add_attachment']);
        add_action('save_post_' . Core::VIDEO_PT, [__CLASS__, 'on_save_video'], 10, 3);
    }

    public static function on_set_thumb($post_id, $thumb_id, $meta = null) {
        self::fill_attachment_fields($thumb_id, get_post($post_id));
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
        $thumb_id = get_post_thumbnail_id($post_id);
        if (!$thumb_id) {
            return;
        }
        $focus = trim((string) get_post_meta($post_id, 'rank_math_focus_keyword', true));
        $title = get_the_title($post_id);
        $parts_for_alt = array_filter(array_unique(array_map('trim', [$focus, $title])));
        if (empty($parts_for_alt)) {
            return;
        }
        $alt_text = implode(' — ', $parts_for_alt);
        $attachment = get_post($thumb_id);
        if (!$attachment) {
            return;
        }
        $current_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        if ('' === trim((string) $current_alt)) {
            update_post_meta($thumb_id, '_wp_attachment_image_alt', $alt_text);
        }
        $attachment_title = $alt_text;
        $attachment_caption = $focus !== '' ? sprintf('%s – highlight thumbnail', $focus) : $title;
        $attachment_description = $focus !== ''
            ? sprintf('%s, thumbnail for %s on Top Models Webcam.', $focus, $title)
            : sprintf('Thumbnail image for %s on Top Models Webcam.', $title);
        $update_args = ['ID' => $thumb_id];
        if ('' === trim((string) $attachment->post_title)) {
            $update_args['post_title'] = $attachment_title;
        }
        if ('' === trim((string) $attachment->post_excerpt)) {
            $update_args['post_excerpt'] = $attachment_caption;
        }
        if ('' === trim((string) $attachment->post_content)) {
            $update_args['post_content'] = $attachment_description;
        }
        if (count($update_args) > 1) {
            wp_update_post($update_args);
        }
    }

    protected static function fill_attachment_fields(int $att_id, ?\WP_Post $parent = null) {
        if (!$att_id) {
            return;
        }
        $alt = get_post_meta($att_id, '_wp_attachment_image_alt', true);
        $att = get_post($att_id);
        $title = $att->post_title;
        $caption = $att->post_excerpt;
        $desc = $att->post_content;

        $ctx = self::context_from_parent($parent);
        if (!$alt) {
            update_post_meta($att_id, '_wp_attachment_image_alt', self::limit($ctx['alt'], 120));
        }
        if (!$title) {
            wp_update_post(['ID' => $att_id, 'post_title' => self::limit($ctx['title'], 80)]);
        }
        if (!$caption) {
            wp_update_post(['ID' => $att_id, 'post_excerpt' => self::limit($ctx['caption'], 140)]);
        }
        if (!$desc) {
            wp_update_post(['ID' => $att_id, 'post_content' => self::limit($ctx['description'], 300)]);
        }
    }

    protected static function context_from_parent(?\WP_Post $parent = null): array {
        $name = $parent ? Core::detect_model_name_from_video($parent) : '';
        if ($parent && $parent->post_type === Core::MODEL_PT) {
            $name = $parent->post_title;
            return [
                'alt' => "{$name} portrait — profile",
                'title' => "{$name} — Profile Portrait",
                'caption' => "Featured portrait of {$name} (Top-Models.Webcam).",
                'description' => "Featured image for {$name}'s model profile on Top-Models.Webcam. Used on header and social cards.",
            ];
        }
        $hook = 'highlights';
        if ($parent) {
            $looks = Core::first_looks($parent->ID);
            if (!empty($looks[0])) {
                $hook = sanitize_text_field($looks[0]);
            }
            if (!$name) {
                $name = Core::detect_model_name_from_video($parent);
            }
        }
        $short = $parent ? wp_strip_all_tags($parent->post_title) : 'Video';
        return [
            'alt' => "{$name} {$hook} — video thumbnail",
            'title' => "{$name} — {$short} (Thumbnail)",
            'caption' => "{$name} — {$hook} (short reel).",
            'description' => "Main thumbnail for “{$short}” on Top-Models.Webcam; links to live chat.",
        ];
    }

    protected static function limit(string $s, int $len): string {
        $s = trim($s);
        return (mb_strlen($s) > $len) ? (mb_substr($s, 0, $len - 1) . '…') : $s;
    }
}
