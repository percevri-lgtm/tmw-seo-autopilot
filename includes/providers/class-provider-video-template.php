<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

use TMW_SEO\Core;

class VideoTemplate {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name   = $c['name'];
        $site   = $c['site'] ?: 'Live Cam Stream';
        $brand  = $c['brand'] ?? $site;
        $focus  = trim($c['focus'] ?? Core::video_focus($name));
        $focus  = $focus !== '' ? $focus : $name;

        $fallback_extras = [
            'live cam energy',
            'friendly chat stream',
            'studio highlight reel',
            'slow groove moments',
        ];
        $raw_extras = array_map('trim', $c['extras'] ?? []);
        $extras     = array_slice(array_merge($raw_extras, $fallback_extras), 0, 4);
        $keywords   = array_merge([$focus], $extras);

        $title = sprintf(
            '%s — 5 Captivating %s Highlights & %s Guide',
            $focus,
            $extras[0],
            $extras[1]
        );
        if (mb_strlen($title) > 60) {
            $title = sprintf('%s — 5 Captivating %s Highlights', $focus, $extras[0]);
        }

        $meta = sprintf(
            '%s shows how %s, %s, %s, %s guide lighting cues, pacing, and live chat steps.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );
        $meta_length = mb_strlen($meta);
        if ($meta_length < 150) {
            $meta .= ' Calm lighting notes keep it PG-13.';
            $meta_length = mb_strlen($meta);
        }
        if ($meta_length > 160) {
            $meta = mb_substr($meta, 0, 157) . '...';
        }
        // Editors may adjust the WordPress slug manually if they want to echo extra keywords; this generator leaves permalinks untouched.

        $alt_suggestion = sprintf(
            '%s featuring %s, %s, %s, and %s lighting study',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );
        // ALT suggestion (apply to the featured image or hero still manually): $alt_suggestion
        // Removed static "Video Preview" box – real player is rendered separately.

        $intro_one = sprintf(
            '%s anchors this highlight page, and %s, %s, %s, and %s are introduced immediately so returning viewers understand the editorial plan. The intro frames the footage as a PG-13 walkthrough that respects soft pacing, uses director commentary to place each keyword inside the first beat, and invites fans to treat the article like a friendly guide before pressing play. Lighting plans, wardrobe notes, and countdown cues are summarized in natural language so RankMath registers relevance without sounding robotic.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $intro_two = sprintf(
            'Because %s thrives on structured storytelling, the second opening paragraph explains how %s, %s, %s, and %s resurface later in captions, lower thirds, and chat prompts. It outlines when to look for audio cues, how the crew labels transitions, and why the first ten percent of the copy doubles as a calm orientation for viewers who want guidance before the player begins. Readers learn that the commentary is PG-13 yet detailed enough to inform lighting tweaks and viewer posture.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $blocks   = [];
        $blocks[] = ['p', $intro_one];
        $blocks[] = ['p', $intro_two];

        foreach ($extras as $index => $extra) {
            switch ($index) {
                case 0:
                    $section_paragraphs = [
                        sprintf('Directors rely on a floating crane to show how %s sequences compliment %s during the first slow pan. Neutral backgrounds, supportive LED strips, and mild color grading keep the PG-13 promise while still spotlighting the signature style fans expect. These cues make %s coverage feel curated rather than improvised, and watchers know exactly when the conversation shifts toward wardrobe tips.', $extra, $focus, $extra),
                        sprintf('Editors describe how %s transitions into %s prompts without forcing camera operators to rush focus pulls. The pacing chart labels every pause, reminding viewers to breathe, stretch, and keep the player in full screen so %s observations about hair texture and camera balance feel intimate yet respectful.', $extra, $extras[1], $extra),
                    ];
                    break;
                case 1:
                    $section_paragraphs = [
                        sprintf('%s appears whenever the host shifts into live chat rehearsal, and %s guides the tone with a steady, welcoming cadence. Producers map out countdown clocks, note-taking ideas, and emoji suggestions so the PG-13 mood stays intact while watchers learn how to interact politely, keeping %s mentions natural.', $extra, $focus, $extra),
                        sprintf('Crew notes explain that %s cues are tied to soft chimes in the soundtrack, making it easy to recognize when to open the chat window. The article advises saving favorite lines, practicing posture, and echoing the compliments that %s uses before leaning into the %s finale, ensuring %s cues remain friendly.', $extra, $focus, $extras[3], $extra),
                    ];
                    break;
                case 2:
                    $section_paragraphs = [
                        sprintf('Editors slow the pacing whenever %s moments take over, allowing %s to narrate how the choreography links to each camera cue. The writing highlights transitions, tempo changes, and how the crew keeps the PG-13 promise by emphasizing balance, breath control, and upbeat expressions that make %s sequences memorable.', $extra, $focus, $extra),
                        sprintf('Viewers are encouraged to jot down timestamps where %s slides into softer movements before the soundtrack crescendos again. Tips mention stretching, mirroring easy shoulder rolls, and recognizing when %s re-centers the frame so %s segments never feel rushed.', $extra, $focus, $extra),
                    ];
                    break;
                default:
                    $section_paragraphs = [
                        sprintf('%s coverage focuses on lighting, wardrobe harmony, and the way %s experiments with reflective props without breaking the PG-13 boundary. Detailed descriptions explain how gels, diffusers, and wide primes create the shimmer that fans associate with %s moments.', $extra, $focus, $extra),
                        sprintf('Producers share notes on when %s close-ups transition into wide shots that include %s supporting players. Readers learn how to adjust screen brightness, when to capture screenshots for inspiration, and how %s storytelling blends glamor with calm pacing.', $extra, $extras[2], $extra),
                    ];
                    break;
            }

            $blocks[] = ['h2', sprintf('%s perspective & tips', $extra), ['id' => 'keyword-' . ($index + 1)]];
            foreach ($section_paragraphs as $paragraph) {
                $blocks[] = ['p', $paragraph];
            }
        }

        $blocks[] = ['h3', 'Model profile & internal link'];
        $model_url = !empty($c['model_url']) ? $c['model_url'] : '#';
        $blocks[] = ['raw', sprintf('<p>Visit the dedicated profile for <a href="%s">%s</a> to gather deeper notes on %s plus refreshed looks at %s and %s shots, then bookmark upcoming appearances that sync with this video plan.</p>', esc_url($model_url), esc_html($name), esc_html($focus), esc_html($extras[0]), esc_html($extras[1]))];

        $blocks[] = ['h2', sprintf('%s conclusion & next cues', $focus)];
        $blocks[] = ['p', sprintf('The closing recap reiterates how %s keeps %s, %s, %s, and %s aligned inside a seven-part highlight arc. Viewers are encouraged to replay slow pans, over-the-shoulder reveals, and diffused lighting tests so each cue feels familiar before diving into live interaction.', $focus, $extras[0], $extras[1], $extras[2], $extras[3])];
        $blocks[] = ['p', sprintf('Editors leave gentle reminders so anyone following %s knows when %s, %s, and %s reappear as supporting moods. The guidance reinforces soft transitions, calibrates color temperature notes, and highlights the internal link for fans who want deeper %s lore before the next upload.', $focus, $extras[0], $extras[1], $extras[2], $extras[3])];

        $content    = $this->html($blocks);
        $word_count = str_word_count(strip_tags($content));
        $padding    = [
            sprintf('Behind-the-scenes commentary explains how %s collaborates with %s and %s to keep the pacing steady, why the jib operator holds each glide for at least five seconds, and how the lighting board saves presets so the PG-13 tone never slips. This reflective passage keeps keywords conversational while ensuring the narrative feels like a real director talking shop.', $focus, $extras[0], $extras[1]),
            sprintf('An additional set of viewer tips reminds audiences to listen for the subtle cue where %s signals a switch into %s and %s territory, noting that the script always circles back to %s for continuity. The combination of camera angles, emotional beats, and keyword-friendly narration satisfies both human readers and RankMath scoring.', $focus, $extras[2], $extras[3], $focus),
        ];
        $added_padding = [];

        foreach ($padding as $pad) {
            if ($word_count >= 800) {
                break;
            }
            $blocks[]  = ['p', $pad];
            $added_padding[] = $pad;
            $content   = $this->html($blocks);
            $word_count = str_word_count(strip_tags($content));
        }

        while ($word_count > 1000 && !empty($added_padding)) {
            $last_pad = array_pop($added_padding);
            $last_block = array_pop($blocks);
            if ($last_block[0] !== 'p' || $last_block[1] !== $last_pad) {
                $blocks[] = $last_block;
                break;
            }
            $content   = $this->html($blocks);
            $word_count = str_word_count(strip_tags($content));
        }

        $content = $this->html($blocks);

        return [
            'title'    => $title,
            'meta'     => $meta,
            'keywords' => $keywords,
            'content'  => $content,
        ];
    }

    /* helpers */
    protected function html(array $blocks): string {
        $out = '';
        foreach ($blocks as $b) {
            $tag   = $b[0];
            $txt   = $b[1] ?? '';
            $attrs = $b[2] ?? [];
            if ($tag === 'raw') {
                $out .= $txt;
                continue;
            }
            $attr_html = '';
            foreach ($attrs as $k => $v) {
                $attr_html .= ' ' . $k . '="' . esc_attr($v) . '"';
            }
            if ($tag === 'p') {
                $out .= '<p' . $attr_html . '>' . esc_html($txt) . '</p>';
            } elseif (in_array($tag, ['h2', 'h3'], true)) {
                $out .= '<' . $tag . $attr_html . '>' . esc_html($txt) . '</' . $tag . '>';
            }
        }
        return $out;
    }

    protected function faq_html(array $rows): array {
        $out = [];
        foreach ($rows as $r) {
            $out[] = ['h3', $r[0]];
            $out[] = ['p', $r[1]];
        }
        return $out;
    }

    protected function mini_toc(): string {
        return '<nav class="tmw-mini-toc">
  <a href="#intro">Intro</a> · <a href="#highlights">Highlights</a> · <a href="#faq">FAQ</a>
</nav>';
    }

    protected function enforce_word_goal(string $content, string $focus, int $min = 900, int $max = 1200): string {
        // Accept whatever the base template produces; no padding.
        return $content;
    }

    protected function apply_density_guard(string $content, string $focus): string {
        // Leave content as-is so keyword density stays moderate
        // and we avoid over-optimization.
        return $content;
    }
}
