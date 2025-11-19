<?php
namespace TMW_SEO\Providers;
if (!defined('ABSPATH')) exit;

use TMW_SEO\Core;

class VideoTemplate {
    /** VIDEO: returns ['title','meta','keywords'=>[5],'content'] */
    public function generate_video(array $c): array {
        $name   = $c['name'];
        $site   = $c['site'] ?: 'Live Cam Stream';
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

        $title = sprintf('%s Journey with %s', $focus, $extras[0]);
        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '...';
        }

        $meta = sprintf(
            '%s welcomes viewers while %s, %s, %s, and %s color the pacing with soft PG-13 cues.',
            $focus,
            $extras[0],
            $extras[1],
            $extras[2],
            $extras[3]
        );
        if (mb_strlen($meta) > 160) {
            $meta = mb_substr($meta, 0, 157) . '...';
        }

        $blocks = [];
        $intro_one = sprintf(
            '%s invites viewers into a calm storyline, describing how each beat of the evening will unfold like a guided meditation with pauses for breath and generous smiles. She lets her laughter linger just long enough for people on the other side of the screen to relax their shoulders, exhale slowly, and imagine themselves sharing the same velvet sofa. Gentle context clues explain the pacing, the hand movements, and the promise that nothing rushes the senses, letting anticipation grow naturally. When %s is mentioned, it becomes a metaphor for bright pulses of optimism, warming the edges of the experience without overwhelming the soothing tempo.',
            $focus,
            $extras[0]
        );
        $intro_two = sprintf(
            'Friends who tune in together see how %s frames the chat as a place for empathy, inviting watchers to describe their own day in just a few calm lines before relaxing into the flow. The guide explains how to set lamplight at home to a honey tone, how to rest elbows on a cushion, and how to listen for the soft inhalations that tell you a surprise grin is coming. Detailed notes compare %s to a moonlit sway that gently closes the eyes of any anxiety, promising that each greeting will feel personal even inside a lively room.',
            $extras[1],
            $extras[3]
        );
        $intro_bridge_text = sprintf(
            'A single whisper about %s promises that the later scenes will melt into a mellow glow, so viewers can let their shoulders drop before the first highlight even begins.',
            $extras[2]
        );

        $blocks[] = ['p', $intro_one];
        $blocks[] = ['p', $intro_two];
        $blocks[] = ['raw', '<div class="intro-bridge">' . esc_html($intro_bridge_text) . '</div>'];

        $blocks[] = ['h2', ucwords($extras[0])];
        $section1_p1 = sprintf(
            'The first spotlight explains how %s uses gentle eye contact and playful pauses whenever %s ripples through the narrative, showing fans how to match their breathing to the subtle sway of her hips. She maps out a steady rise and fall with her hands, letting each smile linger so people at home can mirror that patience while counting quietly to four. Commentary reminds everyone to unclench their shoulders, hold eye contact with the screen for a heartbeat, and then release into a playful grin before the next movement.',
            $focus,
            $extras[0]
        );
        $section1_p2 = sprintf(
            'Guides describe %s as a radiant breeze that drifts across the scene, suggesting that fans close their eyes for a second to picture the warm glow before opening them to catch every shrug and playful wink. They recommend tapping a finger against the wrist to mimic the relaxed tempo, letting that rhythm spill into the next exchange of smiles.',
            $extras[0]
        );
        $blocks[] = ['p', $section1_p1];
        $blocks[] = ['p', $section1_p2];

        $blocks[] = ['h2', ucwords($extras[1])];
        $section2_p1 = sprintf(
            'Notes highlight how %s becomes a mindful gathering space, encouraging viewers to type short, kind phrases between sequences and then rest their hands to feel the hush that follows. The coaching explains how to keep the chat gentle, reminding fans to focus on compliments about posture or the sparkle in each laugh instead of racing ahead.',
            $extras[1]
        );
        $section2_p2 = sprintf(
            'Later chapters slow down to show how breathing between responses keeps the entire room serene before the next wave of commentary arrives. During that pause %s tilts her head toward the lens, offering quiet nods that make remote fans feel included. Viewers are urged to lean back for a few seconds and notice how their own smiles echo hers before they rejoin the conversation.',
            $focus
        );
        $blocks[] = ['p', $section2_p1];
        $blocks[] = ['p', $section2_p2];

        $blocks[] = ['h2', ucwords($extras[2])];
        $section3_p1 = sprintf(
            'Writers compare %s to a shoreline stroll, explaining how the slower cadence invites fans to trace each motion with their fingertips resting lightly on their lap. As the description unfolds %s keeps her shoulders low and gaze steady, giving the audience plenty of time to feel each inhale before the next turn. Observers are urged to follow the arc of her palms and to breathe in sync with the gentle count laid out in the guide.',
            $extras[2],
            $focus
        );
        $section3_p2 = 'Additional guidance explains how to stretch wrists gently while tracing the circular motions on screen, then to jot down a feeling or color that surfaces before the next beat arrives. The aim is to make every watcher feel like the tempo is tailored to them, easing anxious energy and replacing it with a soft hum.';
        $blocks[] = ['p', $section3_p1];
        $blocks[] = ['p', $section3_p2];

        $blocks[] = ['h2', ucwords($extras[3])];
        $section4_p1 = sprintf(
            'Coaches portray %s as the moment when time stretches, advising viewers to dim their room lights slightly and settle their palms on their knees so they can feel each breath glide through the chest.',
            $extras[3]
        );
        $section4_p2 = sprintf(
            'When %s rises again, %s draws her chin toward her collarbone, lets a smile bloom slowly, and invites the audience to mirror that patience before switching to live chat. Fans are reminded to sip water, roll their wrists, and savor the glow that stays in the room after the final twirl.',
            $extras[3],
            $focus
        );
        $blocks[] = ['p', $section4_p1];
        $blocks[] = ['p', $section4_p2];

        $blocks[] = ['h3', 'Internal link'];
        $model_url = !empty($c['model_url']) ? $c['model_url'] : '#';
        $internal_link = sprintf(
            'Keep following %s by visiting <a href="%s">%s</a>, where %s moments are archived with gentle notes for future watch parties.',
            esc_html($focus),
            esc_url($model_url),
            esc_html($name),
            esc_html($extras[1])
        );
        $blocks[] = ['raw', '<p class="internal-link">' . $internal_link . '</p>'];

        $blocks[] = ['h2', sprintf('Conclusion with %s', $focus)];
        $blocks[] = ['p', 'The closing reflection reminds viewers to hold on to the calm pulse they cultivated during the session, to journal a few sensations before logging off, and to return whenever they crave another gentle storyline.'];

        $content = $this->html($blocks);
        $word_count = str_word_count(strip_tags($content));
        if ($word_count < 800 || $word_count > 1000) {
            $content = $this->pad_word_count($content, $blocks, $word_count);
        }

        return [
            'title'    => $title,
            'meta'     => $meta,
            'keywords' => $keywords,
            'content'  => $content,
        ];
    }

    protected function pad_word_count(string $content, array $blocks, int $word_count): string {
        if ($word_count >= 800 && $word_count <= 1000) {
            return $content;
        }
        $extras = [
            'A reminder panel encourages viewers to stretch ankles, roll shoulders, and let the quiet hum of the soundtrack linger before pressing play again.',
            'Another gentle sidebar suggests lighting a small candle or opening a window so the fresh air anchors the emotional pace of the story.',
        ];
        foreach ($extras as $extra) {
            if ($word_count >= 800) {
                break;
            }
            $blocks[] = ['p', $extra];
            $content   = $this->html($blocks);
            $word_count = str_word_count(strip_tags($content));
        }
        return $content;
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
