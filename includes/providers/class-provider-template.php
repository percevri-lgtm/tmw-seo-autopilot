<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

class Template {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name = $c['name'];
        $hook = $c['hook'];
        $site = $c['site'];
        $num = 7;
        $pos = 'Top';
        $power = 'Must-See';
        $title = sprintf('%s — %d %s %s %s', $name, $num, $pos, $power, ucwords($hook));
        $meta = sprintf('%s collects %s in a clean, quick reel with a direct jump to live chat on %s. Photos, teasers, and schedule notes inside.',
            $name, strtolower($hook), $site);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));
        $intro = sprintf('%s presents %d %s %s in a calm, polished reel that mirrors the pace of live chat.',
            $name, $num, strtolower($pos), strtolower($hook));
        $faq = [
            ["When is {$name} usually online?", 'Most evenings with occasional weekend sessions; check profile notes.'],
            ["What’s in this reel?", 'A compact sequence of color-forward chapters with smooth pacing.'],
            ["How do I join live chat?", 'Use the “Join ' . $name . ' live chat” button on the page.'],
            ["Is there more content?", 'Yes—visit the model profile for photos, schedule notes, and updates.'],
        ];
        $content = $this->html([
            ['h2', 'Intro'],
            ['p', $intro],
            ['h2', 'Highlights'],
            ['p', "$name uses steady light, clean frames, and color to guide the mood across seven short chapters."],
            ['p', 'Each segment favors composition over effects, so the set stays rewatchable and device-friendly.'],
            ['h2', 'Style & pacing'],
            ['p', 'The video balances anticipation with resolution—no rush, no drag—so new viewers settle in fast.'],
            ['h2', 'Why it works'],
            ['p', 'Simple structure, thoughtful color, and a consistent rhythm make this easy to enjoy.'],
            ['h2', "$name — FAQ"],
            ...$this->faq_html($faq),
        ]);
        return ['title' => $title, 'meta' => $meta, 'keywords' => $keywords, 'content' => $content];
    }

    /** MODEL: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_model(array $c): array {
        $name = $c['name'];
        $site = $c['site'];
        $title = sprintf('%s — Live Chat & Profile', $name);
        $meta = sprintf('%s on %s. Photos, schedule tips, and live chat links. Follow %s for updates and teasers.', $name, $site, $name);
        $keywords = array_merge([$c['focus']], array_slice($c['extras'], 0, 4));

        $intro = sprintf('%s brings confident, friendly energy to %s. Known for polished visuals and relaxed chat, they blend editorial styling with interactive live sessions.',
            $name, $site);
        $bio = [
            "$name’s profile favors clean compositions and a smooth pace that makes chat feel personal.",
            'Between sessions, expect short teasers and simple updates so you always know what’s next.',
            "Want to catch $name live? Evenings are often best, with weekend pops when the schedule allows.",
        ];
        $faq = [
            ["About $name", "$name keeps the layout clean so you can find highlights, new posts, and live times quickly."],
            ["How often is $name live?", 'Most evenings; check the banner note for changes.'],
            ['How to support ' . $name . '?', 'Join live chat, bookmark the profile, and share favorite posts.'],
        ];
        $content = $this->html([
            ['h2', 'Intro'],
            ['p', $intro],
            ['h2', 'Bio'],
            ['p', implode("\n\n", $bio)],
            ['h2', "$name — FAQ"],
            ...$this->faq_html($faq),
        ]);
        return ['title' => $title, 'meta' => $meta, 'keywords' => $keywords, 'content' => $content];
    }

    /* helpers */
    protected function html(array $blocks): string {
        $out = '';
        foreach ($blocks as $b) {
            [$tag, $txt] = $b;
            if ($tag === 'p') {
                $out .= '<p>' . esc_html($txt) . '</p>';
            } elseif (in_array($tag, ['h2', 'h3'], true)) {
                $out .= '<' . $tag . '>' . esc_html($txt) . '</' . $tag . '>';
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
}
