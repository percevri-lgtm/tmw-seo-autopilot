<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class Core {
    const TAG = '[TMW-SEO-GEN]';
    const MODEL_PT = 'model';
    const VIDEO_PT = 'video';

    public static function brand_order(): array {
        $order = defined('TMW_SEO_BRAND_ORDER') ? TMW_SEO_BRAND_ORDER : 'jasmin,myc,lpr,joy,lsa';
        return array_values(array_filter(array_map('trim', explode(',', strtolower($order)))));
    }

    public static function subaff_pattern(): string {
        return defined('TMW_SEO_SUBAFF_PATTERN') ? TMW_SEO_SUBAFF_PATTERN : '{slug}-{brand}-{postId}';
    }

    public static function default_og(): string {
        return defined('TMW_SEO_DEFAULT_OG') ? TMW_SEO_DEFAULT_OG : '';
    }

    public static function generate_for_video(int $video_id, array $args = []): array {
        $post = get_post($video_id);
        if (!$post || $post->post_type !== self::VIDEO_PT) {
            return ['ok' => false, 'message' => 'Not a video'];
        }

        $name = self::detect_model_name_from_video($post);
        if (!$name) {
            return ['ok' => false, 'message' => 'No model name detected'];
        }

        $model_id = self::ensure_model_exists($name);
        $ctx_video = self::build_ctx_video($video_id, $model_id, $name, $args);
        $ctx_model = self::build_ctx_model($model_id, $name, $args);

        $provider = (isset($args['strategy']) && $args['strategy'] === 'openai' && Providers\OpenAI::is_enabled())
            ? new Providers\OpenAI()
            : new Providers\Template();

        $payload_video = $provider->generate_video($ctx_video);
        $payload_model = $provider->generate_model($ctx_model);

        self::write_all($video_id, $payload_video, 'VIDEO');
        self::write_all($model_id, $payload_model, 'MODEL');

        self::link_video_to_model($video_id, $model_id);
        self::link_model_to_video($model_id, $video_id);

        error_log(self::TAG . " generated video#$video_id & model#$model_id for {$name}");
        return ['ok' => true, 'video' => $payload_video, 'model' => $payload_model, 'model_id' => $model_id];
    }

    public static function ensure_model_exists(string $name): int {
        $model = get_page_by_title($name, OBJECT, self::MODEL_PT);
        if ($model) {
            return (int) $model->ID;
        }

        $id = wp_insert_post([
            'post_type' => self::MODEL_PT,
            'post_status' => 'publish',
            'post_title' => $name,
            'post_content' => '',
        ]);
        error_log(self::TAG . " created model#$id for {$name}");
        return (int) $id;
    }

    public static function detect_model_name_from_video(\WP_Post $post): string {
        $name = '';
        $name = trim((string) get_post_meta($post->ID, 'awe_model_name', true));
        if (!$name) {
            foreach (['models', 'model'] as $tax) {
                if (taxonomy_exists($tax)) {
                    $names = wp_get_post_terms($post->ID, $tax, ['fields' => 'names']);
                    if (!is_wp_error($names) && !empty($names)) {
                        $name = (string) $names[0];
                        break;
                    }
                }
            }
        }
        if (!$name) {
            $t = wp_strip_all_tags($post->post_title);
            $parts = preg_split('/\s+—\s+|-+/', $t);
            if (!empty($parts[0])) {
                $name = trim($parts[0]);
            }
        }
        return $name;
    }

    public static function affiliate_url(string $name, string $brand = '', int $post_id = 0, string $slug = ''): string {
        $brands = self::brand_order();
        if (!$brand) {
            $brand = $brands[0] ?? 'jasmin';
        }
        $psid = defined('TMW_SEO_PSID') ? TMW_SEO_PSID : 'Topmodels4u';
        $pstool = defined('TMW_SEO_PSTOOL') ? TMW_SEO_PSTOOL : '205_1';
        $prog = defined('TMW_SEO_PSPROGRAM') ? TMW_SEO_PSPROGRAM : 'revs';
        $handle = rawurlencode(preg_replace('/\s+/', '', $name));
        $slug = $slug ?: sanitize_title($name);
        $url = add_query_arg([
            'siteId' => $brand,
            'categoryName' => 'girl',
            'pageName' => 'freechat',
            'performerName' => $handle,
            'prm[psid]' => $psid,
            'prm[pstool]' => $pstool,
            'prm[psprogram]' => $prog,
            'prm[campaign_id]' => '',
            'subAffId' => self::build_subaff($slug, $brand, $post_id),
        ], 'https://ctwmsg.com/');
        return $url;
    }

    protected static function build_subaff(string $slug, string $brand, int $post_id): string
    {
        $pattern = self::subaff_pattern();
        $map = [
            '{slug}' => $slug ?: 'model',
            '{brand}' => $brand ?: 'jasmin',
            '{postId}' => $post_id ? (string) $post_id : '0',
        ];
        return strtr($pattern, $map);
    }

    protected static function build_ctx_video(int $video_id, int $model_id, string $name, array $args): array {
        $looks = self::first_looks($video_id);
        $hook = $looks[0] ?? 'highlights';
        $title = get_the_title($video_id);
        $slug = basename(get_permalink($video_id));
        $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);

        $focus = sprintf('%s %s', $name, $hook);
        $extras = self::pick_extras($name, $looks, ['highlights', 'reel', 'live chat']);
        $cta = self::affiliate_url($name, '', $video_id, $slug);

        return compact('video_id', 'model_id', 'name', 'title', 'slug', 'site', 'hook', 'looks', 'focus', 'extras', 'cta');
    }

    protected static function build_ctx_model(int $model_id, string $name, array $args): array {
        $looks = self::first_looks($model_id);
        $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $slug = basename(get_permalink($model_id));
        $focus = $name;
        $extras = self::pick_extras($name, $looks, ['live chat', 'profile', 'schedule']);
        $cta = self::affiliate_url($name, '', $model_id, $slug);
        return compact('model_id', 'name', 'slug', 'site', 'looks', 'focus', 'extras', 'cta');
    }

    public static function first_looks(int $post_id): array {
        $out = [];
        foreach (['video_tag', 'post_tag', 'models', 'category'] as $tax) {
            if (!taxonomy_exists($tax)) {
                continue;
            }
            $names = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
            if (!is_wp_error($names)) {
                $out = array_merge($out, $names);
            }
        }
        return array_values(array_unique(array_filter($out)));
    }

    protected static function pick_extras(string $name, array $looks, array $defaults): array {
        $choices = array_values(array_unique(array_merge($looks, $defaults)));
        $extras = [];
        foreach ($choices as $c) {
            if (strtolower($c) == strtolower($name)) {
                continue;
            }
            $extras[] = sprintf('%s %s', $name, trim($c));
            if (count($extras) >= 4) {
                break;
            }
        }
        while (count($extras) < 4) {
            $extras[] = $name . ' live chat';
        }
        return $extras;
    }

    protected static function write_all(int $post_id, array $payload, string $type): void {
        if (empty($payload['title'])) {
            return;
        }
        $prev = [
            'rank_math_title' => get_post_meta($post_id, 'rank_math_title', true),
            'rank_math_description' => get_post_meta($post_id, 'rank_math_description', true),
            'rank_math_focus_keyword' => get_post_meta($post_id, 'rank_math_focus_keyword', true),
            'post_content' => get_post($post_id)->post_content,
        ];
        update_post_meta($post_id, "_tmwseo_prev_{$type}", $prev);

        update_post_meta($post_id, 'rank_math_title', sanitize_text_field($payload['title']));
        update_post_meta($post_id, 'rank_math_description', sanitize_text_field($payload['meta']));
        update_post_meta($post_id, 'rank_math_focus_keyword', implode(', ', array_map('sanitize_text_field', $payload['keywords'])));

        update_post_meta($post_id, 'rank_math_facebook_title', sanitize_text_field($payload['title']));
        update_post_meta($post_id, 'rank_math_facebook_description', sanitize_text_field($payload['meta']));
        update_post_meta($post_id, 'rank_math_twitter_title', sanitize_text_field($payload['title']));
        update_post_meta($post_id, 'rank_math_twitter_description', sanitize_text_field($payload['meta']));
        if (Core::default_og()) {
            update_post_meta($post_id, 'rank_math_facebook_image', Core::default_og());
            update_post_meta($post_id, 'rank_math_twitter_image', Core::default_og());
        }

        $start = "<!-- TMWSEO:{$type}:START -->";
        $end = "<!-- TMWSEO:{$type}:END -->";
        $post = get_post($post_id);
        $content = $post->post_content ?: '';
        $content = preg_replace("#{$start}.*?{$end}#s", '', $content);
        $content .= "\n{$start}\n" . $payload['content'] . "\n{$end}\n";
        $content = preg_replace('#<h1>#i', '<h2>', $content);
        $content = preg_replace('#</h1>#i', '</h2>', $content);
        wp_update_post(['ID' => $post_id, 'post_content' => $content]);
    }

    protected static function link_video_to_model(int $video_id, int $model_id): void {
        update_post_meta($video_id, '_tmwseo_model_id', $model_id);
    }

    protected static function link_model_to_video(int $model_id, int $video_id): void {
        update_post_meta($model_id, '_tmwseo_latest_video_id', $video_id);
    }
}
