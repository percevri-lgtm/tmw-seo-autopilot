<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

class Template {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name = $c['name'];
        $hook = $c['hook'];
        $site = $c['site'];
        $num = $c['highlights_count'] ?? 7;
        $title = sprintf('%s — %d Live Cam Highlights', $name, $num);
        $meta  = sprintf('%s in a short highlight reel with direct links to live chat and profile on Top Models Webcam.', $name);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));

        $lead = sprintf('%s opens this %s collection with two steady beats that feel like a guided tour instead of a teaser.', $name, strtolower($hook));
        $lead .= sprintf(' %s keeps the focus on confident posture and balanced breathing so the first cut already hints at what private show moments can become.', $name);

        $intro_paragraphs = [
            "$name anchors the introduction by narrating what each segment covers, from warm-up glances to the decisive pose that frames the transition into private shows.",
            "With every new angle, $name explains where the reel connects to live chat, highlighting the cues that signal when an exclusive move is about to land.",
            "Scheduling notes land early: evenings lean toward color-rich sets, late nights switch to monochrome silhouettes, and morning drops act as quick check-ins for fans before work.",
            "$name also points to $site resources so viewers know where the teaser ends and the full live experience continues without confusion.",
        ];

        $highlight_paragraphs = [
            "$name spends the first three highlights on tight camera work that shows fingertips, hair, and the soft glow of the set, proving that quiet detail can still feel cinematic.",
            "Midway through, the soundtrack shifts to a mellow beat; $name syncs each gesture with that tempo so the viewer understands how the private show flow will feel.",
            "Chapters six and seven turn into a direct invitation, mixing smiles, slow turns, and whispered prompts that make the countdown to live chat unmistakable.",
            "$name rounds out the highlight reel with a practical reminder to keep notifications on, because pop-up sessions sometimes appear without warning.",
        ];

        $rhythm_paragraphs = [
            "Every transition uses a gentle dissolve so the energy never drops; even between scenes $name keeps eye contact with the camera to maintain momentum.",
            "Lighting cues stay simple: a cool wash for calm beats, a warmer tone for crescendo moments, and subtle shadows whenever the reel needs intrigue.",
            "$name rehearsed each chapter with a focus on breathing, so the pacing mirrors the kind of controlled confidence fans love in private rooms.",
        ];

        $prep_paragraphs = [
            "To prep for live chat, $name recommends loading playlists, setting room lights to match the reel, and joining a minute before the posted schedule for surprise opener moves.",
            "Community notes call out regulars who drop kind tips in chat; those shout-outs keep the vibe warm and encourage newcomers to say hello.",
            "After the final highlight, $name points everyone back to the profile page for photos, wardrobe polls, and an archive of previous teasers that inspire the next session.",
        ];

        $blocks = [
            ['p', $lead],
            ['raw', $this->mini_toc()],
            ['h2', 'Intro', ['id' => 'intro']],
        ];
        foreach ($intro_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', 'Highlights', ['id' => 'highlights']];
        foreach ($highlight_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        if (!empty($c['model_url'])) {
            $blocks[] = ['raw', '<p>Read more about ' . esc_html($name) . ' on the <a href="' . esc_url($c['model_url']) . '">full profile</a> to see photos, notes, and schedule updates.</p>'];
        }
        $blocks[] = ['h2', 'Rhythm & Structure'];
        foreach ($rhythm_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        $blocks[] = ['h2', 'Live Chat Prep'];
        foreach ($prep_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }
        if (!empty($c['brand_url'])) {
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Jump into ' . esc_html($name) . ' live chat</a> whenever the highlights spark a mood shift.</p>'];
        }

        $faq = [
            ["When is {$name} usually online?", 'Most evenings with occasional weekend sessions; check profile notes.'],
            ["What’s in this reel?", 'A sequence of seven chapters mixing close-ups, pacing notes, and chat-ready cues.'],
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
        $title = sprintf('%s — Live Cam Model Profile & Schedule', $name);
        $meta  = sprintf('%s on %s. Profile, photos, schedule tips, and live chat links. Follow %s for highlights and updates.', $name, $site, $name);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));

        $lead = sprintf('%s introduces this profile with a calm overview of what their room feels like, from the music choices to the steady welcome message that keeps new visitors comfortable.', $name);

        $intro_paragraphs = [
            'The opening section explains how this model frames each session with gentle lighting, soft camera moves, and a relaxed chat pace so everyone can settle in without pressure. A few favorite playlists rotate through the week, keeping mornings mellow and evenings upbeat. Notes on etiquette are written in plain language, making it easy for first-time visitors to say hello or ask about upcoming shows.',
            'A short biography shares how the performer approaches online shows as a creative outlet. They break down how storytelling shapes each broadcast, why pacing matters, and how curiosity drives new ideas. Instead of rushing, the profile highlights the value of steady conversation, thoughtful replies, and small moments of humor that bring viewers back.',
        ];

        $style_paragraphs = [
            sprintf('%s describes their on-cam style as a mix of playful chat and calm confidence. The performer likes to set themes for the week—cozy lounge vibes one day, bold colors the next—so regulars know what mood to expect. The tone stays friendly and respectful, with room for inside jokes that make long-time fans feel seen.', $name),
            'Another paragraph covers how this model uses props, lighting gels, and subtle camera angles to keep each session fresh without overwhelming the room. Music and pacing adjust based on chat energy: chill beats when conversations get deeper, brighter playlists when the vibe turns celebratory. Viewers learn that private room moments simply extend the same care with a bit more focus on one-on-one interaction.',
        ];

        $schedule_paragraphs = [
            sprintf('Scheduling is straightforward: the performer usually signs on in the early evening, with extra weekend slots for fans in different time zones. %s posts reminders in advance so followers can plan around work or study hours. If a surprise stream pops up, the banner on the profile updates first.', $name),
            'A longer scheduling note explains how daylight savings shifts are handled and why the performer sometimes runs short daytime check-ins. These quick sessions act like previews, giving everyone a chance to share ideas for the next full-length show. Time-zone conversions are spelled out clearly, and a recurring calendar link keeps fans from guessing when to drop by.',
        ];

        $experience_paragraphs = [
            sprintf('Public rooms stay welcoming with open chat, easy-going icebreakers, and light-hearted polls. %s mentions that private time keeps the same respectful tone while letting conversations move at a slower, more personal pace. The profile clarifies that boundaries stay firm so everyone knows the space is safe and considerate.', $name),
            'Expectations are set with examples: public segments might showcase new playlists, outfit previews, or a guided tour of recent photos, while private sessions focus on deeper conversation and tailored requests within the posted rules. The performer emphasizes that clear communication keeps the experience smooth for both sides.',
        ];

        $support_paragraphs = [
            sprintf('Following and supporting this model is easy: bookmark the profile, tap notifications, and leave thoughtful messages after each session. %s thanks fans regularly and highlights how small gestures—poll votes, playlist suggestions, or kind DMs—help shape the next broadcast.', $name),
            'Subscribers learn how to join community playlists, participate in seasonal theme votes, and share feedback on camera setups. There are tips for tipping responsibly, using platform tools to stay anonymous if desired, and keeping chat friendly so newcomers feel welcome. Links to the schedule and photo gallery are grouped together for quick access.',
        ];

        $toc = '<nav class="tmw-mini-toc">
  <a href="#intro">Intro</a> · <a href="#style">Style</a> · <a href="#schedule">Schedule</a> · <a href="#experience">Experience</a> · <a href="#support">Support</a> · <a href="#faq">FAQ</a>
</nav>';

        $blocks = [
            ['p', $lead],
            ['raw', $toc],
            ['h2', 'Intro', ['id' => 'intro']],
        ];
        foreach ($intro_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        $blocks[] = ['h2', 'Style & Personality', ['id' => 'style']];
        foreach ($style_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        $blocks[] = ['h2', 'Schedule & Time Zones', ['id' => 'schedule']];
        foreach ($schedule_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        $blocks[] = ['h2', 'Public vs Private Expectations', ['id' => 'experience']];
        foreach ($experience_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        $blocks[] = ['h2', 'Follow & Support', ['id' => 'support']];
        foreach ($support_paragraphs as $p) {
            $blocks[] = ['p', $p];
        }

        if (!empty($c['brand_url'])) {
            $blocks[] = ['raw', '<p class="tmwseo-inline-cta"><a href="' . esc_url($c['brand_url']) . '" rel="sponsored nofollow noopener" target="_blank">Join the next live chat</a> whenever the schedule note shows the room is open.</p>'];
        }

        $faq = [
            ['When is the performer usually online?', 'Most sessions start in the early evening with extra weekend slots; check the schedule notes for updates.'],
            ['What is the vibe in public chat?', 'Light conversation, music requests, polls, and previews of photos or upcoming themes.'],
            ['How do private sessions differ?', 'They keep the respectful tone of public chat while offering slower, more personal conversation within posted boundaries.'],
            ['How can fans support?', 'Turn on notifications, join polls, leave encouraging messages, and follow the schedule to drop in when the room goes live.'],
        ];
        $blocks[] = ['h2', 'FAQ', ['id' => 'faq']];
        $blocks = array_merge($blocks, $this->faq_html($faq));

        $content = $this->html($blocks);

        $max_name_mentions = 10;
        $mentions = substr_count(strtolower($content), strtolower($name));
        if ($mentions > $max_name_mentions) {
            $excess = $mentions - $max_name_mentions;
            $content = preg_replace('/' . preg_quote($name, '/') . '/i', 'this model', $content, $excess);
        }

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
