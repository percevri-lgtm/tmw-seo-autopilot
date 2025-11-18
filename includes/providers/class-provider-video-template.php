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

        // Extra keywords: keep max 4 and use them everywhere (title, headings, body).
        $extras   = array_values(array_slice($c['extras'] ?? [], 0, 4));
        $keywords = array_merge([$focus], $extras);

        // Normalise extras so we can safely reference indices.
        $extra_mentions = $extras;
        while (count($extra_mentions) < 4) {
            $extra_mentions[] = '';
        }
        $extra_one   = $extra_mentions[0] ?: 'live cam model';
        $extra_two   = $extra_mentions[1] ?: 'webcam model profile';
        $extra_three = $extra_mentions[2] ?: 'live webcam chat';
        $extra_four  = $extra_mentions[3] ?: '';

        // --- Title: focus keyword first + a short extra keyword and power word number ---
        $title_seed  = absint(($c['video_id'] ?? 0) ?: crc32($name));
        $numbers     = [3, 4, 5, 6, 7, 8, 9];

        // Use only power words RankMath recognises.
        $power_words = ['Amazing', 'Best', 'Ultimate', 'Exclusive'];

        $number = $numbers[$title_seed % count($numbers)];
        $power  = $power_words[$title_seed % count($power_words)];

        // Use first extra keyword in title (Title Case) to keep it strong but still targeted.
        $extra_title = $extra_one ? ucwords($extra_one) : $power . ' Live';

        // Example:
        //   Cam Model Anisyia — Black Hair Webcam Model (7 Amazing Highlights)
        $title = sprintf(
            '%s — %s (%d %s Highlights)',
            $focus,
            $extra_title,
            $number,
            $power
        );

        // --- Meta description: mention focus + 2 extras, keep it clean and click-friendly ---
        $meta = sprintf(
            '%s — %s in %d %s live highlights on %s. %s and %s vibes with quick links to live chat and profile.',
            $focus,
            $name,
            $number,
            strtolower($power),
            $brand,
            $extra_one,
            $extra_two ?: $extra_one
        );

        // --- Headings ---
        // Intro heading: do NOT repeat the full focus keyword here (helps lower density).
        $intro_heading = 'Intro — Video Overview';

        // Keep one H2 with the focus keyword so RankMath sees it in a subheading.
        if ($extra_two && $extra_two !== $extra_one) {
            $highlights_heading = sprintf('Highlights — %s & %s', $focus, $extra_two);
        } else {
            $highlights_heading = 'Highlights — ' . $focus;
        }

        if ($extra_three && $extra_three !== $extra_one && $extra_three !== $extra_two) {
            $faq_heading = sprintf('FAQ — %s, %s & live chat', $name, $extra_three);
        } else {
            $faq_heading = sprintf('FAQ — %s webcam profile & show', $name);
        }

        // --- Short bridge paragraph between H1 and first H2 ---
        $bridge = sprintf(
            '%s offers %d %s live highlights in one quick reel, blending %s and %s moments with fast jumps into live chat on %s.',
            $name,
            $number,
            strtolower($power),
            $extra_one,
            $extra_two ?: $extra_one,
            $brand
        );

        // --- Body copy ---
        $lead = sprintf(
            '%s starts with polished pacing so the focus keyword shows up right away alongside %s cues about how to jump from this reel into live chat.',
            $name,
            $extra_one
        );

        $intro_paragraphs = [
            sprintf(
                'This intro frames %s as a %s who balances camera angles, soft lighting, and calm narration. Each opening beat hints at the %d %s live highlights promised in the title, making it clear that this page is about curated moments rather than explicit scenes.',
                $name,
                $extra_one,
                $number,
                strtolower($power)
            ),
            sprintf(
                '%s explains how the highlight reel links to live chat without repeating the model page. Viewers get direction on when to click the deep link, how to queue questions, and why the %s keeps the vibe friendly and PG-13 while still feeling like a personal invitation.',
                $name,
                $extra_two
            ),
            sprintf(
                'Early shots reference the hook, showing how playlists, set design, and the warm color wash echo what happens in the full show. The pacing stays brisk so that the main keyword never gets buried, and the description keeps returning to %s and %s style cues.',
                $extra_one,
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
                'The highlights section dives into composition. Close-ups of expressions and quick cuts to outfit details show how %s uses subtle gestures to hold attention. This is where the main keyword reappears, paired with %s so RankMath registers natural secondary phrases.',
                $name,
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
                'A penultimate chapter links directly to the model profile%s, summarizing wardrobe polls and recent fan-favorite segments. It repeats the key phrase naturally, aligning on-page text with the chosen focus keyword while keeping the description anchored in SFW language.',
                ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : ''
            ),
            sprintf(
                'The final highlight revisits the %s tone of the reel. %s thanks supporters, mentions that %s look-inspired requests are welcome in chat, and directs everyone toward the call-to-action link without sounding salesy.',
                strtolower($power),
                $name,
                $extra_four ?: $extra_one
            ),
        ];

        // Build a human-readable list for the FAQ "tags" answer.
        $tag_bits   = array_filter([$extra_one, $extra_two, $extra_three, $extra_four]);
        $tag_phrase = implode(', ', $tag_bits);

        $faq = [
            [
                sprintf('How do I join %s live chat from this highlight page?', $name),
                sprintf('Use the deep link near the title or the brand button below; both routes jump straight into the room where the pacing matches these %d %s live highlights.', $number, strtolower($power)),
            ],
            [
                sprintf('What vibe do these highlights show for %s?', $name),
                sprintf('Expect a mix of soft lighting, eye contact, and relaxed smiles. The tone is closer to a %s than a scripted clip, keeping everything SFW and welcoming to new viewers.', $extra_one),
            ],
            [
                sprintf('Which tags influence this video write-up?', $name),
                $tag_phrase
                    ? sprintf('The content blends the focus keyword with phrases like %s so the description mirrors the tags without repeating the exact model page language.', $tag_phrase)
                    : sprintf('The content blends the focus keyword with supporting phrases so the description mirrors the tags without repeating the exact model page language.')
            ],
            [
                sprintf('How do the highlights connect to the full profile for %s?', $name),
                sprintf('Each paragraph references profile links%s and invites readers to bookmark the schedule. That way fans know when the next reel drops and when %s is likely to be online.', ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : '', $name),
            ],
        ];

        // --- Assemble HTML blocks ---
        $blocks = [
            ['p', $bridge],
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
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Jump into ' . esc_html($name) . ' live chat</a> to see the highlights unfold in real time.</p>'];
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
        // We accept whatever the base template produces; no extra padding loops.
        return $content;
    }

    protected function apply_density_guard(string $content, string $focus): string {
        // Leave content as-is so keyword density stays natural
        // and RankMath doesn’t flag over-optimization.
        return $content;
    }
}
