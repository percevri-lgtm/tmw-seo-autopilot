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
            '%s 5 Epic %s %s %s %s',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $meta = sprintf(
            '%s, %s, %s, %s, and %s drive this PG-13 video guide with lighting tips, camera cues, pacing notes, and an invite to revisit %s on %s.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3],
            $brand,
            $site
        );

        $image_src    = $c['image']
            ?? $c['thumbnail']
            ?? $c['thumbnail_url']
            ?? 'https://placehold.co/1200x675?text=Video+Preview';
        $image_alt = sprintf(
            '%s, %s, %s, %s, %s cinematic preview still',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $intro_one = sprintf(
            '%s stays at the heart of this curated video page, and %s, %s, %s, and %s share the same spotlight so RankMath registers a balanced spread. The introduction explains how the featurette strings together mellow PG-13 storytelling beats, and it sets the tone for viewers who prefer guidance on when to lean in for reactions or when to enjoy the rhythm. Camera cards list which lens handles close warmth, which boom mic catches whispers, and how each keyword helps fans understand the structure before the first scene fades in.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $intro_two = sprintf(
            'Because %s commands the direction, the host spends the first minute outlining how %s, %s, %s, and %s will reappear in captions, scene notes, and smooth on-screen prompts. Viewers get explicit instructions to look for alternating wide and medium angles, to notice the softbox lighting bloom on reflective surfaces, and to follow each keyword as if it were color-coded overlays. This early briefing keeps the first tenth of the script loaded with the SEO targets while still sounding like a human conversation rather than a string of tags.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $mid_one = sprintf(
            'A still frame captures %s with supportive glow while %s and %s orbit the edges, demonstrating how the broadcast maintains clarity without the harshness of live flash rigs. The lighting plan cycles between amber fill and neutral backlight so the PG-13 mood remains dreamy, and each gentle refocus slides into the next highlight. Directors comment on where %s and %s tuck inside the timeline so returning fans can jump straight to the segments they bookmarked.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $mid_two = sprintf(
            'Production notes keep echoing %s to remind editors that the video is about a friendly rhythm rather than sudden shocks. Angle breakdowns instruct camera two to hover slightly above eyeline whenever %s takes the lead, while camera three drops to a relaxed shoulder shot when %s guides the conversation. Those cues give the highlight structure enough variation to feel live without frantic cuts, and they also make it easy for subtitles to reference %s and %s in natural sentences.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );

        $blocks   = [];
        $blocks[] = ['p', $intro_one];
        $blocks[] = ['p', $intro_two];
        $blocks[] = [
            'raw',
            '<figure class="tmwseo-video-preview"><img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" loading="lazy" decoding="async"></figure>'
        ];
        $blocks[] = ['p', $mid_one];
        $blocks[] = ['p', $mid_two];

        foreach ($extras as $index => $extra) {
            $partner_one = $extras[($index + 1) % 4];
            $partner_two = $extras[($index + 2) % 4];
            $partner_thr = $extras[($index + 3) % 4];

            $blocks[] = ['h2', sprintf('%s spotlight & viewing tips', $extra), ['id' => 'keyword-' . ($index + 1)]];
            $blocks[] = ['p', sprintf('%s leads the mid-roll beat where the director leans into a 35mm slider move to showcase the expressions that make %s memorable. Stage managers call out cues so %s remains woven through the dialogue, and regulars know exactly when the highlight structure bends toward a laugh, a pause, or a quick wink that nods back to %s and %s.', $extra, $focus, $extra, $partner_one, $focus)];
            $blocks[] = ['p', sprintf('Lighting notes describe how %s benefits from a layered glow: key light at forty percent, fill dropped to twenty, and backlight colored with the same palette used during %s interludes. Editors mark these details for transcripts so %s appears naturally in the same sentences as %s and %s, giving guidance without sounding pushy.', $extra, $partner_two, $extra, $partner_thr, $focus)];
        }

        $blocks[] = ['h3', 'Model profile & internal link'];
        $model_url = !empty($c['model_url']) ? $c['model_url'] : '#';
        $blocks[] = ['raw', sprintf('<p>Want more context on %s, %s, %s, %s, and %s? Visit the dedicated profile for <a href="%s">%s</a> to review wardrobe notes, playlists, and upcoming appearance times that sync with this video plan.</p>', $focus, $extras[0], $extras[1], $extras[2], $extras[3], esc_url($model_url), esc_html($name))];

        $blocks[] = ['h2', sprintf('%s conclusion & next cues', $focus)];
        $blocks[] = ['p', sprintf('The closing recap reiterates that %s, %s, %s, %s, and %s are the navigation anchors. Viewers are encouraged to replay the slow pans, the over-the-shoulder reveals, and the diffused lighting tests so they can appreciate how every angle underlines the PG-13 charm.', $focus, $extras[0], $extras[1], $extras[2], $extras[3])];
        $blocks[] = ['p', sprintf('Editors leave friendly reminders inside the description so anyone following %s knows when %s, %s, and %s reappear as supporting moods. The guidance reinforces soft transitions, calibrates color temperature notes, and highlights the internal link for fans who want deeper %s lore before the next upload.', $focus, $extras[0], $extras[1], $extras[2], $extras[3])];

        $content    = $this->html($blocks);
        $word_count = str_word_count(strip_tags($content));
        $padding    = [
            sprintf('Behind-the-scenes commentary tracks explain how %s works with %s and %s to keep the pacing steady, why the jib operator holds each glide for at least five seconds, and how the lighting board saves presets so the PG-13 tone never slips. This reflective passage keeps keywords conversational while ensuring the narrative feels like a real director talking shop.', $focus, $extras[0], $extras[1]),
            sprintf('An additional set of viewer tips reminds audiences to listen for the subtle cue where %s signals a switch into %s and %s territory, noting that the script always circles back to %s for continuity. The combination of camera angles, emotional beats, and keyword-friendly narration satisfies both human readers and RankMath scoring.', $focus, $extras[2], $extras[3], $focus),
        ];

        foreach ($padding as $pad) {
            if ($word_count >= 800) {
                break;
            }
            $blocks[]  = ['p', $pad];
            $content   = $this->html($blocks);
            $word_count = str_word_count(strip_tags($content));
        }

        if ($word_count > 1000) {
            array_pop($blocks);
            $content = $this->html($blocks);
        }

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
