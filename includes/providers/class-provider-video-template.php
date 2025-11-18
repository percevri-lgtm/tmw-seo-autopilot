<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

use TMW_SEO\Core;

class VideoTemplate {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name  = $c['name'];
        $site  = $c['site'];
        $focus = trim($c['focus'] ?? Core::video_focus($name));
        $brand = $c['brand'] ?? ($c['site'] ?: 'Top Models Webcam');

        // Extra keywords from LJ / tags
        $extras   = array_values(array_slice($c['extras'] ?? [], 0, 4));
        $keywords = array_merge([$focus], $extras);

        // Light randomisation for numbers & power words
        $title_seed  = absint(($c['video_id'] ?? 0) ?: crc32($name));
        $numbers     = [3, 4, 5, 6, 7, 8, 9];
        $power_words = ['Exclusive', 'Prime', 'Top', 'Essential'];
        $number      = $numbers[$title_seed % count($numbers)];
        $power       = $power_words[$title_seed % count($power_words)];

        // SEO title (used by RankMath)
        $title = sprintf(
            '%s — %s (%d %s Live Highlights)',
            $focus,
            $extras[0] ?? 'Black Hair Webcam Model',
            $number,
            $power
        );

        $descriptor = $extras[0] ?? 'webcam model';

        $meta = sprintf(
            '%s — %s in %d %s live highlights on %s. %s and cam girl live vibes with quick links to live chat and profile.',
            $focus,
            $name,
            $number,
            strtolower($power),
            $brand,
            $descriptor
        );

        // Neutral headings so we don’t spam the keyword
        $intro_heading      = 'Intro — Video Overview';
        $highlights_heading = 'Highlights & Live Moments';
        $faq_heading        = 'FAQ — ' . $name . ' webcam profile & show';

        $extra_mentions = array_slice($extras, 0, 3);
        $extra_one      = $extra_mentions[0] ?? 'black hair webcam model';
        $extra_two      = $extra_mentions[1] ?? 'cam girl live';
        $extra_three    = $extra_mentions[2] ?? 'dancing webcam model';

        // FIRST sentence: must start with the focus keyword for RankMath.
        // This should still read like normal text.
        $lead_focus = $focus !== '' ? $focus : $name;
        $lead = sprintf(
            '%s offers %d %s live highlights in one quick reel, blending %s and %s vibes with fast jumps into live chat on Jasmin.',
            $lead_focus,
            $number,
            strtolower($power),
            $extra_one,
            $extra_two
        );

        // Intro body – all human-readable, no SEO jargon in the visible text
        $intro_paragraphs = [
            sprintf(
                'From the first seconds, %s keeps the camera steady and the lighting soft, so viewers can settle in and understand the mood of the room. Each opening beat hints at the %d live highlights promised in the title, making it clear that this reel is all about carefully chosen moments instead of random cuts.',
                $name,
                $number
            ),
            sprintf(
                '%s talks viewers through how this highlight reel connects to live chat, without simply repeating what is already written on the model profile. People see exactly when to click through, how to line up questions, and what kind of welcome they can expect once the room goes live.',
                $name
            ),
            sprintf(
                'Early shots echo the main hook of the show: playlists, room design, and that warm color wash that feels instantly familiar to regulars. The pacing stays brisk so the main phrase that people search for never feels forced, and the description naturally circles back to %s and %s details.',
                $extra_one,
                $extra_three
            ),
            sprintf(
                'Fans arriving from %s will recognize small navigation hints tucked into the narration. Short lines point toward profile updates, schedule banners, and the latest highlight count. The text stays clear and conversational, as if %s is guiding a new friend through the page step by step.',
                $site ?: 'the site',
                $name
            ),
        ];

        $highlight_paragraphs = [
            sprintf(
                'The highlight section focuses on composition. Close-ups of expressions and quick cuts to outfit details show how %s uses small gestures and eye contact to keep attention on the screen. Every change of angle has a purpose, whether it is to show a reaction, an outfit detail, or just a better view of the room.',
                $name
            ),
            sprintf(
                'Halfway through the reel, the soundtrack softens and the lighting cools down. %s mentions these shifts out loud, explaining which parts of the show work best for relaxed chat and which moments usually lead into more energetic segments when the room is full.',
                $name
            ),
            sprintf(
                'Another segment follows the countdown just before a live session starts. The camera lingers on setup details and chat reactions, while %s reminds viewers to keep notifications on and to bookmark the %s page so they can drop in right as the energy peaks.',
                $name,
                $brand
            ),
            sprintf(
                'A later chapter links back to the model profile%s. Here the narration calls out wardrobe polls, recent fan favorites, and the kind of themes that keep coming back in shows, giving people a sense of what they will see if they follow the profile regularly.',
                ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : ''
            ),
            sprintf(
                'The final highlight slows everything down again. %s thanks supporters, invites simple look-inspired requests based on that %s style, and gently nudges everyone toward the call-to-action link, keeping the tone friendly instead of pushy.',
                $name,
                $extra_one
            ),
        ];

        $faq = [
            [
                sprintf('How do I join %s live chat from this highlight page?', $name),
                sprintf(
                    'Use the deep link near the title or the branded button below; both take you straight into the room where the pace and atmosphere match these %d %s live highlights.',
                    $number,
                    strtolower($power)
                ),
            ],
            [
                sprintf('What kind of vibe do these highlights show for %s?', $name),
                sprintf(
                    'Expect soft lighting, plenty of eye contact, and relaxed smiles. The overall feel is closer to a cosy %s session than a scripted clip, making it easy for new viewers to settle in.',
                    $extra_one
                ),
            ],
            [
                sprintf('Which tags helped shape this video description?', $name),
                sprintf(
                    'The write-up weaves in phrases drawn from tags such as %s, %s, and %s so the page feels aligned with how fans usually describe the show.',
                    $extra_one,
                    $extra_two,
                    $extra_three
                ),
            ],
            [
                sprintf('How does this reel connect to the full profile for %s?', $name),
                sprintf(
                    'Throughout the text you will see gentle reminders to visit the profile%s and save the schedule. That way fans know when the next reel is likely to drop and when %s is usually online for live chat.',
                    ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : '',
                    $name
                ),
            ],
        ];

        // Build block structure
        $blocks = [];

        // Short paragraph BEFORE any H2 so RankMath sees the focus keyword
        $blocks[] = ['p', $lead];

        $blocks[] = ['h2', $intro_heading, ['id' => 'intro']];
        foreach ($intro_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        $blocks[] = ['h2', $highlights_heading, ['id' => 'highlights']];
        foreach ($highlight_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        if (!empty($c['brand_url'])) {
            $blocks[] = [
                'raw',
                '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Join '
                . esc_html($name)
                . ' live chat</a> to see the highlights unfold in real time.</p>',
            ];
        }

        $blocks[] = ['h2', $faq_heading, ['id' => 'faq']];
        $blocks   = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);

        // Keep helpers as no-op so we don’t accidentally stuff keywords
        $content = $this->enforce_word_goal($content, $focus, 650, 800);
        $content = $this->apply_density_guard($content, $focus);

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
