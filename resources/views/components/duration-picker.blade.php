@props(['id'=>'duration','seconds'=>604800,'label'=>'Durasi lisensi','restore'=>false])
@php
    $hours = $restore ? old('hours', old('days') !== null ? (int) old('days') * 24 : intdiv($seconds,3600)) : intdiv($seconds,3600);
    $minutes = $restore ? old('minutes', intdiv($seconds % 3600,60)) : intdiv($seconds % 3600,60);
    $total = (int) $hours * 60 + (int) $minutes;
@endphp
<fieldset class="duration-picker" data-duration-picker>
    <legend>{{ $label }}</legend>
    <div class="duration-presets" role="group" aria-label="Pilihan durasi cepat">
        @foreach([1,3,7,14,30] as $day)
            <button type="button" data-duration-hours="{{ $day * 24 }}" class="preset {{ $total === $day * 1440 ? 'chosen' : '' }}" aria-pressed="{{ $total === $day * 1440 ? 'true' : 'false' }}">{{ $day }} hari</button>
        @endforeach
    </div>
    <div class="duration-fields">
        <div class="duration-unit"><label for="{{ $id }}-hours">Jam</label><input id="{{ $id }}-hours" name="hours" type="number" min="0" max="720" step="1" inputmode="numeric" value="{{ $hours }}" aria-describedby="{{ $id }}-help" required></div>
        <span class="duration-divider" aria-hidden="true">:</span>
        <div class="duration-unit"><label for="{{ $id }}-minutes">Menit</label><input id="{{ $id }}-minutes" name="minutes" type="number" min="0" max="59" step="1" inputmode="numeric" value="{{ $minutes }}" aria-describedby="{{ $id }}-help" required></div>
    </div>
    <p class="field-help" id="{{ $id }}-help">Atur durasi tepatnya · minimal 1 menit, maksimal 720 jam.</p>
</fieldset>
