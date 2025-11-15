<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class RankMath {
    const TAG = '[TMW-SEO-RM]';

    public static function boot(): void {
        add_filter('rank_math/frontend/title', [__CLASS__, 'filter_title'], 10, 2);
        add_filter('rank_math/frontend/description', [__CLASS__, 'filter_description'], 10, 2);
    }

    public static function filter_title($title, $object_id) {
        if (!$title && $object_id) {
            $stored = get_post_meta($object_id, 'rank_math_title', true);
            if ($stored) {
                $title = $stored;
            }
        }
        return $title;
    }

    public static function filter_description($description, $object_id) {
        if (!$description && $object_id) {
            $stored = get_post_meta($object_id, 'rank_math_description', true);
            if ($stored) {
                $description = $stored;
            }
        }
        return $description;
    }
}
