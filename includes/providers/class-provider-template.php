<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

use TMW_SEO\Core;

class Template {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name  = $c['name'];
        $site  = $c['site'];
        $focus = trim($c['focus'] ?? Core::video_focus($name));
        $brand = $c['brand'] ?? ($c['site'] ?: 'Top Models Webcam');

        $extras   = array_values(array_slice($c['extras'] ?? [], 0, 4));
        $keywords = array_merge([$focus], $extras);

        $title_seed  = absint(($c['video_id'] ?? 0) ?: crc32($name));
        $numbers     = [3, 4, 5, 6, 7, 8, 9];
        $power_words = ['Must-See', 'Exclusive', 'Top', 'Prime'];
        $number      = $numbers[$title_seed % count($numbers)];
        $power       = $power_words[$title_seed % count($power_words)];

        $title = sprintf('Cam Model %s — %d %s Live Highlights', $name, $number, $power);

        $descriptor = $extras[0] ?? 'webcam model';
        $meta = sprintf(
            '%s in %d %s live highlights on %s. %s vibes with quick links to live chat and profile.',
            $name,
            $number,
            strtolower($power),
            $brand,
            $descriptor
        );

        $intro_heading      = 'Intro — ' . $name . ' live cam highlights';
        $highlights_heading = 'Highlights — ' . $name . ' on live cam';
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
                'The final highlight revisits the %s tone of the reel. %s thanks supporters, mentions that %s look-inspired requests are welcome in chat, and directs everyone toward the call-to-action link without sounding salesy.',
                strtolower($power),
                $name,
                $extra_one
            ),
        ];

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
                sprintf('The content blends the focus keyword with phrases like %s and %s so the description mirrors the tags without repeating the exact model page language.', $extra_two, $extra_three),
            ],
            [
                sprintf('How do the highlights connect to the full profile for %s?', $name),
                sprintf('Each paragraph references profile links%s and invites readers to bookmark the schedule. That way fans know when the next reel drops and when %s is likely to be online.', ! empty($c['model_permalink']) ? ' at ' . esc_url($c['model_permalink']) : '', $name),
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
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Jump into ' . esc_html($name) . ' live chat</a> to see the highlights unfold in real time.</p>'];
        }

        $blocks[] = ['h2', $faq_heading, ['id' => 'faq']];
        $blocks = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);
        $content = $this->enforce_word_goal($content, $focus, 650, 800);
        $content = $this->apply_density_guard($content, $focus);

        return ['title' => $title, 'meta' => $meta, 'keywords' => $keywords, 'content' => $content];
    }

    /** MODEL: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_model(array $c): array {
        $name = isset($c['name']) ? $c['name'] : '';
        $site = isset($c['site']) ? $c['site'] : '';
        $focus = isset($c['focus']) ? $c['focus'] : $name;
        $extras = isset($c['extras']) && is_array($c['extras']) ? $c['extras'] : [];

        $safe_name = esc_html($name);
        $safe_site = esc_html($site ?: 'Top Models Webcam');

        $title_patterns = [
            '%s — 7 Charming Quick Facts & Profile Highlights',
            '%s — 5 Essential Profile Highlights & Schedule Notes',
            '%s — 10 Favorite Moments & Live Cam Profile Guide',
        ];
        $title = sprintf($title_patterns[array_rand($title_patterns)], $name);

        $meta = sprintf(
            '%s on %s. Profile, photos, schedule notes, and live chat links. Follow %s for highlight clips, updates, and friendly live shows.',
            $name,
            $site ?: 'Top Models Webcam',
            $name
        );

        $base_keywords = [
            $name,
            $name . ' live cam',
            $name . ' profile',
            $name . ' webcam model',
            'live cam model',
            'webcam model profile',
        ];

        if (!empty($focus) && $focus !== $name) {
            array_unshift($base_keywords, $focus);
        }

        $keywords = array_values(array_unique(array_merge($base_keywords, array_slice($extras, 0, 4))));

        $intro_paragraphs = [
            '%s welcomes visitors with a calm overview of the room, explaining how playlists, chat prompts, and lighting come together so newcomers feel at ease within the first few minutes. The introduction mentions how reminders on %s keep everyone updated without needing social feeds, making the page a reliable hub for quick check-ins.',
            '%s prefers starting shows with a relaxed tempo that lets viewers find their footing, sip a drink, and read the day’s pinned notes. The intro highlights how the profile points to schedule blocks, replay links, and a friendly FAQ so every guest knows where to click next.',
            'Instead of rushing through announcements, %s outlines the flow of a typical session, from opening greetings to community polls. Visitors are encouraged to favorite the profile on %s and enable notifications so surprise sessions are easy to catch even on busy weekdays.',
            'This opening section clarifies that %s values steady communication and consistent pacing. The tone stays upbeat and PG-13, focusing on music themes, camera clarity, and the easy way regulars help guide new guests toward the best moments of each broadcast on %s.',
            '%s also shares how the room is organized, pointing out where playlists, lighting presets, and conversation starters live on the page. The goal is to remove guesswork so viewers can jump into friendly chat or request a specific vibe without interrupting the flow on %s.',
        ];

        $about_paragraphs = [
            '%s keeps this profile honest and detailed, sharing favorite topics, comfort levels, and how personal boundaries are protected. A short backstory notes how streaming started as a creative outlet, eventually growing into a daily routine that balances calm mornings with lively evenings. Followers see that this page gathers highlights, schedule notes, and community reminders in one place.',
            'In this section, %s explains how storytelling and music shape the channel. The performer loves quiet downtempo tracks for reflective conversations and brighter playlists for group energy. Viewers learn about favorite hobbies off-stream, from journaling to crafting playlists for friends, giving the page a grounded, personable feel.',
            'There is also a focus on how %s maintains a respectful environment. Platform tools are used to filter disruptive messages, and clear house rules encourage polite chat. New followers are reminded that questions are welcome, and even shy viewers can drop a greeting without pressure.',
            '%s highlights how the profile evolved through community feedback on %s. Small tweaks like clearer camera angles, better captions on photo sets, and themed nights came directly from viewer suggestions. That collaborative approach keeps the content feeling fresh without losing its comforting core.',
            'While the page stays professional, %s adds warm anecdotes about memorable streams, like the night a playlist sparked a city-themed discussion or the time a supportive viewer shared study tips with the room. These little notes make the profile feel like a friendly journal of shared moments.',
        ];

        $style_paragraphs = [
            'Streaming sessions with %s revolve around atmosphere: warm lighting, steady camera angles, and clear sound that keeps voices crisp. The performer narrates small actions so viewers never feel lost, describing when a new playlist starts or when the vibe is switching from calm talk to upbeat dancing in a PG-13 way.',
            'Expect a thoughtful pace from %s, where every transition is explained before it happens. When a poll closes, the results are read aloud; when a new outfit is teased, the schedule for that segment is shared. That structure builds trust, especially for fans who prefer planned, low-stress interactions.',
            '%s also balances spontaneity with predictability. Surprise mini-games pop up between songs, while regular themed nights keep the calendar anchored. The camera stays respectful, focusing on expressions, gestures, and set design rather than explicit visuals, making the content welcoming to a broad audience.',
            'Lighting and music choices are announced early so viewers with sensory preferences can prepare. %s notes when strobes will stay off, when softer color washes are coming, and how quieter songs signal more intimate, conversational moments that still stay within friendly PG-13 boundaries.',
            'During each stream, %s often pauses to recap chat highlights, thank recent supporters, and encourage newcomers to say hello. These check-ins make the room feel guided, ensuring that even when energy rises, the experience remains comfortable and considerate.',
        ];

        $schedule_paragraphs = [
            '%s keeps scheduling transparent with weekly posts and day-of reminders. Typical sessions start in the early evening, but the profile notes when daytime check-ins happen for viewers in other time zones. Calendar links and pinned updates on %s make it easy to track every change without sifting through old messages.',
            'When schedules shift, %s posts a quick explanation along with the adjusted plan, emphasizing reliability. Followers see which nights feature longer sets, which afternoons host short chats, and how to prepare headphones or screens for the best viewing angle before the show begins.',
            'Seasonal events also show up in this section. %s might host theme weeks tied to music genres or city-inspired sets, always noting start times and estimated durations. The goal is to help fans plan around work, study, or family commitments while still catching live moments.',
            '%s shares how reminders are delivered: push notifications on the platform, email digests, and a small banner at the top of the profile. This layered approach means even casual viewers can catch at least one alert before the room goes live.',
            'The schedule section encourages viewers to join a few minutes early. %s uses that window for sound checks, lighting tweaks, and quick chat icebreakers so the main show starts smoothly. That routine builds predictability and keeps technical hiccups from interrupting the fun.',
        ];

        $community_paragraphs = [
            'Community notes explain how %s keeps chat welcoming. Moderators greet newcomers, share links to rules, and highlight viewer milestones like anniversaries or helpful tips. The performer reminds everyone that kind language and patience make the room feel like a lounge rather than a rushed feed.',
            '%s encourages gentle conversation starters: favorite playlists, travel stories, study hacks, or feel-good shows worth recommending. Viewers often share productivity tricks while waiting for a segment to start, turning idle minutes into collaborative time that keeps energy positive.',
            'Support tips focus on small gestures. %s thanks viewers for simple actions like clicking follow, answering polls, or sharing timing preferences. These signals help tailor each stream while keeping expectations realistic and respectful.',
            'Another paragraph outlines how %s handles requests. Clear boundaries are restated, and suggestions that align with the day’s theme are queued up. When something cannot happen, the performer explains why, preserving trust and keeping the conversation on track.',
            'Finally, the community section invites viewers to bookmark the profile and use reaction tools to cheer on the performer. %s notes that this ongoing encouragement shapes future sets and helps decide which highlights become permanent clips on the profile.',
        ];

        $faq_paragraphs = [
            'When asked about typical online times, %s usually points to early evenings with bonus weekend shows; the FAQ reminds readers to check the banner on %s for last-minute adjustments.',
            'Viewers also ask about the room vibe. The answer emphasizes friendly chat, curated music, and calm pacing, plus reminders that respectful language keeps the mood bright and inclusive for everyone present.',
            'Questions about private requests are covered by explaining that one-on-one time mirrors the public tone—focused on conversation, music choices, and guided moments that stay within posted guidelines. Boundaries remain clear so everyone feels safe.',
            'Fans often wonder how to support the performer. The FAQ suggests turning on notifications, bookmarking the profile, participating in polls, and sharing thoughtful feedback after each stream to help shape future sessions.',
            'Another common question involves technical prep. %s recommends using headphones for audio cues, keeping screens at eye level for comfort, and testing connections a few minutes before showtime to avoid missing the intro.',
        ];

        $build_paragraphs = function (array $templates, int $count = 3) use ($safe_name, $safe_site): string {
            shuffle($templates);
            $html = '';
            foreach (array_slice($templates, 0, $count) as $tpl) {
                $html .= '<p>' . sprintf($tpl, $safe_name, $safe_site) . '</p>';
            }
            return $html;
        };

        $content  = '<p>' . sprintf('%s welcomes visitors with a profile that explains their style, schedule, and what viewers can expect from each live show on %s.', $safe_name, $safe_site) . '</p>';
        $content .= '<h2>Intro</h2>' . $build_paragraphs($intro_paragraphs, 3);
        $content .= '<h2>About ' . $safe_name . '</h2>' . $build_paragraphs($about_paragraphs, 3);
        $content .= '<h2>Streaming Style &amp; What to Expect</h2>' . $build_paragraphs($style_paragraphs, 3);
        $content .= '<h2>Schedule Notes</h2>' . $build_paragraphs($schedule_paragraphs, 3);
        $content .= '<h2>Community &amp; Tips</h2>' . $build_paragraphs($community_paragraphs, 3);
        $content .= '<h2>FAQ</h2>' . $build_paragraphs($faq_paragraphs, 3);

        return ['title' => $title, 'meta' => $meta, 'keywords' => $keywords, 'content' => $content];
    }
    /* helpers */
    protected function html(array $blocks): string {
        $out = '';
        foreach ($blocks as $b) {
            $tag = $b[0];
            $txt = $b[1] ?? '';
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
        // New behavior: do not aggressively pad with repeated paragraphs.
        // We accept whatever the base template produces.
        return $content;
    }

    protected function apply_density_guard(string $content, string $focus): string {
        // Old behavior: force at least 8 mentions of the focus keyword
        // by appending more paragraphs with the name.
        //
        // New behavior: leave content as-is so keyword density stays lower
        // and RankMath doesn't complain about over-optimization.
        return $content;
    }
}
