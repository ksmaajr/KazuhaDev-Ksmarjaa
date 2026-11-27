<x-layout title="Paket Lisensi">
<div class="package-studio" data-no-ui-icon>
<header class="pk-hero"><div><div class="pk-kicker"><x-icon name="package" size="15"/> CATALOG / LICENSE PACKAGES</div><h1>Paket lisensi<span>.</span></h1><p>Kelola harga, durasi dan akses game dalam katalog member.</p><button type="button" class="btn primary" data-open-dialog="create-product"><x-icon name="plus" size="17"/> Tambah paket</button></div><div class="pk-overview"><div><span class="pk-symbol"><x-icon name="package"/></span><small>Hasil katalog</small><strong>{{ $products->total() }}</strong></div><div><span class="pk-symbol"><x-icon name="eye"/></span><small>Aktif di halaman ini</small><strong>{{ $products->getCollection()->where('is_active',true)->count() }}</strong></div><div><span class="pk-symbol"><x-icon name="gamepad"/></span><small>Game tersedia</small><strong>{{ $games->count() }}</strong></div></div></header>
<div class="pk-filter"><div class="pk-section-heading"><span class="pk-symbol"><x-icon name="filter" size="17"/></span><div><h2>Temukan paket</h2><p>Filter katalog sesuai kebutuhan.</p></div></div><x-list-filters scope="products" :games="$games"/></div>
<section class="catalog-admin" aria-label="Daftar paket lisensi">
    <div class="catalog-admin-heading"><div><span class="pk-kicker">02 / MEMBER CATALOG</span><h2>Direktori paket</h2><p>Harga dan akses disalin saat pembelian; perubahan paket tidak mengubah pesanan lama.</p></div><span class="subtle-chip">{{ $products->total() }} paket</span></div>
    <div class="product-admin-grid catalog-package-grid">
    @forelse($products as $product)
        @php
            $packageGames = $games->whereIn('id', $product->game_ids ?? []);
            $packageArtwork = $packageGames->filter(fn($game) => $game->artworkUrl('banner'))->take(3)->values();
        @endphp
        <article class="panel product-admin-card catalog-package {{ $product->is_active ? '' : 'catalog-package-hidden' }}">
            <div class="catalog-package-art" aria-hidden="true">
                @if($packageArtwork->isNotEmpty())<img class="catalog-art-center" src="{{ $packageArtwork->first()->artworkUrl('banner') }}" alt="" loading="lazy">@else<span class="pk-art-fallback"><x-icon name="package" size="54"/></span>@endif
            </div>
            <div class="catalog-package-body">
                <div class="catalog-package-topline"><span class="catalog-visibility {{ $product->is_active ? 'is-visible' : '' }}"><i aria-hidden="true"></i>{{ $product->is_active?'Tampil di katalog':'Disembunyikan' }}</span><span class="catalog-package-id">PAKET #{{ $product->id }}</span></div>
                <h3><button class="record-link" type="button" data-record-url="{{ route('records.show',['product',$product->id]) }}">{{ $product->name }} <x-icon name="arrow" size="16"/></button></h3>
                <p class="catalog-description">{{ $product->description ?: 'Paket akses game dengan masa berlaku sejak aktivasi pertama.' }}</p>
                <div class="catalog-package-facts"><div><span><x-icon name="wallet" size="14"/> HARGA PAKET</span><strong class="catalog-price"><small>Rp</small> {{ number_format($product->price,0,',','.') }}</strong></div><div><span><x-icon name="clock" size="14"/> DURASI AKSES</span><strong class="catalog-duration"> {{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400) }}</strong><small>Sejak aktivasi pertama</small></div></div>
                <div class="catalog-package-games"><span class="catalog-fact-label"><x-icon name="gamepad" size="14"/> AKSES GAME <small>({{ $packageGames->count() }})</small></span><div class="catalog-game-list">@forelse($packageGames as $game)<span class="catalog-game"><x-icon name="gamepad" size="13"/> {{ $game->name }}</span>@empty<span class="order-secondary">Game tidak tersedia</span>@endforelse</div></div>
            </div>
            <footer class="catalog-package-footer"><button type="button" class="btn secondary compact" data-record-url="{{ route('records.show',['product',$product->id]) }}"><x-icon name="eye" size="14"/> Detail</button><div class="catalog-package-actions"><button type="button" class="btn primary compact" data-open-dialog="edit-product-{{ $product->id }}"><x-icon name="edit" size="14"/> Edit</button><form method="post" action="{{ route('products.toggle',$product) }}">@csrf<button class="btn secondary compact"><x-icon name="eye" size="14"/> {{ $product->is_active?'Sembunyikan':'Tampilkan' }}</button></form></div></footer>
        </article>
    @empty
        <div class="panel catalog-admin-empty"><x-icon name="shopping" size="30"/><h3>Belum ada paket lisensi</h3><p>Tambahkan paket untuk mengatur harga, durasi, dan akses game.</p><button type="button" class="btn primary compact" data-open-dialog="create-product"><x-icon name="plus" size="14"/> Tambah paket</button></div>
    @endforelse
    </div>
    <x-pagination :paginator="$products"/>
</section>
</div>
@foreach(collect([null])->concat($products->getCollection()) as $editing)
@php $suffix = $editing ? '-'.$editing->id : ''; @endphp
<dialog id="{{ $editing ? 'edit-product-'.$editing->id : 'create-product' }}" class="modal lk-create pk-editor" aria-labelledby="pk-title{{ $suffix }}" data-no-ui-icon>
<form method="post" action="{{ $editing ? route('products.update',$editing) : route('products.store') }}" data-pk-editor>
@csrf
@if($editing) @method('PUT') @endif
<header class="lk-modal-header"><span class="lk-modal-symbol"><x-icon name="{{ $editing ? 'edit' : 'package' }}" size="23"/></span><div><div class="lk-kicker">CATALOG / {{ $editing ? 'EDIT PACKAGE' : 'NEW PACKAGE' }}</div><h2 id="pk-title{{ $suffix }}">{{ $editing ? 'Edit paket lisensi' : 'Tambah paket lisensi' }}</h2><p>Konfigurasi produk yang tersedia untuk member.</p></div><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></header>
<div class="lk-modal-content">
<section class="lk-form-section"><div class="lk-section-title"><span><x-icon name="package" size="16"/></span><div><h3>Identitas paket</h3><p>Nama dan deskripsi yang dilihat member.</p></div></div>
<label for="pk-name{{ $suffix }}">Nama paket</label><input id="pk-name{{ $suffix }}" name="name" value="{{ $editing?->name }}" maxlength="100" placeholder="Contoh: Premium 7 Hari" required>
<label for="pk-description{{ $suffix }}">Deskripsi <span class="muted">(opsional)</span></label><textarea id="pk-description{{ $suffix }}" name="description" maxlength="500" rows="2" placeholder="Jelaskan manfaat paket">{{ $editing?->description }}</textarea></section>
<section class="lk-form-section"><div class="lk-section-title"><span><x-icon name="wallet" size="16"/></span><div><h3>Harga & durasi</h3><p>Harga katalog dan masa berlaku sejak aktivasi pertama.</p></div></div>
<label for="pk-price{{ $suffix }}">Harga (Rupiah)</label><input id="pk-price{{ $suffix }}" name="price" type="number" min="1" max="100000000000" step="1" value="{{ $editing?->price }}" placeholder="50000" required>
@if($editing)<x-duration-picker :id="'product-duration-'.$editing->id" :seconds="$editing->duration_seconds ?? $editing->days * 86400" label="Durasi sejak aktivasi"/>@else<x-duration-picker id="product-duration" label="Durasi sejak aktivasi"/>@endif
</section>
<section class="lk-form-section"><div class="lk-section-title"><span><x-icon name="gamepad" size="16"/></span><div><h3>Akses game</h3><p>Pilih game yang termasuk dalam paket.</p></div></div>
@if($editing)<x-game-picker :games="$games" :selected="$editing->game_ids" :id="'edit-product-games-'.$editing->id"/>@else<x-game-picker :games="$games" id="product-games"/>@endif
</section>
<section class="pk-editor-review" aria-live="polite"><div class="pk-review-heading"><span class="pk-symbol"><x-icon name="receipt" size="18"/></span><div><small>PREVIEW KATALOG</small><strong data-pk-name>{{ $editing?->name ?? 'Paket baru' }}</strong></div></div><div class="lk-review"><div><small>Harga</small><strong data-pk-price>—</strong></div><div><small>Durasi</small><strong data-pk-duration>—</strong></div><div><small>Akses</small><strong data-pk-games>—</strong></div></div><p class="field-help">Perubahan paket tidak mengubah pesanan atau lisensi yang telah diterbitkan.</p></section>
</div><footer class="modal-actions lk-modal-footer"><span><x-icon name="shield" size="15"/> Konfigurasi katalog</span><div><button type="button" class="btn secondary" data-close-dialog>Batal</button><button type="submit" class="btn primary"><x-icon name="{{ $editing ? 'check' : 'plus' }}" size="17"/> {{ $editing ? 'Simpan paket' : 'Terbitkan paket' }}</button></div></footer>
</form></dialog>
@endforeach
</x-layout>
