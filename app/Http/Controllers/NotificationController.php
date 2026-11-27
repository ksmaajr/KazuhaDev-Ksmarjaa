<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const LABELS = ['created' => 'Key baru dibuat', 'edited' => 'Data key diperbarui', 'extended' => 'Durasi key diperpanjang', 'deleted' => 'Key dihapus', 'revoked' => 'Key dicabut', 'hwid_reset' => 'HWID direset', 'activated' => 'Perangkat diaktifkan', 'order_created' => 'Pesanan baru dibuat', 'order_paid' => 'Pembayaran dikonfirmasi · akses diproses', 'order_cancelled' => 'Pesanan dibatalkan', 'game_created' => 'Game baru ditambahkan', 'game_updated' => 'Konfigurasi game diperbarui', 'product_created' => 'Paket baru tersedia', 'product_updated' => 'Paket diperbarui', 'finance_recorded' => 'Transaksi keuangan dicatat', 'finance_voided' => 'Transaksi di-void', 'admin_created' => 'Admin baru ditambahkan', 'payment_settings_updated' => 'Pengaturan pembayaran diperbarui', 'payment_link_created' => 'Invoice pembayaran dibuat', 'payment_test_completed' => 'Simulasi pembayaran selesai', 'payment_failed' => 'Pembayaran gagal', 'payment_expired' => 'Invoice kedaluwarsa'];

    public function admin(Request $request): JsonResponse
    {
        return $this->feed($request, null);
    }

    public function member(Request $request): JsonResponse
    {
        return $this->feed($request, $request->attributes->get('member_license'));
    }

    private function feed(Request $request, ?LicenseKey $license): JsonResponse
    {
        $input = $request->validate(['after' => ['nullable', 'integer', 'min:0'], 'read' => ['nullable', 'integer', 'min:0']]);
        $query = LicenseEvent::with('licenseKey')->whereIn('action', array_keys(self::LABELS));
        if ($license) {
            $query->where('license_key_id', $license->id);
        }
        $max = (int) (clone $query)->max('id');
        $items = (clone $query)->latest('id')->limit(20)->get();
        $map = fn ($event) => ['id' => $event->id, 'title' => self::LABELS[$event->action], 'message' => ($event->metadata['order_number'] ?? $event->metadata['product_name'] ?? $event->metadata['game_name'] ?? $event->metadata['reference'] ?? $event->licenseKey?->label ?? 'Workspace').' · '.$event->created_at->timezone('Asia/Jakarta')->format('d M H:i').' WIB', 'time' => $event->created_at->toIso8601String(), 'details_url' => route($license ? 'member.records.show' : 'records.show', ['event', $event->id])];
        $revision = hash('sha256', json_encode([$license ? (clone $query)->max('id') : LicenseEvent::max('id'), $license?->updated_at?->format('U.u'), Game::max('updated_at'), LicenseProduct::max('updated_at'), (int) floor(now()->timestamp / 30)]));

        return response()->json(['scope' => $license ? 'member-'.$license->id : 'admin-'.$request->user()->id, 'latest_id' => $max, 'revision' => $revision, 'server_time' => now()->toIso8601String(), 'items' => $items->map($map), 'new_items' => isset($input['after']) ? (clone $query)->where('id', '>', $input['after'])->orderBy('id')->limit(10)->get()->map($map) : [], 'unread' => (clone $query)->where('id', '>', $input['read'] ?? 0)->count()])->header('Cache-Control', 'private, no-store');
    }
}
