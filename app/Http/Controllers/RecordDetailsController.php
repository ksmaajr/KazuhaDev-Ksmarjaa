<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\Game;
use App\Models\LicenseEvent;
use App\Models\LicenseKey;
use App\Models\LicenseProduct;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Support\LicenseDuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecordDetailsController extends Controller
{
    public function admin(Request $request, string $kind, int $id): JsonResponse
    {
        return $this->show($request, $kind, $id, false);
    }

    public function member(Request $request, string $kind, int $id): JsonResponse
    {
        abort_unless(in_array($kind, ['event', 'order', 'license']), 404);

        return $this->show($request, $kind, $id, true);
    }

    private function show(Request $request, string $kind, int $id, bool $member): JsonResponse
    {
        $rows = [];
        $links = [];
        $timeline = [];
        $ownId = $request->attributes->get('member_license')?->id;
        $route = $member ? 'member.records.show' : 'records.show';
        $date = fn ($value) => $value ? $value->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB' : null;
        $add = function (string $label, mixed $value) use (&$rows): void {
            $rows[] = ['label' => $label, 'value' => $value === null || $value === '' ? 'Belum tercatat' : (string) $value];
        };
        $link = function (string $label, string $type, int $recordId) use (&$links, $route): void {
            $links[] = ['label' => $label, 'url' => route($route, [$type, $recordId])];
        };
        $events = null;
        switch ($kind) {
            case 'order':
                $record = PurchaseOrder::with(['owner', 'issuedLicense'])->findOrFail($id);
                abort_if($member && $record->owner_license_id !== $ownId, 404);
                $title = $record->purpose === 'renewal' ? 'Detail perpanjangan' : 'Detail pesanan lisensi';
                $subtitle = $record->number;
                $add('Nomor pesanan', $record->number);
                $add('Jenis pesanan', $record->purpose === 'renewal' ? 'Perpanjangan key yang sama' : 'Pembelian key baru');
                $add('Status', $record->gateway_mode === 'sandbox' ? 'Simulasi sandbox · '.$record->status : (['pending' => 'Menunggu pembayaran', 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan'][$record->status] ?? $record->status));
                $add('Nama member / pembeli', $record->member_name);
                $add('Kontak pembeli', $record->customer_contact);
                $add('Email pembeli', $record->customer_email);
                $add('Paket saat dipesan', $record->product_name);
                $add('Deskripsi saat dipesan', $record->product_description);
                $add('Durasi pesanan', LicenseDuration::label($record->duration_seconds ?? $record->days * 86400));
                $snapshot = $record->game_snapshot;
                $add('Akses game', $snapshot ? collect($snapshot)->map(fn ($game) => $game['name'].' ('.$game['code'].')')->join(', ') : 'Snapshot nama belum tersedia · ID game: '.implode(', ', $record->game_ids));
                $add('Total pembayaran', 'Rp '.number_format($record->amount, 0, ',', '.'));
                $add('Metode pembayaran', ['manual' => 'Manual / belum dirinci', 'qris' => 'QRIS', 'bank_transfer' => 'Transfer bank', 'e_wallet' => 'E-wallet', 'cash' => 'Tunai'][$record->payment_method] ?? $record->payment_method);
                $add('Penyedia pembayaran', $record->payment_provider);
                $add('Nama pengirim dana', $record->payer_name);
                $add('Referensi pembayaran', $record->payment_reference);
                $add('Waktu pembayaran', $record->paid_at ? $date($record->paid_at) : null);
                $add('Waktu verifikasi', $record->verified_at ? $date($record->verified_at) : null);
                $add('ID pembayaran gateway', $record->gateway_payment_id);
                $add('Status gateway', $record->gateway_status);
                $add('Lingkungan gateway', $record->gateway_mode);
                if (! $member) {
                    $add('Diverifikasi oleh', $record->verified_by ? User::find($record->verified_by)?->name : ($record->payment_provider === 'SumoPod' && $record->status === 'paid' ? 'Webhook SumoPod terverifikasi' : null));
                    $add('Catatan pembayaran', $record->payment_notes);
                }
                $add('Key tujuan / asal', $record->target_key_hint ?? $record->owner?->key_hint);
                $add('Key diterbitkan', $record->issuedLicense?->key_hint);
                $add('Dibuat', $date($record->created_at));
                $add('Dibatalkan', $record->cancelled_at ? $date($record->cancelled_at) : null);
                $add('Alasan pembatalan', $record->cancellation_reason);
                $events = LicenseEvent::where('metadata->order_number', $record->number);
                if (! $member && $record->owner_license_id) {
                    $link('Lihat key asal', 'license', $record->owner_license_id);
                }
                if ($record->issued_license_id && ! $member) {
                    $link('Lihat key hasil pembelian', 'license', $record->issued_license_id);
                }
                if (! $member && ($ledger = FinancialTransaction::where('order_id', $id)->first())) {
                    $link('Lihat transaksi keuangan', 'transaction', $ledger->id);
                }
                break;
            case 'event':
                $record = LicenseEvent::with('licenseKey')->findOrFail($id);
                abort_if($member && $record->license_key_id !== $ownId, 404);
                $title = 'Detail aktivitas';
                $subtitle = 'EVENT #'.$record->id;
                $add('ID aktivitas', '#'.$record->id);
                $add('Aktivitas', str_replace('_', ' ', $record->action));
                $add('Waktu (WIB)', $date($record->created_at));
                $add('Nama key', $record->licenseKey?->label);
                $add('Member', $record->licenseKey?->member_name);
                $add('Key', $record->licenseKey?->key_hint);
                if (! $member) {
                    $add('Pelaku', $record->user_id ? User::find($record->user_id)?->name : 'Sistem / member / API');
                    $add('Alamat IP', $record->ip_address);
                }
                foreach (['order_number' => 'Nomor pesanan', 'product_name' => 'Nama paket', 'game_name' => 'Game', 'game_code' => 'Kode game', 'reference' => 'Referensi transaksi', 'amount' => 'Nominal (IDR)', 'payment_method' => 'Metode bayar', 'payment_reference' => 'Referensi pembayaran', 'target_email' => 'Email admin', 'version' => 'Versi'] as $key => $label) {
                    if (isset($record->metadata[$key]) && (! $member || $key !== 'target_email')) {
                        $add($label, $record->metadata[$key]);
                    }
                }
                if ($number = $record->metadata['order_number'] ?? null) {
                    $order = PurchaseOrder::where('number', $number)->first();
                    if ($order && (! $member || $order->owner_license_id === $ownId)) {
                        $link('Lihat detail pesanan', 'order', $order->id);
                    }
                }
                if (! $member && ($ref = $record->metadata['reference'] ?? null) && ($ledger = FinancialTransaction::where('reference', $ref)->first())) {
                    $link('Lihat transaksi', 'transaction', $ledger->id);
                }
                if ($record->license_key_id) {
                    $link('Lihat detail key', 'license', $record->license_key_id);
                }
                if (! $member && ($name = $record->metadata['product_name'] ?? null) && ($product = LicenseProduct::where('name', $name)->first())) {
                    $link('Lihat paket saat ini', 'product', $product->id);
                }
                break;
            case 'license':
                $record = LicenseKey::withTrashed()->with('games')->findOrFail($id);
                abort_if($member && $record->id !== $ownId, 404);
                $title = 'Detail license key';
                $subtitle = $record->label;
                foreach (['ID key' => '#'.$record->id, 'Member' => $record->member_name, 'Label' => $record->label, 'Key' => $record->key_hint, 'Status' => $record->trashed() ? 'Dihapus' : $record->status(), 'Durasi alokasi' => LicenseDuration::label($record->duration_seconds), 'Akses game' => $record->games->pluck('name')->join(', '), 'Dibuat' => $date($record->created_at), 'Aktivasi' => $record->activated_at ? $date($record->activated_at) : 'Belum aktif', 'Expired' => $record->expires_at ? $date($record->expires_at) : 'Belum dimulai', 'Reset HWID' => $record->reset_count.' kali', 'Terakhir terhubung' => $record->last_seen_at ? $date($record->last_seen_at) : null] as $label => $value) {
                    $add($label, $value);
                }
                if (! $member) {
                    $created = $record->events()->where('action', 'created')->oldest('id')->first();
                    $snapshot = $created?->metadata['issued_package'] ?? null;
                    $purchase = PurchaseOrder::where('status', 'paid')
                        ->where('issued_license_id', $record->id)
                        ->where(fn ($query) => $query->whereNull('gateway_mode')->orWhere('gateway_mode', 'production'))
                        ->latest('id')->first();
                    $renewal = PurchaseOrder::where('status', 'paid')->where('purpose', 'renewal')
                        ->where('owner_license_id', $record->id)
                        ->where(fn ($query) => $query->whereNull('gateway_mode')->orWhere('gateway_mode', 'production'))
                        ->latest('id')->first();
                    $add('Paket penerbitan', $purchase?->product_name ?? ($snapshot['name'] ?? (($created?->metadata['issuance_source'] ?? null) === 'manual' ? 'Custom / tanpa paket' : 'Tidak tercatat')));
                    if (($packageId = $purchase?->product_id ?? ($snapshot['id'] ?? null)) && LicenseProduct::whereKey($packageId)->exists()) {
                        $link('Lihat paket di katalog', 'product', (int) $packageId);
                    }
                    $add('Sumber penerbitan', $purchase ? 'Pembelian terverifikasi' : ($created?->user_id ? 'Administrator' : 'Tidak tercatat'));
                    $ledger = FinancialTransaction::where('reference', 'LIC-MAN-'.$record->id)->first();
                    if ($ledger) {
                        $add('Harga penerbitan', 'Rp '.number_format($ledger->amount, 0, ',', '.'));
                        $add('Status pencatatan keuangan', $ledger->status === 'posted' ? 'Tercatat manual' : 'Void');
                        $link('Lihat transaksi penerbitan', 'transaction', $ledger->id);
                    }
                    $add('Durasi paket awal', $purchase ? LicenseDuration::label($purchase->duration_seconds ?? $purchase->days * 86400) : (isset($snapshot['duration_seconds']) ? LicenseDuration::label($snapshot['duration_seconds']) : null));
                    $add('Paket perpanjangan terakhir', $renewal?->product_name);
                    $add('Diterbitkan oleh', $created?->user_id ? User::find($created->user_id)?->name : null);
                    $add('HWID perangkat', $record->hwid);
                    $add('Status perangkat', $record->hwid_hash ? 'Terikat' : 'Belum terikat');
                    $add('Waktu pengikatan', $date($record->bound_at));
                    $add('Sisa masa berlaku', $record->expires_at ? LicenseDuration::label(max(0, $record->expires_at->getTimestamp() - now()->getTimestamp())) : 'Belum dimulai');
                    $add('Dicabut', $date($record->revoked_at));
                    if ($purchase) {
                        $link('Pesanan penerbitan', 'order', $purchase->id);
                    }
                    if ($renewal) {
                        $link('Perpanjangan terakhir', 'order', $renewal->id);
                    }
                    $add('Catatan internal', $record->notes);
                    $add('Dihapus', $record->deleted_at ? $date($record->deleted_at) : null);
                }
                $events = LicenseEvent::where('license_key_id', $record->id);
                break;
            case 'transaction':
                $record = FinancialTransaction::findOrFail($id);
                $title = 'Detail transaksi';
                $subtitle = $record->reference;
                foreach (['Referensi' => $record->reference, 'Deskripsi' => $record->description, 'Jenis' => $record->direction, 'Sumber' => $record->source, 'Status' => $record->status, 'Bruto' => 'Rp '.number_format($record->amount, 0, ',', '.'), 'Biaya' => 'Rp '.number_format($record->fee, 0, ',', '.'), 'Neto' => 'Rp '.number_format($record->amount - $record->fee, 0, ',', '.'), 'Referensi pembayaran' => $record->external_reference, 'Waktu bayar' => $date($record->paid_at), 'Dicatat' => $date($record->created_at), 'Dicatat oleh' => $record->created_by ? User::find($record->created_by)?->name : 'Webhook gateway'] as $label => $value) {
                    $add($label, $value);
                }
                $order = $record->order_id ? PurchaseOrder::find($record->order_id) : null;
                $license = null;
                $packageId = null;
                if ($order) {
                    $link('Lihat detail pesanan', 'order', $order->id);
                    $license = LicenseKey::withTrashed()->find($order->purpose === 'renewal' ? $order->owner_license_id : $order->issued_license_id);
                    $packageId = $order->product_id;
                    $add('Paket saat transaksi', $order->product_name);
                    $add('Durasi paket', LicenseDuration::label($order->duration_seconds ?? $order->days * 86400));
                    $add('Akses game paket', collect($order->game_snapshot ?? [])->pluck('name')->join(', ') ?: Game::whereIn('id', $order->game_ids ?? [])->pluck('name')->join(', '));
                } elseif ($record->source === 'manual' && preg_match('/^LIC-MAN-([0-9]+)$/D', $record->reference, $matches)) {
                    $issuance = LicenseEvent::where('license_key_id', (int) $matches[1])->where('action', 'created')->where('metadata->reference', $record->reference)->first();
                    if ($issuance) {
                        $license = LicenseKey::withTrashed()->find($issuance->license_key_id);
                        $snapshot = $issuance->metadata['issued_package'] ?? [];
                        $packageId = $snapshot['id'] ?? null;
                        $add('Paket saat transaksi', $snapshot['name'] ?? null);
                        $add('Durasi paket', isset($snapshot['duration_seconds']) ? LicenseDuration::label($snapshot['duration_seconds']) : null);
                        $add('Akses game paket', Game::whereIn('id', $snapshot['game_ids'] ?? [])->pluck('name')->join(', '));
                    }
                }
                if ($license) {
                    $add('ID lisensi terkait', '#'.$license->id);
                    $add('Label lisensi', $license->label);
                    $add('Member lisensi', $license->member_name);
                    $add('Key terkait', $license->key_hint);
                    $link('Lihat detail lisensi', 'license', $license->id);
                }
                if ($packageId && LicenseProduct::whereKey($packageId)->exists()) {
                    $link('Lihat paket di katalog', 'product', (int) $packageId);
                }
                $events = LicenseEvent::where('metadata->reference', $record->reference);
                break;
            case 'product':
                $record = LicenseProduct::findOrFail($id);
                $title = 'Detail paket lisensi';
                $subtitle = $record->name;
                foreach (['ID paket' => '#'.$record->id, 'Nama' => $record->name, 'Deskripsi' => $record->description, 'Status' => $record->is_active ? 'Tampil di katalog' : 'Disembunyikan', 'Harga' => 'Rp '.number_format($record->price, 0, ',', '.'), 'Durasi' => LicenseDuration::label($record->duration_seconds ?? $record->days * 86400), 'Akses game' => Game::whereIn('id', $record->game_ids)->pluck('name')->join(', '), 'Dibuat' => $date($record->created_at), 'Diperbarui' => $date($record->updated_at)] as $label => $value) {
                    $add($label, $value);
                }
                break;
            case 'game':
                $record = Game::withCount('licenses')->findOrFail($id); $title = 'Detail game'; $subtitle = $record->name;
                foreach (['Nama game'=>$record->name,'Kode API'=>$record->code,'Versi rilis'=>$record->release_version,'Jumlah lisensi'=>$record->licenses_count,'Injector'=>$record->injector_path ? 'Tersedia' : 'Belum diunggah','DLL'=>$record->dll_path ? 'Tersedia melalui API' : 'Belum diunggah','Dibuat'=>$date($record->created_at),'Diperbarui'=>$date($record->updated_at)] as $label=>$value) { $add($label,$value); }
                break;
            case 'admin':
                $record = User::where('role', 'admin')->findOrFail($id);
                $title = 'Detail administrator';
                $subtitle = $record->name;
                foreach (['Nama' => $record->name, 'Email' => $record->email, 'Role' => 'Administrator', 'Dibuat' => $date($record->created_at)] as $label => $value) {
                    $add($label, $value);
                }
                break;
            default: abort(404);
        }
        if ($events) {
            $timeline = $events->latest('id')->limit(20)->get()->map(fn ($event) => ['title' => str_replace('_', ' ', $event->action), 'time' => $date($event->created_at), 'url' => route($route, ['event', $event->id])])->all();
        }

        return response()->json(compact('title', 'subtitle', 'rows', 'timeline', 'links', 'kind'))->header('Cache-Control','private, no-store');
    }
}
