<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class CLI {
    public static function boot(): void {
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('tmw-seo generate-video', [__CLASS__, 'cmd_generate']);
        }
    }

    public static function cmd_generate($args, $assoc_args): void {
        $video_id = isset($args[0]) ? absint($args[0]) : 0;
        $strategy = isset($assoc_args['strategy']) && $assoc_args['strategy'] === 'openai' ? 'openai' : 'template';
        if (!$video_id) {
            \WP_CLI::error('Provide a video ID.');
            return;
        }
        $result = Core::generate_for_video($video_id, ['strategy' => $strategy]);
        if (!empty($result['ok'])) {
            \WP_CLI::success(sprintf('Generated SEO for video #%d', $video_id));
        } else {
            \WP_CLI::error($result['message'] ?? 'Unknown error');
        }
    }
}
