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

        $extras   = array_values(array_slice($c['extras'] ?? [], 0, 4));
        $keywords = array_merge([$focus], $extras);

        // Use a stable number but always keep it small (good for titles)
        $title_seed = absint(($c['video_id'] ?? 0) ?: crc32($name));
        $numbers    = [3, 4, 5, 6, 7, 8, 9];
        $number     = $numbers[$title_seed % count($numbers)];

        /**
         * IMPORTANT for RankMath:
         * - Focus keyword at the beginning.
         * - Includes "Best" and "Amazing" (power + positive sentiment words).
         * - Includes a number.
         */
        $title = sprintf(
            '%s — %d Best & Amazing Live Highlights',
            $focus,
            $number
        );

        $descriptor = $extras[0] ?? 'webcam model';
        $meta = sprintf(
            '%s — %s in %d best and amazing live highlights on %s. %s vibes with quick links to live chat and profile.',
            $focus,
            $name,
            $number,
            $brand,
            $descriptor
        );

        // Headings keep the focus keyword in H2s for RankMath
        $intro_heading      = 'Intro — ' . $focus;
        $highlights_heading = 'Highlights — ' . $focus;
        $faq_heading        = 'FAQ — ' . $name . ' webcam profile & show';

        $extra_mentions = array_slice($extras, 0, 3);
        $extra_one      = $extra_mentions[0] ?? 'live cam model';
        $extra_two      = $extra_mentions[1] ?? 'webcam model profile';
        $extra_three    = $extra_mentions[2] ?? 'live webcam chat';

        $lead = sprintf(
            '%s starts with polished pacing so the focus keyword "%s" shows up right away alongside %s cues about how to jump from this reel into live chat.',
            $name,
            $focus,
            $extra_one
        );

        $intro_paragraphs = [
            sprintf(
                'This intro frames %s as a %s who balances camera angles, soft lighting, and calm narration. Each opening beat hints at the %d best live highlights promised in the title, making it clear that this page is about curated moments rather than explicit scenes.',
                $name,
                $extra_one,
                $number
            ),
            sprintf(
                '%s explains how the highlight reel links to live chat without repeating the model page. Viewers get direction on when to click the deep link, how to queue questions, and why the %s keeps the vibe friendly and PG-13 while still feeling like a personal invitation.',
                $name,
                $extra_two
            ),
            sprintf(
                'Early shots reference the hook, showing how playlists, set design, and the warm color wash echo what happens in the full show. The pacing stays brisk so that the focus keyword never gets buried, and the description keeps returning to %s and %s style cues.',
                $focus,
                $extra_three
            ),
            sprintf(
                'Fans reading from %s will notice navigation tips that point toward profile updates, schedule banners, and the latest highlight count. By keeping sentences short and descriptive, the intro keeps search-friendly wording while sounding like guidance from %s directly.',
                $site ?: 'the site',
                $name
            ),
        ];

        $highlight_paragraphs = [
            sprintf(
                'The highlights section dives into composition. Close-ups of expressions and quick cuts to outfit details show how %s uses subtle gestures to hold attention. This is where the focus keyword "%s" reappears, paired with %s so RankMath registers natural secondary phrases.',
                $name,
                $focus,
                $extra_two
            ),
            sprintf(
                'Midway through the reel, the soundtrack softens and the lighting shifts to a cooler palette. %s narrates each adjustment, noting which moves translate to live chat and how viewers can expect the same pacing in private while keeping all language clean and approachable.',
                $name
            ),
            sprintf(
                'Another highlight follows the countdown moments before a live session starts. The camera lingers on setup details, letting %s remind viewers to keep notifications on and to bookmark the %s page for quick access when energy ramps up.',
                $name,
                $brand
            ),
            sprintf(
                'A penultimate chapter links directly to the model profile%s, summarizing wardrobe polls and recent fan-favorite segments. It repeats the phrase "%s" naturally, aligning on-page text with the chosen focus keyword while keeping the description anchored in SFW language.',
                ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : '',
                $focus
            ),
            sprintf(
                'The final highlight revisits the best-and-amazing tone of the reel. %s thanks supporters, mentions that %s look-inspired requests are welcome in chat, and directs everyone toward the call-to-action link without sounding salesy.',
                $name,
                $extra_one
            ),
        ];

        $faq = [
            [
                sprintf('How do I join %s live chat from this highlight page?', $name),
                sprintf(
                    'Use the deep link near the title or the brand button below; both routes jump straight into the room where the pacing matches these %d best live highlights.',
                    $number
                ),
            ],
            [
                sprintf('What vibe do these highlights show for %s?', $name),
                sprintf(
                    'Expect a mix of soft lighting, eye contact, and relaxed smiles. The tone is closer to a %s than a scripted clip, keeping everything SFW and welcoming to new viewers.',
                    $extra_one
                ),
            ],
            [
                sprintf('Which tags influence this video write-up?', $name),
                sprintf(
                    'The content blends the focus keyword with phrases like %s and %s so the description mirrors the tags without repeating the exact model page language.',
                    $extra_two,
                    $extra_three
                ),
            ],
            [
                sprintf('How do the highlights connect to the full profile for %s?', $name),
                sprintf(
                    'Each paragraph references profile links%s and invites readers to bookmark the schedule. That way fans know when the next reel drops and when %s is likely to be online.',
                    ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : '',
                    $name
                ),
            ],
        ];

        $blocks = [
            ['h2', $intro_heading, ['id' => 'intro']],
            ['p', $lead],
        ];
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
                '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Jump into ' . esc_html($name) . ' live chat</a> to see the highlights unfold in real time.</p>'
            ];
        }

        $blocks[] = ['h2', $faq_heading, ['id' => 'faq']];
        $blocks   = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);
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
        // We keep the base template output; RankMath already likes ~600–800 words.
        return $content;
    }

    protected function apply_density_guard(string $content, string $focus): string {
        // Leave content as-is so keyword density stays natural.
        return $content;
    }
}
