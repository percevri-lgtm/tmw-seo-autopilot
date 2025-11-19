<?php
namespace TMW_SEO;

if ( ! defined('ABSPATH') ) exit;

class Image_Meta {

    public static function boot() {
        // Runs whenever the edit screen loads
        add_action( 'edit_form_after_title', [ __CLASS__, 'auto_fill_video_image_meta' ] );
    }

    public static function auto_fill_video_image_meta( $post ) {

        // Only video post type
        if ( $post->post_type !== 'video' ) {
            return;
        }

        $thumb_id = get_post_thumbnail_id( $post->ID );
        if ( ! $thumb_id ) {
            return;
        }

        // Focus keyword from RankMath
        $focus = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );
        if ( ! $focus ) {
            $focus = $post->post_title;
        }

        // Model name (taxonomy: models)
        $terms = wp_get_post_terms( $post->ID, 'models' );
        $model_name = (!empty($terms) && ! is_wp_error($terms)) ? $terms[0]->name : null;

        // Existing attachment fields
        $existing_alt     = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
        $existing_title   = get_post_field( 'post_title', $thumb_id );
        $existing_caption = get_post_field( 'post_excerpt', $thumb_id );
        $existing_desc    = get_post_field( 'post_content', $thumb_id );

        // Don’t overwrite manually filled metadata
        if ( $existing_alt && $existing_title && $existing_caption && $existing_desc ) {
            return;
        }

        // Generate values
        $alt_text = $focus;

        $title_text = $model_name
            ? "$model_name featured in a soft-light studio preview"
            : "Webcam model in soft-light studio preview";

        $caption_text = $model_name
            ? "$model_name in a friendly live-chat highlight on Top-Models.Webcam."
            : "Friendly live-chat highlight on Top-Models.Webcam.";

        $desc_text = $model_name
            ? "$model_name appears in this thumbnail, captured from a highlight reel on Top-Models.Webcam. Soft lighting and relaxed pacing."
            : "Thumbnail captured from a webcam highlight reel on Top-Models.Webcam with soft lighting and relaxed pacing.";

        // Save fields ONLY when empty
        if ( empty( $existing_alt ) ) {
            update_post_meta( $thumb_id, '_wp_attachment_image_alt', $alt_text );
        }

        wp_update_post([
            'ID'           => $thumb_id,
            'post_title'   => $existing_title   ?: $title_text,
            'post_excerpt' => $existing_caption ?: $caption_text,
            'post_content' => $existing_desc    ?: $desc_text,
        ]);

        error_log("[TMW-IMG-SEO] Auto-filled image metadata for thumbnail #$thumb_id on video #{$post->ID}");
    }
}
