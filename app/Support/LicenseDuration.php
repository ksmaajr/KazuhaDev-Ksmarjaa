<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class LicenseDuration
{
    public static function rules(array $input, bool $required = true): array
    {
        return [
            'days' => [$required ? 'required_without_all:hours,minutes' : 'sometimes', Rule::prohibitedIf(array_key_exists('hours', $input) || array_key_exists('minutes', $input)), 'integer', 'between:1,30'],
            'hours' => ['required_with:minutes', 'integer', 'between:0,720'],
            'minutes' => ['required_with:hours', 'integer', 'between:0,59', function (string $attribute, mixed $value, \Closure $fail) use ($input): void {
                $total = (int) ($input['hours'] ?? 0) * 60 + (int) $value;
                if ($total < 1 || $total > 43200) {
                    $fail('Durasi harus antara 1 menit dan 720 jam (30 hari).');
                }
            }],
        ];
    }

    public static function seconds(array $data): int
    {
        return array_key_exists('hours', $data)
            ? (int) $data['hours'] * 3600 + (int) $data['minutes'] * 60
            : (int) $data['days'] * 86400;
    }

    public static function label(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($minutes === 0) {
            return $hours.' jam';
        }

        return $hours > 0 ? $hours.' jam '.$minutes.' menit' : $minutes.' menit';
    }

    public static function productData(array $data): array
    {
        $data['duration_seconds'] = self::seconds($data);
        $data['game_ids'] = array_map('intval', $data['game_ids']);
        $data['days'] = (int) ceil($data['duration_seconds'] / 86400);
        unset($data['hours'], $data['minutes']);

        return $data;
    }
}
