@props(['game'=>null,'id'])
<div class="release-section-label"><x-icon name="image" size="16"/> Tampilan game</div>
@foreach(['banner'=>'Background / banner game','logo'=>'Logo game'] as $kind=>$label)
<div class="artwork-upload"><label for="{{ $id }}-{{ $kind }}-file">{{ $label }}</label>
@if($game?->artworkUrl($kind))<img class="artwork-preview {{ $kind }}" src="{{ $game->artworkUrl($kind) }}" alt="Preview {{ $label }}">@endif
<input id="{{ $id }}-{{ $kind }}-file" type="file" name="{{ $kind }}_file" accept="image/jpeg,image/png,image/webp" data-image-preview><small class="field-help">JPG, PNG atau WebP. Maksimal {{ $kind==='banner'?'5':'2' }} MB.</small>
@if($game?->{$kind.'_path'})<label class="checkbox-label"><input type="checkbox" name="remove_{{ $kind }}" value="1">Hapus {{ $kind }} kustom</label>@endif</div>@endforeach
<div class="release-section-label"><x-icon name="download" size="16"/> Rilis & distribusi</div>
<label for="{{ $id }}-version">Versi rilis</label><input id="{{ $id }}-version" name="release_version" maxlength="50" value="{{ $game?->release_version }}" placeholder="1.0.0">
@foreach(['injector'=>'Injector (.exe atau .zip)','dll'=>'DLL (.dll)'] as $kind=>$label)
<label for="{{ $id }}-{{ $kind }}">{{ $label }}</label><input id="{{ $id }}-{{ $kind }}" type="file" name="{{ $kind }}_file" accept="{{ $kind==='injector' ? '.exe,.zip' : '.dll' }}"><p class="field-help">Maksimal 20 MB. {{ $game?->{$kind.'_name'} ? 'Unggah untuk mengganti berkas saat ini.' : 'Opsional, dapat diunggah nanti.' }}</p>
@if($game?->{$kind.'_path'})<label class="game-choice"><input type="checkbox" name="remove_{{ $kind }}" value="1"><span>Hapus berkas {{ $kind }} saat ini</span></label><details class="checksum"><summary>SHA-256 berkas saat ini</summary><code>{{ $game->{$kind.'_sha256'} }}</code></details>@endif
@endforeach
<p class="field-help">Member hanya mengunduh injector. DLL dikirim langsung ke injector melalui API yang memeriksa key, HWID dan akses game. Berkas injector dan DLL disimpan terpisah. Unggahan hanya disimpan dan tidak dijalankan di server. Kode game tetap sama setelah diedit.</p>
