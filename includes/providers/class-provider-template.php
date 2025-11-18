<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

class Template {
    public function generate_video($ctx) {
        return (new VideoTemplate)->generate_video($ctx);
    }
    public function generate_model($ctx) {
        return (new ModelTemplate)->generate_model($ctx);
    }
}
