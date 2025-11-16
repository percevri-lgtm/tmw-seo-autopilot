<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

class Template {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name = $c['name'];
        $hook = $c['hook'];
        $site = $c['site'];
        $num = max(3, (int) ($c['highlights_count'] ?? 7));
        $title = sprintf('%s — %d Must-See Highlights (Private Show)', $name, $num);
        $meta = sprintf('%s trims %d scenes into a quick reel with direct links to live chat and the profile hub on %s.', $name, $num, $site);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));

        $lead = sprintf('%s frames this %s reel as a primer for %s visitors who want context before hitting the live button.', $name, strtolower($hook), $site);
        $lead .= sprintf(' Each beat previews how %s escalates the private show and reminds viewers where to tap for live chat without missing a chapter.', $name);

        $intro_paragraphs = [
            "$name narrates what each scene represents so fans know which portion of the schedule they are watching and how the highlight connects to private rooms.",
            "Camera changes are labeled on screen, and the notes reference the $site blog so returning viewers can track how the production evolves week to week.",
            "The first minutes mention community cues, preferred emojis, and the moment $name usually signals that a surprise set is about to start.",
            "$name also lists which teasers lead directly to the profile gallery for anyone who wants extra photos before committing to live chat.",
        ];

        $highlight_paragraphs = [
            sprintf('%s spends the early highlights on macro shots—hands, eyes, fabric—so the reel has texture before the pace increases.', $name),
            sprintf('Chapters three through five show how %s mirrors the soundtrack, matching each transition to breathing patterns to avoid jump cuts.', $name),
            sprintf('The final two highlights flip to POV angles and whispered CTAs that make the countdown to live chat unmistakable for anyone skimming.', $name),
            sprintf('%s wraps the highlight pass with a reminder to keep push alerts on because pop-up sessions appear without warning.', $name),
        ];

        $rhythm_paragraphs = [
            "Every transition relies on soft dissolves so the reel feels like a single take even when the camera swaps sides mid-sentence.",
            "$name keeps lighting palettes simple—cool for calm beats, warm when the reel leans into private show energy—so the mood shifts are obvious to new viewers.",
            "$name rehearsed each sequence with a breath-count method so fans know exactly how the pacing inside a live room will feel.",
        ];

        $prep_paragraphs = [
            "$name suggests prepping playlists, setting room brightness to match the reel, and joining chat a minute early to catch any surprise openers.",
            "Community shout-outs mention regulars who keep the vibe positive, encouraging newcomers to introduce themselves when they jump in.",
            "After the highlights wrap, everyone is pointed back to the profile for wardrobe polls, studio notes, and archived teasers for reference.",
        ];

        $toc = $this->mini_toc($name, $hook);

        $blocks = [
            ['p', $lead],
            ['raw', $toc],
            ['h2', sprintf('%s %s overview', $name, $hook), ['id' => 'intro']],
        ];
        foreach ($intro_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', sprintf('%s highlights & key moments', $name), ['id' => 'highlights']];
        foreach ($highlight_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        if (!empty($c['model_url'])) {
            $blocks[] = ['raw', '<p>Read more about ' . esc_html($name) . ' on the <a href="' . esc_url($c['model_url']) . '">full profile</a> for gallery links, wardrobe polls, and the latest notes.</p>'];
        }
        $blocks[] = ['h2', sprintf('%s – rhythm & structure', $name), ['id' => 'rhythm']];
        foreach ($rhythm_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', sprintf('Live chat with %s – prep tips', $name), ['id' => 'prep']];
        foreach ($prep_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        if (!empty($c['brand_url'])) {
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Jump into ' . esc_html($name) . ' live chat</a> whenever the highlights spark a mood shift.</p>'];
        }

        $faq = [
            ["When is {$name} usually online?", 'Most evenings with occasional weekend sessions; check the banner for changes.'],
            ["What’s in this reel?", 'A sequence of ' . $num . ' chapters mixing close-ups, pacing notes, and chat-ready cues.'],
            ["How do I join live chat?", 'Use the “Join ' . $name . ' live chat” button or the inline link on this page.'],
            ["Is there more content?", 'Yes—visit the model profile for photos, schedule notes, wardrobe polls, and teasers.'],
        ];
        $blocks[] = ['h2', "$name — FAQ", ['id' => 'faq']];
        $blocks = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);
        $content = $this->enforce_word_goal($content, $name);
        $content = $this->apply_density_guard($content, $name);

        return ['title' => $title, 'meta' => $meta, 'keywords' => $keywords, 'content' => $content];
    }

    /** MODEL: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_model(array $c): array {
        $name = $c['name'];
        $site = $c['site'];
        $title = sprintf('%s — Live Chat & Profile', $name);
        $meta = sprintf('%s on %s. Photos, schedule tips, and live chat links. Follow %s for updates and teasers.', $name, $site, $name);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));

        $lead = sprintf('%s opens the profile with a conversational summary that outlines live chat themes, favorite playlists, and how %s keeps regulars looped in.', $name, $name);
        $lead .= sprintf(' The first paragraph feels like a DM so newcomers understand why the %s community keeps returning.', $site);

        $intro_paragraphs = [
            "$name mixes editorial-inspired photos with studio snapshots that show how the lighting changes between public and private rooms.",
            "The intro also lists warm-up rituals, pre-show polls, and the subtle cues that signal when a surprise session might pop up.",
            "$name emphasizes community guidelines that keep the profile welcoming, highlighting how respectful vibes lead to more adventurous shows.",
        ];

        $highlight_paragraphs = [
            "$name keeps the highlights section organized by mood: chill mornings, bold evening looks, and limited seasonal shoots that only appear once per quarter.",
            "Each gallery includes notes about wardrobe, camera gear, and post-production choices so aspiring creators can learn from the workflow.",
            "$name also drops little anecdotes about memorable fans, emphasizing how genuine chats inspire the next shoot.",
            "Schedule blocks show when $name prefers structured shows versus spontaneous drop-ins, giving subscribers a practical planning tool.",
        ];

        $community_paragraphs = [
            "Community tips remind followers to bookmark the page, enable notifications, and vote in polls that decide future looks.",
            "$name shares gratitude posts after each milestone, celebrating moderators, gifters, and lurkers who keep the vibe balanced.",
            "A quick rundown of backstage routines—stretching, lighting tests, playlist swaps—helps fans appreciate the craft behind each live chat.",
        ];

        $toc = $this->mini_toc($name);

        $blocks = [
            ['p', $lead],
            ['raw', $toc],
            ['h2', sprintf('About %s', $name), ['id' => 'intro']],
        ];
        foreach ($intro_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', sprintf('%s highlights', $name), ['id' => 'highlights']];
        foreach ($highlight_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', sprintf('%s community & tips', $name), ['id' => 'community']];
        foreach ($community_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        if (!empty($c['brand_url'])) {
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Chat live with ' . esc_html($name) . '</a> whenever the profile updates inspire you.</p>'];
        }
        $faq = [
            ["About $name", "$name keeps the layout clean so you can find highlights, new posts, and live times quickly."],
            ["How often is $name live?", 'Most evenings; check the banner note for changes.'],
            ['How to support ' . $name . '?', 'Join live chat, bookmark the profile, leave kind feedback, and share favorite posts.'],
            ['Where to see teasers?', 'Scroll to the media section for short clips, wardrobe polls, and behind-the-scenes snapshots.'],
        ];
        $blocks[] = ['h2', "$name — FAQ", ['id' => 'faq']];
        $blocks = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);
        $content = $this->enforce_word_goal($content, $name);
        $content = $this->apply_density_guard($content, $name);

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

    protected function mini_toc(string $name, string $hook = 'highlights'): string {
        $sections = [
            '#intro' => sprintf('About %s', $name),
            '#highlights' => sprintf('%s %s', $name, $hook),
            '#faq' => sprintf('%s FAQ', $name),
        ];
        $links = [];
        foreach ($sections as $href => $label) {
            $links[] = '<a href="' . esc_attr($href) . '">' . esc_html($label) . '</a>';
        }
        return '<nav class="tmw-mini-toc">' . implode(' · ', $links) . '</nav>';
    }

    protected function enforce_word_goal(string $content, string $focus, int $min = 900, int $max = 1200): string {
        $content = trim($content);
        $words = str_word_count(wp_strip_all_tags($content));
        if ($words < $min) {
            $extras = [
                'Producers keep refining transitions so every release flows without abrupt jumps, giving space for reaction shots, lighting pivots, and subtle gestures to land.',
                'Editors log how pacing affects mood, then annotate playlists, camera angles, and prop choices so the recap reads like a storyboard instead of a keyword dump.',
                'Each drop weaves in notes about community feedback, showing how polls, chat transcripts, and fan mail quietly shape the rhythm of upcoming sessions.',
                'Behind the scenes, the team tweaks color palettes, trims repetitive beats, and balances cozy language with direct instructions so updates feel conversational.',
            ];
            $i = 0;
            while ($words < $min && $i < 20) {
                $addition = '<p>' . esc_html($extras[$i % count($extras)]) . '</p>';
                $next_words = str_word_count(wp_strip_all_tags($content . $addition));
                if ($next_words > $max) {
                    break;
                }
                $content .= $addition;
                $words = $next_words;
                $i++;
            }
        }

        if ($words > $max) {
            $pattern = '#(.*)(<p[^>]*>.*?</p>)\s*$#is';
            $attempts = 0;
            while ($words > $max && $attempts < 20 && preg_match($pattern, $content, $matches)) {
                if (stripos($matches[2], 'tmwseo-inline-cta') !== false) {
                    break;
                }
                $content = trim($matches[1]);
                $words = str_word_count(wp_strip_all_tags($content));
                $attempts++;
            }
        }

        return $content;
    }

    protected function apply_density_guard(string $content, string $focus): string {
        $count = substr_count(strtolower($content), strtolower($focus));
        if ($count >= 8) {
            return $content;
        }
        $extras = [
            "$focus leans into natural pauses so each fan can breathe before the next reveal.",
            "When scenes get intense, $focus steadies the camera to keep everything intentional.",
            "$focus closes every update with gratitude, keeping the tone supportive and warm.",
        ];
        $i = 0;
        while ($count < 8 && $i < 10) {
            $content .= '<p>' . esc_html($extras[$i % count($extras)]) . '</p>';
            $count++;
            $i++;
        }
        return $content;
    }
}
