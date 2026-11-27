<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class PaymentSettings
{
    public static function mode(): string
    {
        return Setting::valueFor('payment_mode', 'sandbox') === 'production' ? 'production' : 'sandbox';
    }

    public static function secret(string $name, ?string $mode = null): string
    {
        $value = Setting::valueFor('sumopod_'.($mode ?? self::mode()).'_'.$name);
        if ($value === '') {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return '';
        }
    }

    public static function ready(?string $mode = null): bool
    {
        return self::unavailableReason($mode) === null;
    }

    public static function unavailableReason(?string $mode = null): ?string
    {
        $mode ??= self::mode();
        $environment = $mode === 'production' ? 'Production' : 'Demo / Sandbox';

        if (Setting::valueFor('payment_enabled', '0') !== '1') {
            return 'Pembayaran online belum diaktifkan oleh admin.';
        }
        if ($mode === 'production' && app()->environment('local')) {
            return 'Pembayaran Production tidak tersedia di server lokal. Gunakan Demo / Sandbox untuk pengujian.';
        }
        if (self::secret('api_key', $mode) === '') {
            return 'Pembayaran online sudah diaktifkan, tetapi API key '.$environment.' belum dikonfigurasi atau tidak dapat dibaca.';
        }
        if (self::secret('webhook_secret', $mode) === '' && self::secret('webhook_token', $mode) === '') {
            return 'Pembayaran online sudah diaktifkan, tetapi verifikasi webhook '.$environment.' belum dikonfigurasi. Admin perlu mengisi Signing secret webhook (Svix) atau Token webhook untuk lingkungan ini.';
        }

        return null;
    }

    public static function saveSecret(string $name, string $value, string $mode): void
    {
        Setting::updateOrCreate(['name' => 'sumopod_'.$mode.'_'.$name], ['value' => Crypt::encryptString($value)]);
    }
}
