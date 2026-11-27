<dialog id="create-key" class="modal"><form action="{{ route('licenses.store') }}" method="post">@csrf
<div class="modal-heading"><div><span class="eyebrow">LICENSE MANAGEMENT</span><h2>Buat license key</h2></div><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></div>
<p class="muted">Key unik, satu perangkat, kendali penuh.</p>
<label for="member-name">Nama member</label><input id="member-name" name="member_name" value="{{ old('member_name') }}" placeholder="Nama pemilik lisensi" maxlength="100" required><label for="key-label">Nama key / label</label><input id="key-label" name="label" value="{{ old('label') }}" placeholder="Contoh: Andi · Premium" maxlength="100" required>
<x-duration-picker id="create-duration" :restore="true"/>
<div class="hint-box"><x-icon name="clock" size="18"/><span>Durasi mulai saat aktivasi pertama melalui injector dan HWID tersimpan. 1 hari = 24 jam. Key yang belum diaktifkan belum memiliki waktu expired.</span></div>
<x-game-picker :games="$games" :selected="$games->where('code','rules-of-survival')->pluck('id')->all()" id="create-games"/>
<label for="key-notes">Catatan <span class="muted">(opsional)</span></label><textarea id="key-notes" name="notes" rows="2" maxlength="1000" placeholder="Catatan internal untuk key ini">{{ old('notes') }}</textarea>
<div class="modal-actions"><button type="button" class="btn secondary" data-close-dialog>Batal</button><button class="btn primary" type="submit"><x-icon name="plus" size="17"/> Generate key</button></div>
</form></dialog>
