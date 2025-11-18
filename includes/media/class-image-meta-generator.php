<?php
namespace TMW_SEO\Media;

use TMW_SEO\Core;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

class Image_Meta_Generator {
    private const SIGNATURE_META_KEY = '_tmwseo_image_meta_signature';
    private const SIGNATURE_VERSION  = 1;

    /**
     * Generate and update image meta for a video's featured image.
     */
    public static function maybe_update_featured_image_meta(int $video_post_id, int $attachment_id = 0): void {
        $video = get_post($video_post_id);
        if (!$video instanceof WP_Post || !in_array($video->post_type, Core::video_post_types(), true)) {
            return;
        }

        $thumb_id = $attachment_id ?: (int) get_post_thumbnail_id($video->ID);
        if (!$thumb_id) {
            return;
        }

        $attachment = get_post($thumb_id);
        if (!$attachment instanceof WP_Post || 'attachment' !== $attachment->post_type || !wp_attachment_is_image($attachment)) {
            return;
        }

        $generated = self::generate_for_video($video);
        if (empty(array_filter($generated))) {
            return;
        }

        $signature = self::read_signature($thumb_id);
        $updates   = ['ID' => $thumb_id];
        $applied   = [];

        $current_alt = trim((string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true));
        if (self::should_update_field($current_alt, $generated['alt'], $signature, 'alt')) {
            update_post_meta($thumb_id, '_wp_attachment_image_alt', $generated['alt']);
            $applied['alt'] = $generated['alt'];
        }

        if (self::should_update_field($attachment->post_title, $generated['title'], $signature, 'title')) {
            $updates['post_title'] = $generated['title'];
            $applied['title']      = $generated['title'];
        }

        if (self::should_update_field($attachment->post_excerpt, $generated['caption'], $signature, 'caption')) {
            $updates['post_excerpt'] = $generated['caption'];
            $applied['caption']      = $generated['caption'];
        }

        if (self::should_update_field($attachment->post_content, $generated['description'], $signature, 'description')) {
            $updates['post_content'] = $generated['description'];
            $applied['description']  = $generated['description'];
        }

        if (count($updates) > 1) {
            wp_update_post($updates);
        }

        if (!empty($applied)) {
            self::write_signature($thumb_id, $signature, $applied);

            if (defined('TMW_DEBUG') && TMW_DEBUG) {
                error_log(
                    sprintf(
                        '[TMW-IMG-SEO] video#%d thumb#%d fields=%s',
                        $video->ID,
                        $thumb_id,
                        implode(',', array_keys($applied))
                    )
                );
            }
        }
    }

    /**
     * Build the text fragments for a video's featured image.
     */
    public static function generate_for_video(WP_Post $video): array {
        $focus_keyword = self::detect_focus_keyword($video);
        $model_name    = self::detect_model_name($video, $focus_keyword);
        $brand         = Core::brand_order()[0] ?? 'Top-Models.Webcam';

        $alt          = self::build_alt($focus_keyword, $model_name);
        $title        = self::build_title($model_name, $focus_keyword);
        $caption      = self::build_caption($model_name, $focus_keyword, $brand);
        $description  = self::build_description($model_name, $focus_keyword, $brand);

        return [
            'alt'         => $alt,
            'title'       => $title,
            'caption'     => $caption,
            'description' => $description,
        ];
    }

    private static function detect_focus_keyword(WP_Post $video): string {
        $raw = trim((string) get_post_meta($video->ID, 'rank_math_focus_keyword', true));
        if ($raw !== '') {
            return sanitize_text_field($raw);
        }

        $name = Core::detect_model_name_from_video($video);
        if ($name === '') {
            $name = wp_strip_all_tags($video->post_title);
        }

        return Core::video_focus($name);
    }

    private static function detect_model_name(WP_Post $video, string $focus_keyword): string {
        $model_terms = taxonomy_exists('models')
            ? wp_get_post_terms($video->ID, 'models', ['fields' => 'names'])
            : [];

        if (!is_wp_error($model_terms) && !empty($model_terms)) {
            $candidate = trim((string) ($model_terms[0] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        $focus = $focus_keyword;
        if ($focus !== '') {
            $focus = preg_replace('/\s*\([^\)]*\)\s*$/u', '', $focus);
            $parts = preg_split('/\s*[—–\-]\s*/u', (string) $focus);
            if (!empty($parts[0])) {
                $focus = trim((string) $parts[0]);
            }
            if ($focus !== '') {
                return $focus;
            }
        }

        return wp_strip_all_tags($video->post_title);
    }

    private static function build_alt(string $focus_keyword, string $model_name): string {
        if ($focus_keyword !== '') {
            return $focus_keyword;
        }

        if ($model_name !== '') {
            return trim(sprintf('%s webcam highlight thumbnail', $model_name));
        }

        return 'Video highlight thumbnail';
    }

    private static function build_title(string $model_name, string $focus_keyword): string {
        if ($model_name !== '') {
            return sanitize_text_field(sprintf('%s in calm studio lighting', $model_name));
        }

        if ($focus_keyword !== '') {
            return sanitize_text_field(sprintf('%s preview thumbnail', $focus_keyword));
        }

        return 'Webcam highlight preview image';
    }

    private static function build_caption(string $model_name, string $focus_keyword, string $brand): string {
        $subject = $model_name !== '' ? $model_name : ($focus_keyword !== '' ? $focus_keyword : 'This model');

        return sanitize_text_field(
            sprintf('%s on %s with friendly live chat vibes.', $subject, $brand)
        );
    }

    private static function build_description(string $model_name, string $focus_keyword, string $brand): string {
        $subject = $model_name !== '' ? $model_name : ($focus_keyword !== '' ? $focus_keyword : 'This video');
        $focus   = $focus_keyword !== '' ? $focus_keyword : $subject;

        $sentences = [];
        $sentences[] = sprintf(
            '%s appears in this highlight thumbnail from a live chat reel on %s.',
            $subject,
            $brand
        );
        $sentences[] = sprintf(
            'Friendly, relaxed mood with soft lighting — %s stays the focus.',
            $focus
        );

        return implode(' ', array_map('sanitize_text_field', $sentences));
    }

    private static function should_update_field(string $current, string $generated, array $signature, string $field): bool {
        if ($generated === '') {
            return false;
        }

        $current = trim($current);
        if ($current === '') {
            return true;
        }

        return self::is_generated_value($current, $signature, $field);
    }

    private static function read_signature(int $attachment_id): array {
        $raw = get_post_meta($attachment_id, self::SIGNATURE_META_KEY, true);
        return is_array($raw) ? $raw : [];
    }

    private static function write_signature(int $attachment_id, array $existing, array $applied): void {
        $values = isset($existing['values']) && is_array($existing['values']) ? $existing['values'] : [];
        foreach ($applied as $field => $value) {
            $values[$field] = $value;
        }

        $payload = [
            'version'  => self::SIGNATURE_VERSION,
            'values'   => $values,
            'updated'  => current_time('mysql'),
        ];

        update_post_meta($attachment_id, self::SIGNATURE_META_KEY, $payload);
    }

    private static function is_generated_value(string $current, array $signature, string $field): bool {
        if (empty($signature['values'][$field])) {
            return false;
        }

        return trim((string) $signature['values'][$field]) === trim($current);
    }
}
