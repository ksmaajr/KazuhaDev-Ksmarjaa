@props(['scope'=>'licenses','games'=>collect(),'actions'=>collect()])
@php
    $statuses = match($scope) {
        'orders','member-orders' => ['pending'=>'Menunggu pembayaran','paid'=>'Lunas','cancelled'=>'Dibatalkan'],
        'products' => ['active'=>'Tampil di katalog','hidden'=>'Disembunyikan'],
        'finance' => ['posted'=>'Tercatat','void'=>'Void'],
        default => ['active'=>'Aktif','unbound'=>'Belum terikat','expired'=>'Expired','revoked'=>'Dicabut'],
    };
@endphp
<form class="list-filters" method="get" action="{{ url()->current() }}" aria-label="Filter {{ $scope }}">
    <div class="filter-primary">
        <div class="filter-search"><label for="{{ $scope }}-search">Pencarian</label><div class="search-field"><x-icon name="search" size="17"/><input id="{{ $scope }}-search" name="q" type="search" maxlength="100" placeholder="{{ $scope==='orders' ? 'Nomor pesanan, member, paket, referensi…' : ($scope==='finance' ? 'Referensi atau deskripsi transaksi…' : 'Cari data…') }}" value="{{ request('q') }}"></div></div>
        @unless(in_array($scope,['admins','activity','games']))
        <div><label for="{{ $scope }}-status">Status</label><select id="{{ $scope }}-status" name="status"><option value="">Semua status</option>@foreach($statuses as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
        @endunless
        @if($games->isNotEmpty())<div><label for="{{ $scope }}-game">Game</label><select id="{{ $scope }}-game" name="game"><option value="">Semua game</option>@foreach($games as $game)<option value="{{ $game->id }}" @selected((string)request('game')===(string)$game->id)>{{ $game->name }}</option>@endforeach</select></div>@endif
        @if($scope==='activity')<div><label for="activity-action">Aktivitas</label><select id="activity-action" name="action"><option value="">Semua aktivitas</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action')===$action)>{{ str_replace('_',' ',$action) }}</option>@endforeach</select></div>@endif
        @if(in_array($scope,['orders','finance']))
        <div><label for="{{ $scope }}-method">Metode bayar</label><select id="{{ $scope }}-method" name="method"><option value="">Semua metode</option>@foreach(['qris'=>'QRIS','bank_transfer'=>'Transfer bank','e_wallet'=>'E-wallet','cash'=>'Tunai','manual'=>'Manual / belum dirinci'] as $value=>$label)<option value="{{ $value }}" @selected(request('method')===$value)>{{ $label }}</option>@endforeach</select></div>
        @endif
        <div class="filter-actions"><button class="btn primary compact" type="submit"><x-icon name="filter" size="15"/> Terapkan</button><a class="btn secondary compact" href="{{ url()->current() }}">Reset</a></div>
    </div>
    <details class="filter-advanced" @if(request()->anyFilled(['from','to','direction','source','purpose','min_amount','max_amount'])) open @endif>
        <summary>Filter lanjutan <x-icon name="chevron" size="13"/></summary>
        <div class="filter-secondary">
            <div><label for="{{ $scope }}-from">Dari tanggal (WIB)</label><input id="{{ $scope }}-from" type="date" name="from" value="{{ request('from', $scope==='finance' ? now('Asia/Jakarta')->startOfMonth()->toDateString() : '') }}"></div>
            <div><label for="{{ $scope }}-to">Sampai tanggal (WIB)</label><input id="{{ $scope }}-to" type="date" name="to" value="{{ request('to', $scope==='finance' ? now('Asia/Jakarta')->toDateString() : '') }}"></div>
            @if($scope==='orders')<div><label for="order-purpose">Jenis pesanan</label><select id="order-purpose" name="purpose"><option value="">Pembelian & perpanjangan</option><option value="purchase" @selected(request('purpose')==='purchase')>Pembelian key</option><option value="renewal" @selected(request('purpose')==='renewal')>Perpanjangan key</option></select></div>@endif
            @if($scope==='finance')
            <div><label for="finance-direction">Jenis transaksi</label><select id="finance-direction" name="direction"><option value="">Semua jenis</option><option value="income" @selected(request('direction')==='income')>Pemasukan</option><option value="expense" @selected(request('direction')==='expense')>Pengeluaran</option></select></div>
            <div><label for="finance-source">Sumber</label><select id="finance-source" name="source"><option value="">Semua sumber</option><option value="manual" @selected(request('source')==='manual')>Manual</option><option value="gateway" @selected(request('source')==='gateway')>Payment gateway</option></select></div>
            <div><label for="finance-min">Nominal minimum</label><input id="finance-min" name="min_amount" type="number" min="0" step="1" value="{{ request('min_amount') }}" placeholder="Rp 0"></div><div><label for="finance-max">Nominal maksimum</label><input id="finance-max" name="max_amount" type="number" min="0" step="1" value="{{ request('max_amount') }}" placeholder="Tanpa batas"></div>
            @endif
        </div>
    </details>
    <div class="filter-bottom"><span><x-icon name="shield" size="13"/> Maksimal 50 data per halaman</span><div><label for="{{ $scope }}-sort">Urutan</label><select id="{{ $scope }}-sort" name="sort">@foreach(['newest'=>'Terbaru','oldest'=>'Terlama','name'=>'Nama A–Z'] + (in_array($scope,['finance','orders','products']) ? ['amount_desc'=>'Nominal tertinggi','amount_asc'=>'Nominal terendah'] : []) as $value=>$label)<option value="{{ $value }}" @selected(request('sort','newest')===$value)>{{ $label }}</option>@endforeach</select><label for="{{ $scope }}-per-page">Tampilkan</label><select id="{{ $scope }}-per-page" name="per_page">@foreach([8,10,25,50] as $limit)<option value="{{ $limit }}" @selected((int)request('per_page',in_array($scope,['licenses','activity'])?8:10)===$limit)>{{ $limit }} baris</option>@endforeach</select></div></div>
</form>
