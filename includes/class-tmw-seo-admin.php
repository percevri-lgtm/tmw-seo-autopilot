<?php
namespace TMW_SEO;

if (!defined('ABSPATH')) {
    exit;
}

class Admin {
    const TAG = '[TMW-SEO-ADMIN]';

    public static function boot() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
    }

    public static function register_menu(): void {
        add_management_page(
            'TMW SEO Autopilot',
            'TMW SEO Autopilot',
            'manage_options',
            'tmw-seo-autopilot',
            [__CLASS__, 'render_tools']
        );
    }

    public static function render_tools(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'tmw-seo-autopilot'));
        }
        $notice = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('tmw_seo_manual_generate', 'tmw_seo_nonce')) {
            $video_id = isset($_POST['tmw_seo_video_id']) ? absint($_POST['tmw_seo_video_id']) : 0;
            $strategy = isset($_POST['tmw_seo_strategy']) && $_POST['tmw_seo_strategy'] === 'openai' ? 'openai' : 'template';
            if ($video_id) {
                $result = Core::generate_for_video($video_id, ['strategy' => $strategy]);
                if (!empty($result['ok'])) {
                    $notice = sprintf(__('Generation successful for video #%d.', 'tmw-seo-autopilot'), $video_id);
                } else {
                    $notice = sprintf(__('Generation failed for video #%d: %s', 'tmw-seo-autopilot'), $video_id, $result['message'] ?? '');
                }
            } else {
                $notice = __('Please provide a valid Video ID.', 'tmw-seo-autopilot');
            }
        }
        echo '<div class="wrap">';
        echo '<h1>TMW SEO Autopilot</h1>';
        if ($notice) {
            echo '<div class="notice notice-info"><p>' . esc_html($notice) . '</p></div>';
        }
        echo '<form method="post">';
        wp_nonce_field('tmw_seo_manual_generate', 'tmw_seo_nonce');
        echo '<table class="form-table">';
        echo '<tr><th scope="row"><label for="tmw_seo_video_id">' . esc_html__('Video ID', 'tmw-seo-autopilot') . '</label></th>';
        echo '<td><input type="number" name="tmw_seo_video_id" id="tmw_seo_video_id" class="regular-text" required></td></tr>';
        echo '<tr><th scope="row">' . esc_html__('Strategy', 'tmw-seo-autopilot') . '</th>';
        echo '<td><select name="tmw_seo_strategy"><option value="template">' . esc_html__('Template (offline)', 'tmw-seo-autopilot') . '</option>';
        echo '<option value="openai">' . esc_html__('OpenAI (if configured)', 'tmw-seo-autopilot') . '</option></select></td></tr>';
        echo '</table>';
        submit_button(__('Generate Now', 'tmw-seo-autopilot'));
        echo '</form>';

        echo '<hr><h2>' . esc_html__('Integration Settings (read-only)', 'tmw-seo-autopilot') . '</h2>';
        echo '<table class="widefat"><tbody>';
        echo '<tr><th>' . esc_html__('Brand order', 'tmw-seo-autopilot') . '</th><td>' . esc_html(implode(' → ', Core::brand_order())) . '</td></tr>';
        echo '<tr><th>' . esc_html__('SUBAFF pattern', 'tmw-seo-autopilot') . '</th><td>' . esc_html(Core::subaff_pattern()) . '</td></tr>';
        $og = Core::default_og();
        echo '<tr><th>' . esc_html__('Default OG image', 'tmw-seo-autopilot') . '</th><td>' . ($og ? '<code>' . esc_url($og) . '</code>' : '<em>' . esc_html__('none', 'tmw-seo-autopilot') . '</em>') . '</td></tr>';
        echo '</tbody></table>';

        echo '</div>';
    }
}
