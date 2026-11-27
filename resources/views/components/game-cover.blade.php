@props(['game','badge'=>''])
<div class="game-cover">
@if($game->artworkUrl('banner'))<img class="game-cover-photo" src="{{ $game->artworkUrl('banner') }}" alt="" loading="lazy">@endif
<div class="game-cover-shade"></div><span class="game-cover-label"><i></i> GAME LIBRARY</span>
@if($badge)<span class="game-cover-badge">{{ $badge }}</span>@endif
@if($game->logo_path || $game->banner_path || $game->code!=='rules-of-survival')<div class="game-cover-brand">@if($game->artworkUrl('logo'))<img class="game-cover-logo" src="{{ $game->artworkUrl('logo') }}" alt="{{ $game->name }}" loading="lazy">@else<x-icon name="gamepad" size="40"/>@endif</div>@endif
<div class="game-cover-caption"><span>{{ $game->code }}</span><span><x-icon name="shield" size="12"/> KEY PROTECTED</span></div>
</div>
