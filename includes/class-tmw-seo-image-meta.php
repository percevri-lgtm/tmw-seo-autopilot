<?php
/**
 * TMW SEO – Image meta bootstrap
 *
 * Hooks into featured image changes for videos and models
 * and calls the media\Image_Meta_Generator helper.
 */

namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

use TMW_SEO\Media\Image_Meta_Generator;

class Image_Meta {

    public static function boot(): void {
        if (!is_admin()) {
            return;
        }

        // When an attachment is uploaded from the media modal.
        add_action('add_attachment', [__CLASS__, 'on_attachment_added'], 20);

        // When a featured image is set or changed.
        add_action('set_post_thumbnail', [__CLASS__, 'on_set_post_thumbnail'], 20, 3);

        // When a thumbnail meta value is written directly.
        add_action('added_post_meta', [__CLASS__, 'on_thumbnail_meta_added'], 20, 4);
        add_action('updated_post_meta', [__CLASS__, 'on_thumbnail_meta_updated'], 20, 4);

        // Safety net: when a video/model post is saved and already has a thumbnail.
        add_action('save_post', [__CLASS__, 'on_save_post_with_thumbnail'], 20, 3);
    }

    /**
     * Fires when an attachment is created (e.g. uploaded via the media modal).
     *
     * @param int $attachment_id
     */
    public static function on_attachment_added(int $attachment_id): void {
        $attachment = get_post($attachment_id);
        if (!$attachment instanceof \WP_Post || 'attachment' !== $attachment->post_type) {
            return;
        }

        $parent_post = self::resolve_parent_post($attachment);
        if (!$parent_post instanceof \WP_Post) {
            return;
        }

        self::maybe_generate_meta($attachment_id, $parent_post, 'add_attachment');
    }

    /**
     * Fires when a thumbnail is selected on the edit screen.
     *
     * @param int $post_id
     * @param int $thumb_id
     * @param mixed $meta
     */
    public static function on_set_post_thumbnail(int $post_id, int $thumb_id, $meta = null): void {
        $post = get_post($post_id);
        if (!$post instanceof \WP_Post) {
            return;
        }

        self::maybe_generate_meta($thumb_id, $post, 'set_post_thumbnail');
    }

    /**
     * Backup hook – if the importer sets the thumbnail before we’re loaded.
     *
     * @param int      $post_id
     * @param \WP_Post $post
     * @param bool     $update
     */
    public static function on_save_post_with_thumbnail(int $post_id, \WP_Post $post, bool $update): void {
        if (!self::is_supported_post($post)) {
            return;
        }

        if (self::is_ignored_save($post_id)) {
            return;
        }

        $thumb_id = (int) get_post_thumbnail_id($post_id);
        if ($thumb_id > 0) {
            self::maybe_generate_meta($thumb_id, $post, 'save_post');
        }
    }

    /**
     * Fires when _thumbnail_id is written directly.
     */
    public static function on_thumbnail_meta_added(int $meta_id, int $object_id, string $meta_key, $meta_value): void {
        self::on_thumbnail_meta_changed($object_id, $meta_key, $meta_value, 'added_post_meta');
    }

    /**
     * Fires when _thumbnail_id is updated directly.
     */
    public static function on_thumbnail_meta_updated(int $meta_id, int $object_id, string $meta_key, $meta_value): void {
        self::on_thumbnail_meta_changed($object_id, $meta_key, $meta_value, 'updated_post_meta');
    }

    /**
     * Shared meta handler.
     */
    protected static function on_thumbnail_meta_changed(int $object_id, string $meta_key, $meta_value, string $source): void {
        if ($meta_key !== '_thumbnail_id') {
            return;
        }

        $post_id  = (int) $object_id;
        $thumb_id = (int) $meta_value;

        $post = get_post($post_id);
        if (!$post instanceof \WP_Post || $thumb_id <= 0) {
            return;
        }

        if (!self::is_supported_post($post) || self::is_ignored_save($post_id)) {
            return;
        }

        self::maybe_generate_meta($thumb_id, $post, $source);
    }

    /**
     * Generate meta when safe for the provided attachment/post combo.
     */
    protected static function maybe_generate_meta(int $attachment_id, \WP_Post $parent_post, string $source): void {
        if (!self::is_supported_post($parent_post)) {
            return;
        }

        // Only proceed for images.
        $attachment = get_post($attachment_id);
        if (!$attachment instanceof \WP_Post || 'attachment' !== $attachment->post_type || !wp_attachment_is_image($attachment)) {
            return;
        }

        if (defined('TMW_DEBUG') && TMW_DEBUG) {
            error_log('[TMW-IMAGE] generating meta from ' . $source . " for post {$parent_post->ID} / attachment {$attachment_id}");
        }

        Image_Meta_Generator::generate_for_featured_image($attachment_id, $parent_post);
    }

    /**
     * Retrieve a parent post for an attachment upload.
     */
    protected static function resolve_parent_post(\WP_Post $attachment): ?\WP_Post {
        $parent_id = 0;

        if (!empty($attachment->post_parent)) {
            $parent_id = (int) $attachment->post_parent;
        }

        if (!$parent_id && isset($_REQUEST['post_id'])) {
            $parent_id = (int) $_REQUEST['post_id'];
        }

        if ($parent_id <= 0) {
            return null;
        }

        $post = get_post($parent_id);

        return $post instanceof \WP_Post ? $post : null;
    }

    /**
     * Whether the current save context should be skipped (autosaves, revisions, cron/AJAX).
     */
    protected static function is_ignored_save(int $post_id = 0): bool {
        return (
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
            wp_is_post_autosave($post_id) ||
            wp_is_post_revision($post_id) ||
            (defined('DOING_CRON') && DOING_CRON)
        );
    }

    /**
     * Checks whether the post type belongs to our video/model flows.
     */
    protected static function is_supported_post(\WP_Post $post): bool {
        return Media::supports_post_type($post->post_type);
    }
}
