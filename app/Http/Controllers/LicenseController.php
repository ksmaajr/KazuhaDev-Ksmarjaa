<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtendLicenseRequest;
use App\Http\Requests\StoreLicenseRequest;
use App\Http\Requests\UpdateLicenseRequest;
use App\Models\LicenseKey;
use App\Services\LicenseService;
use App\Support\LicenseDuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LicenseController extends Controller
{
    public function store(StoreLicenseRequest $request, LicenseService $service): RedirectResponse
    {
        [$license,$key] = $service->issue($request->validated(), $request->user()->id, $request->ip());

        return redirect()->route('licenses')->with('issued_key', $key)->with('success', 'Key berhasil dibuat. Durasi mulai saat aktivasi pertama di injector.');
    }

    public function reset(Request $request, LicenseKey $license, LicenseService $service): RedirectResponse
    {
        $service->reset($license, $request->user()->id, $request->ip());

        return redirect()->route('licenses')->with('success', 'HWID direset. Masa berlaku dan aktivasi pertama tetap.');
    }

    public function revoke(Request $request, LicenseKey $license, LicenseService $service): RedirectResponse
    {
        $service->revoke($license, $request->user()->id, $request->ip());

        return redirect()->route('licenses')->with('success', 'Key dicabut. Request berikutnya akan ditolak.');
    }

    public function destroy(Request $request, LicenseKey $license, LicenseService $service): RedirectResponse
    {
        $service->remove($license, $request->user()->id, $request->ip());

        return redirect()->route('licenses')->with('success', 'Key dihapus. Akses API dan member untuk key ini langsung ditolak.');
    }

    public function extend(ExtendLicenseRequest $request, LicenseKey $license, LicenseService $service): RedirectResponse
    {
        $data = $request->validated();
        try {
            $service->extendSeconds($license, LicenseDuration::seconds($data), $request->user()->id, $request->ip());
        } catch (ValidationException $exception) {
            return redirect()->route('licenses')->withErrors($exception->errors());
        }

        return redirect()->route('licenses')->with('success', 'Durasi ditambahkan '.LicenseDuration::label(LicenseDuration::seconds($data)).'. HWID dan aktivasi pertama tetap.');
    }

    public function update(UpdateLicenseRequest $request, LicenseKey $license, LicenseService $service): RedirectResponse
    {
        $data = $request->validated();
        try {
            $service->edit($license, $data, $request->user()->id, $request->ip());
        } catch (ValidationException $exception) {
            return redirect()->route('licenses')->withErrors($exception->errors());
        }

        return redirect()->route('licenses')->with('success', 'Data license key berhasil diperbarui.');
    }
}
