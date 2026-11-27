<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['name', 'value'];

    public static function valueFor(string $name, string $default = ''): string
    {
        return (string) (static::where('name', $name)->value('value') ?? $default);
    }

    public static function renewalUrl(LicenseKey $license): ?string
    {
        $number = static::valueFor('whatsapp_number');
        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode('Halo Admin, saya ingin memperpanjang lisensi #'.$license->id.' ('.$license->label.'). Mohon informasi paket dan pembayaran.');
    }

    public static function apiBaseUrl(): string
    {
        return rtrim(static::valueFor('public_url', config('app.url')), '/').'/api/v1';
    }
}
