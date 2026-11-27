<?php

namespace App\Http\Middleware;

use App\Models\LicenseKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $license = LicenseKey::find($request->session()->get('member_license_id'));
        if (! $license || $license->revoked_at) {
            $request->session()->forget('member_license_id');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'code' => 'member_session_expired'], 401);
            }

            return redirect()->route('member.login')->withErrors(['key' => 'Sesi berakhir atau key sudah tidak tersedia.']);
        }
        $request->attributes->set('member_license', $license);

        return $next($request);
    }
}
