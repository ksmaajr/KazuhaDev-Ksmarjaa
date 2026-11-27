<x-layout title="Paket Lisensi">
<div class="page-heading"><div><span class="eyebrow">CATALOG / LICENSE PACKAGES</span><h1>Paket untuk setiap kebutuhan.</h1><p>Atur harga, durasi, dan akses game yang tersedia.</p></div><button class="btn primary" data-open-dialog="create-product"><x-icon name="plus" size="17"/> Tambah paket</button></div>
<x-list-filters scope="products" :games="$games"/>
<section class="catalog-admin" aria-label="Daftar paket lisensi">
    <div class="catalog-admin-heading"><div><span class="eyebrow">MEMBER CATALOG</span><h2>Paket lisensi</h2><p>Harga dan akses disalin saat pembelian; perubahan paket tidak mengubah pesanan lama.</p></div><span class="subtle-chip">{{ $products->total() }} paket</span></div>
    <div class="product-admin-grid catalog-package-grid">
    @forelse($products as $product)
        @php
            $packageGames = $games->whereIn('id', $product->game_ids ?? []);
            $packageArtwork = $packageGames->filter(fn($game) => $game->artworkUrl('banner'))->take(3)->values();
        @endphp
        <article class="panel product-admin-card catalog-package {{ $product->is_active ? '' : 'catalog-package-hidden' }}">
            <div class="catalog-package-art" aria-hidden="true">
                @foreach(['left','center','right'] as $position)
                    @if($packageArtwork->isNotEmpty())<img class="catalog-art-{{ $position }}" src="{{ $packageArtwork[$loop->index % $packageArtwork->count()]->artworkUrl('banner') }}" alt="" loading="lazy">@endif
                @endforeach
            </div>
            <div class="catalog-package-body">
                <div class="catalog-package-topline"><span class="catalog-visibility {{ $product->is_active ? 'is-visible' : '' }}"><i aria-hidden="true"></i>{{ $product->is_active?'Tampil di katalog':'Disembunyikan' }}</span><span class="catalog-package-id">PAKET #{{ $product->id }}</span></div>
                <h3><button class="record-link" type="button" data-record-url="{{ route('records.show',['product',$product->id]) }}">{{ $product->name }} <x-icon name="arrow" size="16"/></button></h3>
                <p class="catalog-description">{{ $product->description ?: 'Paket akses game dengan masa berlaku sejak aktivasi pertama.' }}</p>
                <div class="catalog-package-facts"><div><span>HARGA PAKET</span><strong class="catalog-price"><small>Rp</small> {{ number_format($product->price,0,',','.') }}</strong></div><div><span>DURASI AKSES</span><strong class="catalog-duration"><x-icon name="clock" size="16"/> {{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400) }}</strong><small>Sejak aktivasi pertama</small></div></div>
                <div class="catalog-package-games"><span class="catalog-fact-label">AKSES GAME <small>({{ $packageGames->count() }})</small></span><div class="catalog-game-list">@forelse($packageGames as $game)<span class="catalog-game"><x-icon name="gamepad" size="13"/> {{ $game->name }}</span>@empty<span class="order-secondary">Game tidak tersedia</span>@endforelse</div></div>
            </div>
            <footer class="catalog-package-footer"><button type="button" class="btn secondary compact" data-record-url="{{ route('records.show',['product',$product->id]) }}">Detail paket <x-icon name="arrow" size="13"/></button><div class="catalog-package-actions"><button type="button" class="btn primary compact" data-open-dialog="edit-product-{{ $product->id }}"><x-icon name="edit" size="14"/> Edit</button><form method="post" action="{{ route('products.toggle',$product) }}">@csrf<button class="btn secondary compact">{{ $product->is_active?'Sembunyikan':'Tampilkan' }}</button></form></div></footer>
        </article>
    @empty
        <div class="panel catalog-admin-empty"><x-icon name="shopping" size="30"/><h3>Belum ada paket lisensi</h3><p>Tambahkan paket untuk mengatur harga, durasi, dan akses game.</p><button type="button" class="btn primary compact" data-open-dialog="create-product"><x-icon name="plus" size="14"/> Tambah paket</button></div>
    @endforelse
    </div>
    <x-pagination :paginator="$products"/>
</section>
<dialog id="create-product" class="modal"><form method="post" action="{{ route('products.store') }}">@csrf<div class="modal-heading"><h2>Tambah paket lisensi</h2><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></div><label for="product-name">Nama paket</label><input id="product-name" name="name" maxlength="100" required><label for="product-description">Deskripsi</label><textarea id="product-description" name="description" maxlength="500"></textarea><x-duration-picker id="product-duration" label="Durasi sejak aktivasi"/><label for="product-price">Harga (Rupiah)</label><input id="product-price" name="price" type="number" min="1" max="100000000000" step="1" required><x-game-picker :games="$games" id="product-games"/><div class="modal-actions"><button type="button" class="btn secondary" data-close-dialog>Batal</button><button class="btn primary">Terbitkan paket</button></div></form></dialog>
@foreach($products as $product)<dialog id="edit-product-{{ $product->id }}" class="modal"><form method="post" action="{{ route('products.update',$product) }}">@csrf @method('PUT')<div class="modal-heading"><h2>Edit paket</h2><button class="icon-btn" type="button" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></div><label for="edit-product-name-{{ $product->id }}">Nama paket</label><input id="edit-product-name-{{ $product->id }}" name="name" value="{{ $product->name }}" maxlength="100" required><label for="edit-product-description-{{ $product->id }}">Deskripsi</label><textarea id="edit-product-description-{{ $product->id }}" name="description" maxlength="500">{{ $product->description }}</textarea><x-duration-picker :id="'product-duration-'.$product->id" :seconds="$product->duration_seconds ?? $product->days * 86400" label="Durasi sejak aktivasi"/><label for="edit-product-price-{{ $product->id }}">Harga (Rupiah)</label><input id="edit-product-price-{{ $product->id }}" name="price" type="number" min="1" max="100000000000" value="{{ $product->price }}" required><x-game-picker :games="$games" :selected="$product->game_ids" :id="'edit-product-games-'.$product->id"/><div class="modal-actions"><button class="btn secondary" type="button" data-close-dialog>Batal</button><button class="btn primary">Simpan paket</button></div></form></dialog>@endforeach
</x-layout>
