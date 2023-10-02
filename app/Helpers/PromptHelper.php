<?php

namespace App\Helpers;

use App\Enums\Tone;
use Illuminate\Support\Facades\Lang;

class PromptHelper
{
    protected string $language;

    public function __construct($language = 'en')
    {
        $this->language = $language;
    }

    public function generic($prompt, $text)
    {
        return Lang::get('prompt.generic_prompt', ['prompt' => $prompt, 'text' => $text], $this->language);
    }

    public function summarize($text)
    {
        return Lang::get('prompt.summarize_text', ['text' => $text], $this->language);
    }

    public function simplify($text)
    {
        return Lang::get('prompt.simplify_text', ['text' => $text], $this->language);
    }

    public function paraphrase($text, $tone = 'normal')
    {
        return Lang::get('prompt.paraphrase_text', ['text' => $text, 'tone' => $tone], $this->language);
    }

    public function translate($text, $targetLanguage)
    {
        return Lang::get('prompt.translate_text', [
            'text' => $text,
            'target_language' => $targetLanguage
        ], $this->language);
    }

    public function writeFirstPass($outline, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        $prompt = Lang::get('prompt.blog_first_pass', ['tone' => $tone, 'outline' => $outline], $this->language);
        if ($params['style'] ?? false) {
            $prompt .= Lang::get('prompt.style_instructions', ['style' => $params['style']], $this->language);
        }
        return $prompt;
    }

    public function writeTitle($context, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        $prompt = Lang::get('prompt.write_title', ['context' => $context, 'tone' => $tone], $this->language);
        if ($params['keyword'] ?? false) {
            $prompt .= Lang::get('prompt.keyword_instructions', ['keyword' => $params['keyword']], $this->language);
        }

        return $prompt;
    }

    public function writeOutline($context, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        return Lang::get('prompt.write_outline', [
            'tone' => $tone,
            'maxsubtopics' => $params['maxsubtopics'],
            'context' => $context,
            'style' => $params['style'],
            'keyword' => $params['keyword']
        ], $this->language);
    }


    public function givenFollowingText($text)
    {
        return Lang::get('prompt.given_following_text', ['text' => $text], $this->language);
    }

    public function andGivenFollowingContext($text)
    {
        return Lang::get('prompt.given_following_context', ['context' => preg_replace('/\s+/', ' ', $text)], $this->language);
    }

    public function expandOn($text, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        $prompt = Lang::get('prompt.expand_text', ['tone' => $tone, 'context' => $text], $this->language);
        if ($params['style'] ?? false) {
            $prompt .= Lang::get('prompt.style_instructions', ['style' => $params['style']], $this->language);
        }
        return $prompt;
    }

    public function writeMetaDescription($text, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        $prompt = Lang::get('prompt.write_meta_description', [], $this->language);
        if ($params['keyword'] ?? false) {
            $prompt .= Lang::get('prompt.keyword_instructions', ['keyword' => $params['keyword']], $this->language);
        }
        $prompt .= Lang::get('prompt.tone_instructions', ['tone' => $tone], $this->language);
        $prompt .= Lang::get('prompt.meta_description_context_instructions', ['context' => $text], $this->language);

        return $prompt;
    }

    public function writeSocialMediaPost($context, array $params)
    {
        $tone = Tone::fromLanguage($params['tone'] ?? 'casual', $this->language);
        $prompt = Lang::get('social_media_prompt.write_social_media_post', ['platform' => $params['platform']], $this->language);
        if ($params['platform'] === 'twitter') {
            $prompt .= Lang::get('social_media_prompt.max_words', ['max' => 35], $this->language);
        }
        if ($params['keyword'] ?? false) {
            $prompt .= Lang::get('social_media_prompt.keyword_instructions', ['keyword' => $params['keyword']], $this->language);
        }
        if ($params['style'] ?? false) {
            $prompt .= Lang::get('social_media_prompt.style_instructions', ['style' => $params['style']], $this->language);
        }
        $prompt .= Lang::get('social_media_prompt.tone_instructions', ['tone' => $tone], $this->language);
        $prompt .= Lang::get('social_media_prompt.context_instructions', ['context' => $context], $this->language);
        if ($params['more_instructions']) {
            $prompt .= Lang::get('social_media_prompt.more_instructions', ['instructions' => $params['more_instructions']], $this->language);
        }
        return $prompt;
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;
    }
}
