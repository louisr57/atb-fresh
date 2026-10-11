<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Support\Str;

/**
 * Finds existing venues that look like the one being entered, so the user can be
 * warned (not blocked) about probable duplicates. Duplicate venues would defeat
 * the "one event per venue at a time" clash check.
 */
class VenueSimilarityChecker
{
    /**
     * Words that do not help tell venues apart.
     */
    private const NOISE_WORDS = ['the', 'a', 'an', 'of', 'de', 'la', 'le', 'el'];

    /**
     * @return array<int, array{id: int, name: string, city: string, reasons: array<int, string>}>
     */
    public function similarTo(array $attributes, ?int $ignoreVenueId = null): array
    {
        $name = $this->normalize($attributes['venue_name'] ?? '');
        $address = $this->normalize(($attributes['address'] ?? '').' '.($attributes['city'] ?? ''));

        if ($name === '') {
            return [];
        }

        $matches = [];

        Venue::query()
            ->when($ignoreVenueId, fn ($q) => $q->where('id', '!=', $ignoreVenueId))
            ->orderBy('venue_name')
            ->get()
            ->each(function (Venue $venue) use ($name, $address, &$matches) {
                $otherName = $this->normalize($venue->venue_name);
                $otherAddress = $this->normalize($venue->address.' '.$venue->city);
                $reasons = [];

                if ($otherName === $name) {
                    $reasons[] = 'same name (ignoring case, accents and punctuation)';
                } elseif ($otherName !== '' && (str_contains($otherName, $name) || str_contains($name, $otherName))) {
                    $reasons[] = 'one name contains the other';
                } elseif ($this->isCloseSpelling($name, $otherName)) {
                    $reasons[] = 'very similar spelling';
                } elseif ($this->wordsContained($name, $otherName)) {
                    $reasons[] = 'all the words of one name appear in the other';
                }

                if ($address !== '' && $otherAddress === $address) {
                    $reasons[] = 'same address and city (if this is another room in the same building, make sure the name says which room)';
                }

                if (! empty($reasons)) {
                    $matches[] = [
                        'id' => $venue->id,
                        'name' => $venue->venue_name,
                        'city' => $venue->city,
                        'reasons' => $reasons,
                    ];
                }
            });

        return $matches;
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $words = array_filter(explode(' ', $value), fn ($w) => $w !== '' && ! in_array($w, self::NOISE_WORDS, true));

        return implode(' ', $words);
    }

    /**
     * True when every word of the shorter name (at least two words) appears in the
     * longer one, e.g. "Ashram Raices" vs "Ashram GFU Raices".
     */
    private function wordsContained(string $a, string $b): bool
    {
        $wa = array_unique(explode(' ', $a));
        $wb = array_unique(explode(' ', $b));
        [$short, $long] = count($wa) <= count($wb) ? [$wa, $wb] : [$wb, $wa];

        return count($short) >= 2 && empty(array_diff($short, $long));
    }

    private function isCloseSpelling(string $a, string $b): bool
    {
        if ($a === '' || $b === '' || strlen($a) > 255 || strlen($b) > 255) {
            return false;
        }

        $longest = max(strlen($a), strlen($b));
        $threshold = max(2, (int) floor($longest * 0.2));

        return levenshtein($a, $b) <= $threshold;
    }
}
