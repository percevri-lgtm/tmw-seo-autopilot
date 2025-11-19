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

        $phrase_limits = [
            'focus' => ['phrase' => $focus, 'min' => 6, 'max' => 12, 'fallback' => 'the performer'],
        ];
        $fallbacks = [
            'extra_0' => 'this opening groove',
            'extra_1' => 'this chat rehearsal',
            'extra_2' => 'this pacing study',
            'extra_3' => 'this reflective finale',
        ];
        foreach ($extras as $idx => $extra) {
            $phrase_limits['extra_' . $idx] = [
                'phrase'   => $extra,
                'min'      => 3,
                'max'      => 7,
                'fallback' => $fallbacks['extra_' . $idx] ?? 'this segment',
            ];
        }
        $phrase_counts = array_fill_keys(array_keys($phrase_limits), 0);

        $use_phrase = function (string $key, string $fallback = '') use (&$phrase_counts, $phrase_limits) {
            if (!isset($phrase_limits[$key])) {
                return $fallback;
            }
            if ($fallback === '') {
                $fallback = $phrase_limits[$key]['fallback'] ?? '';
            }
            if ($phrase_counts[$key] >= $phrase_limits[$key]['max']) {
                return $fallback;
            }
            $phrase_counts[$key]++;
            return $phrase_limits[$key]['phrase'];
        };

        $blocks   = [];
        $intro_one = sprintf(
            '%s sets the tone for this viewing guide, weaving %s, %s, %s, and %s into a calm welcome that previews the moods ahead. The opening notes describe how she shifts from warm smiles to purposeful gestures, explain when the camera glides past the dressing mirror, and signal when viewers should take a breath before the first highlight cues roll in.',
            $use_phrase('focus', $focus),
            $use_phrase('extra_0', $extras[0]),
            $use_phrase('extra_1', $extras[1]),
            $use_phrase('extra_2', $extras[2]),
            $use_phrase('extra_3', $extras[3])
        );

        $intro_two = sprintf(
            'Because %s treats each stream like a mini narrative, the second greeting paragraph sets expectations for chapter breaks, mentions when the crew dims the lamps, and cues watchers to note breathing exercises before transitions. It also highlights how the team shares respectful chat etiquette reminders so the experience stays PG-13 while still feeling spontaneous.',
            $use_phrase('focus', $focus)
        );

        $blocks[] = ['p', $intro_one];
        $blocks[] = ['p', $intro_two];

        foreach ($extras as $index => $extra) {
            if ($index === 0) {
                $heading_phrase = $use_phrase('extra_0', $extra);
                $blocks[] = ['h2', sprintf('%s framing tips', $heading_phrase), ['id' => 'segment-' . ($index + 1)]];
                $blocks[] = ['p', sprintf('Stage managers describe how %s draws strength from %s by pairing long glides with steady smiles and relaxed shoulders. Overhead lanterns are feathered toward the floor, giving space for soft shadows while the host references journal notes about breathing rhythm and eye contact.', $use_phrase('focus', $focus), $use_phrase('extra_0', $extra))];
                $blocks[] = ['p', sprintf('During the first chorus the writing points out that %s becomes a cue for viewers to sit taller and track the gentle sway of the camera crane. The article encourages fans to notice fingertip placement on the mic stand, appreciate the controlled pace, and mirror the slow inhales before the tempo shifts.', $use_phrase('extra_0', $extra))];
            } elseif ($index === 1) {
                $heading_phrase = $use_phrase('extra_1', $extra);
                $blocks[] = ['h2', sprintf('%s hosting cues', $heading_phrase), ['id' => 'segment-' . ($index + 1)]];
                $blocks[] = ['p', sprintf('Writers explain how %s invites the room to settle into %s segments where the host leans toward the lens and practices gentle call-and-response prompts. Lighting dims to a honey color, and crew members tap cue cards that remind everyone to smile between each spoken line.', $use_phrase('focus', $focus), $use_phrase('extra_1', $extra))];
                $blocks[] = ['p', sprintf('The breakdown shows viewers how %s moments include soft wrist rolls, reassuring nods, and a pause that lets supportive comments surface in the live window. Fans are reminded to keep fingertips relaxed on the keyboard and to note how the host circles back to compassionate humor before moving on.', $use_phrase('extra_1', $extra))];
            } elseif ($index === 2) {
                $heading_phrase = $use_phrase('extra_2', $extra);
                $blocks[] = ['h2', sprintf('%s pacing study', $heading_phrase), ['id' => 'segment-' . ($index + 1)]];
                $blocks[] = ['p', sprintf('Cinematographers linger on each beat whenever %s takes over, allowing %s to describe the choreography in a reflective tone. The script details how the dolly operator counts to five on every push, how the wardrobe shimmer catches low angles, and how the performer keeps expressions bright even while moving slowly.', $use_phrase('extra_2', $extra), $use_phrase('focus', $focus))];
                $blocks[] = ['p', sprintf('Viewers are urged to stretch their shoulders as %s sections build, keeping the spine tall and noticing how ambient synth notes rise and fall. Notes remind everyone that the host quietly adjusts bracelets and posture to match the groove, which helps the segment feel more intimate without breaking the PG-13 promise.', $use_phrase('extra_2', $extra))];
            } else {
                $heading_phrase = $use_phrase('extra_3', $extra);
                $blocks[] = ['h2', sprintf('%s lightplay notes', $heading_phrase), ['id' => 'segment-' . ($index + 1)]];
                $blocks[] = ['p', sprintf('Crew journals describe how %s is presented with reflective props, letting %s experiment with glints of light that skate across the stage floor. Wide primes capture glittering curtains while assistants adjust dimmers in slow increments so nothing feels rushed.', $use_phrase('extra_3', $extra), $use_phrase('focus', $focus))];
                $blocks[] = ['p', sprintf('When %s returns later in the set, the article recommends lowering room lights at home and leaning closer to the screen to watch the mirrored movements. It points out how the host rests a palm on the railing, takes a deliberate pause, and then turns toward the audience with a calm grin that anchors the final minutes.', $use_phrase('extra_3', $extra))];
            }
        }

        $blocks[] = ['p', 'Between segments, stagehands tidy cables, wipe mirrors, and pass along warm tea so the performer can keep posture relaxed. The guide suggests viewers stretch wrists, adjust chair height, and mimic the gentle breathing pattern described by the crew to keep their own living rooms aligned with the calm studio cadence.'];

        $blocks[] = ['h3', 'Model profile & internal link'];
        $model_url = !empty($c['model_url']) ? $c['model_url'] : '#';
        $link_focus = $use_phrase('focus', $focus);
        $link_extra = $use_phrase('extra_0', $extras[0]);
        $blocks[] = ['raw', sprintf('<p>Visit the dedicated profile for <a href="%s">%s</a> to gather behind-the-scenes notes on %s and refreshed looks at %s clips, then bookmark upcoming appearances that echo this pacing guide.</p>', esc_url($model_url), esc_html($name), esc_html($link_focus), esc_html($link_extra))];

        $conclusion_heading = $use_phrase('focus', $focus);
        $blocks[] = ['h2', sprintf('%s closing reflections', $conclusion_heading)];
        $blocks[] = ['p', sprintf('The recap ties together how %s threads %s, %s, %s, and %s through a five-part structure that feels like a tour of the studio floor. Readers are reminded to sip water between chapters, keep volume at a gentle level, and jot down the timestamp where the laughter peaks before the chat window reopens.', $use_phrase('focus', $focus), $use_phrase('extra_0', $extras[0]), $use_phrase('extra_1', $extras[1]), $use_phrase('extra_2', $extras[2]), $use_phrase('extra_3', $extras[3]))];
        $blocks[] = ['p', sprintf('Final notes encourage viewers to breathe along with the tempo changes, watch for the way %s softens her shoulders before spinning toward the crowd, and switch over to live interaction only after the closing bow fades.', $use_phrase('focus', $focus))];

        $supplement_templates = [
            'focus'   => 'A quick aside reminds viewers that %s steadies the atmosphere whenever nerves rise before the next cue.',
            'extra_0' => 'Crew leads repeat that %s is the signal to relax shoulders and let the upbeat sway return.',
            'extra_1' => 'Hosts mention that %s is the best moment to type gentle compliments without rushing.',
            'extra_2' => 'Choreographers add that %s helps everyone feel the slower rhythm before the next swell.',
            'extra_3' => 'Lighting techs whisper that %s brings the reflective shimmer back for one last wink.',
        ];
        foreach ($phrase_limits as $key => $limit) {
            while ($phrase_counts[$key] < $limit['min'] && $phrase_counts[$key] < $limit['max']) {
                $value = $use_phrase($key);
                if ($value === '') {
                    break;
                }
                $template = $supplement_templates[$key] ?? 'Additional guidance reminds viewers how %s keeps the momentum steady.';
                $blocks[] = ['p', sprintf($template, $value)];
            }
        }

        $content    = $this->html($blocks);
        $word_count = str_word_count(strip_tags($content));
        $padding    = [
            'Production journals expand on how the stage manager counts down each lighting fade, encouraging viewers at home to dim lamps gradually so their spaces feel as calm as the studio.',
            'Camera assistants describe wiping lenses between takes, checking that the jib wheels roll silently, and reminding fans to stretch wrists or sip water while the performer resets props.',
            'A final behind-the-scenes vignette paints the green room, noting quiet affirmations from stylists, freshly steamed outfits on rolling racks, and the gentle hum of monitors waiting for the encore.',
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
