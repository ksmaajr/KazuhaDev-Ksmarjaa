@props(['games','products'=>collect()])
<dialog id="create-key" class="modal lk-create" aria-labelledby="lk-create-title" data-no-ui-icon>
<form action="{{ route('licenses.store') }}" method="post" data-lk-create>@csrf
<header class="lk-modal-header"><span class="lk-modal-symbol"><x-icon name="key" size="23"/></span><div><div class="lk-kicker">LICENSE MANAGEMENT / CREATE</div><h2 id="lk-create-title">Buat license key</h2><p>Terbitkan akses member dengan konfigurasi yang tepat.</p></div><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></header>
<div class="lk-modal-content">
<section class="lk-form-section"><div class="lk-section-title"><span>01</span><div><h3>Identitas member</h3><p>Informasi pemilik dan label internal.</p></div></div>
<div class="lk-fields"><div><label for="member-name">Nama member</label><input id="member-name" name="member_name" value="{{ old('member_name') }}" placeholder="Nama pemilik lisensi" maxlength="100" required></div><div><label for="key-label">Nama key / label</label><input id="key-label" name="label" value="{{ old('label') }}" placeholder="Contoh: Andi · Premium" maxlength="100" required></div></div></section>
<section class="lk-form-section"><div class="lk-section-title"><span>02</span><div><h3>Konfigurasi akses</h3><p>Pilih paket aktif atau atur secara manual.</p></div></div>
<label for="create-product">Paket lisensi <span class="muted">(opsional)</span></label>
<select id="create-product" name="product_id" data-license-product>
<option value="">Custom / tanpa paket</option>
@foreach($products as $product)
<option value="{{ $product->id }}" data-duration="{{ $product->duration_seconds ?? $product->days * 86400 }}" data-games="{{ implode(',', $product->game_ids ?? []) }}" @selected((string)old('product_id') === (string)$product->id)>{{ $product->name }} · {{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400) }}</option>
@endforeach
</select>
<p class="field-help" data-lk-package-hint>Pilih paket untuk mengikuti durasi dan game katalog. Tidak membuat invoice.</p>
<div class="lk-package-fields" data-lk-package-fields>
<x-duration-picker id="create-duration" :restore="true"/>
<x-game-picker :games="$games" :selected="$games->where('code','rules-of-survival')->pluck('id')->all()" id="create-games"/>
</div></section>
<section class="lk-form-section"><div class="lk-section-title"><span>03</span><div><h3>Catatan & ringkasan</h3><p>Periksa konfigurasi sebelum menerbitkan key.</p></div></div>
<label for="key-notes">Catatan <span class="muted">(opsional)</span></label><textarea id="key-notes" name="notes" rows="2" maxlength="1000" placeholder="Catatan internal untuk key ini">{{ old('notes') }}</textarea>
<div class="lk-review" aria-live="polite"><div><small>Durasi</small><strong data-lk-summary-duration>—</strong></div><div><small>Game</small><strong data-lk-summary-games>—</strong></div><div><small>Aktivasi</small><strong>Perangkat pertama</strong></div></div>
<div class="hint-box"><x-icon name="clock" size="18"/><span>Durasi mulai saat aktivasi pertama dan HWID tersimpan. 1 hari = 24 jam. Key yang belum diaktifkan belum memiliki waktu expired.</span></div></section>
</div>
<footer class="modal-actions lk-modal-footer"><span><x-icon name="shield" size="15"/> Penerbitan manual · tanpa pembayaran</span><div><button type="button" class="btn secondary" data-close-dialog>Batal</button><button class="btn primary" type="submit"><x-icon name="plus" size="17"/> Generate key</button></div></footer>
</form></dialog>
