<?php

namespace App\Service;

class SensitiveWordFilter
{
    private array $bannedWords = [
        'kill', 'rape', 'murder', 'hate',
        'gay', 'lesbian', 'queer', 'tranny',
        'faggot', 'fag', 'dyke', 'homo',
        // Add more as needed
    ];

    public function isSafe(string $text): bool
    {
        $lowerText = strtolower($text);
        foreach ($this->bannedWords as $word) {
            if (stripos($lowerText, $word) !== false) {
                return false;
            }
        }
        return true;
    }

    public function filter(string $text): string
    {
        foreach ($this->bannedWords as $word) {
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '***', $text);
        }
        return $text;
    }
}
