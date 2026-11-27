<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('settings', ['publicUrl' => Setting::valueFor('public_url', config('app.url')), 'interval' => Setting::valueFor('heartbeat_interval', '60'), 'whatsapp' => Setting::valueFor('whatsapp_number')]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $name => $value) {
            Setting::updateOrCreate(['name' => $name], ['value' => $name === 'public_url' ? rtrim($value, '/') : (string) $value]);
        }

        return redirect()->route('settings')->with('success', 'Pengaturan endpoint berhasil disimpan.');
    }
}
