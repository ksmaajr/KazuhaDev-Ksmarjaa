<?php

namespace App\Http\Controllers;

use App\Models\LicenseEvent;
use App\Models\Setting;
use App\Support\PaymentSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function edit(): View
    {
        $configured = [];
        foreach (['sandbox', 'production'] as $mode) {
            foreach (['api_key', 'webhook_secret', 'webhook_token'] as $key) {
                $configured[$mode][$key] = PaymentSettings::secret($key, $mode) !== '';
            }
        }

        return view('payment-settings', ['mode' => PaymentSettings::mode(), 'enabled' => Setting::valueFor('payment_enabled', '0') === '1', 'configured' => $configured, 'gatewayIssue' => PaymentSettings::unavailableReason()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = ['mode' => ['required', 'in:sandbox,production'], 'enabled' => ['nullable', 'boolean']];
        foreach (['sandbox', 'production'] as $mode) {
            foreach (['api_key', 'webhook_secret', 'webhook_token'] as $key) {
                $rules[$mode.'_'.$key] = ['nullable', 'string', 'max:1024'];
            }
        }
        $data = $request->validate($rules);
        DB::transaction(function () use ($data, $request): void {
            foreach (['sandbox', 'production'] as $mode) {
                foreach (['api_key', 'webhook_secret', 'webhook_token'] as $key) {
                    if (filled($data[$mode.'_'.$key] ?? null)) {
                        PaymentSettings::saveSecret($key, trim($data[$mode.'_'.$key]), $mode);
                    }
                }
            }
            Setting::updateOrCreate(['name' => 'payment_mode'], ['value' => $data['mode']]);
            Setting::updateOrCreate(['name' => 'payment_enabled'], ['value' => ! empty($data['enabled']) ? '1' : '0']);
            LicenseEvent::create(['user_id' => $request->user()->id, 'action' => 'payment_settings_updated', 'ip_address' => $request->ip(), 'metadata' => ['mode' => $data['mode'], 'enabled' => ! empty($data['enabled'])]]);
        });

        $message = 'Pengaturan pembayaran disimpan. Nilai rahasia kosong mempertahankan konfigurasi sebelumnya.';
        if (Setting::valueFor('payment_enabled', '0') === '1' && ($issue = PaymentSettings::unavailableReason()) !== null) {
            $message .= ' Perlu dilengkapi: '.$issue;
        }

        return redirect()->route('payments.settings')->with('success', $message);
    }
}
