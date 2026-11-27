@props(['games','selected'=>[],'id'=>'games'])
<fieldset class="game-picker"><legend>Akses game <span class="muted">· pilih satu atau lebih</span></legend>
<div class="game-pick-grid">@forelse($games as $game)<label class="game-choice game-pick-card" for="{{ $id }}-{{ $game->id }}">
@if($game->artworkUrl('banner'))<img class="game-pick-photo" src="{{ $game->artworkUrl('banner') }}" alt="" loading="lazy">@endif
<span class="game-pick-shade"></span><input class="game-pick-input" type="checkbox" id="{{ $id }}-{{ $game->id }}" name="game_ids[]" value="{{ $game->id }}" @checked(in_array($game->id,old('game_ids',$selected)))><span class="game-pick-text"><strong>{{ $game->name }}</strong><small>{{ $game->code }}</small></span><span class="game-pick-mark" aria-hidden="true"><x-icon name="check" size="14"/></span>
</label>@empty<p class="field-help">Tambahkan game di Konfigurasi Game terlebih dahulu.</p>@endforelse</div></fieldset>
