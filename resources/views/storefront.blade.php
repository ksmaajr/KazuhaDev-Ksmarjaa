<x-dynamic-component :component="$renewal?'member-layout':'public-layout'" :title="$renewal?'Perpanjang Lisensi':'Paket Lisensi'" :license="$license">
<div class="store-redesign {{ $renewal ? 'renewal-studio' : '' }}" @if($renewal) data-no-ui-icon @endif><section class="storefront-intro"><div><span class="eyebrow">{{ $renewal?'RENEW YOUR ACCESS':'KAZUHADEV / LICENSE STORE' }}</span><h1>{{ $renewal?'Waktu baru. Key yang sama.':'Temukan lisensi Anda.' }}</h1><p>{{ $renewal?'Pilih durasi untuk key Anda. Perpanjangan menjaga HWID, akses game, dan aktivasi pertama.':'Pilih paket yang sesuai dengan kebutuhan Anda. Satu alur pembelian, dari pemilihan lisensi hingga aktivasi.' }}</p></div><div class="storefront-assurance"><x-icon name="shield" size="25"/><span>{{ $renewal?$license->key_hint:'Akses satu perangkat' }}<small>{{ $renewal?$license->member_name:'Durasi dimulai sejak aktivasi pertama' }}</small></span></div></section>
@if($renewal)<div class="renewal-benefits"><div><span class="renewal-benefit-icon"><x-icon name="key" size="21"/></span><div><strong>Key tetap sama</strong><small>Tidak perlu mengganti license key</small></div></div><div><span class="renewal-benefit-icon"><x-icon name="device" size="21"/></span><div><strong>Perangkat tetap terikat</strong><small>HWID dan akses game dipertahankan</small></div></div><div><span class="renewal-benefit-icon"><x-icon name="clock" size="21"/></span><div><strong>Tambahan masa berlaku</strong><small>Setelah pembayaran terverifikasi</small></div></div></div>@endif
@if($gatewayMode==='sandbox')<div class="hint-box"><x-icon name="code" size="17"/><span>{{ $renewal ? 'Lingkungan demo: simulasi pembayaran tidak memperpanjang lisensi produksi atau mencatat pendapatan riil.' : 'Lingkungan demo: pembelian sandbox menerbitkan key demo untuk portal member, tanpa akses produksi atau pendapatan riil.' }}</span></div>@endif
@if(($pendingInvoices ?? collect())->isNotEmpty())
<section class="store-pending-invoices {{ $pendingInvoices->contains(fn($invoice) => !in_array($invoice->gateway_status, ['failed','expired'])) ? 'store-pending-live' : '' }}" aria-label="Invoice belum selesai"><div class="store-pending-heading"><span class="store-pending-symbol"><x-icon name="receipt" size="22"/></span><div><strong>Anda memiliki invoice yang belum selesai</strong><p>Lanjutkan invoice yang tersedia tanpa membuat pembayaran baru.</p></div></div>
@foreach($pendingInvoices as $pending)<a class="store-pending-row" href="{{ route('checkout.receipt', ['order' => $pending->number]) }}"><div><strong>{{ $pending->product_name }}</strong><small>{{ $pending->number }} · {{ $pending->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small></div><div class="store-pending-price"><strong>Rp {{ number_format($pending->amount,0,',','.') }}</strong><small class="store-wait-status {{ in_array($pending->gateway_status,['failed','expired'])?'needs-review':'is-waiting' }}"><i aria-hidden="true"></i>{{ in_array($pending->gateway_status,['creating','uncertain'])?'Menunggu konfirmasi':(in_array($pending->gateway_status,['failed','expired'])?'Perlu pemeriksaan':'Belum selesai') }}</small></div><x-icon name="arrow" size="16"/></a>@endforeach
</section>@endif
<div class="checkout-steps" aria-label="Tahapan pembelian"><span class="current"><b>01</b> {{ $renewal?'Pilih perpanjangan':'Pilih paket' }}</span><i></i><span><b>02</b> Metode pembayaran</span><i></i><span><b>03</b> {{ $renewal?'Key diperpanjang':'Key siap digunakan' }}</span></div>
<div class="store-catalog-heading"><div><span class="eyebrow">01 / KATALOG</span><h2>{{ $renewal ? "Paket perpanjangan" : "Jelajahi paket lisensi" }}</h2></div><span>{{ $products->total() }} paket tersedia</span></div>
@if(!$renewal)<form class="list-filters storefront-filters" method="get"><div class="filter-primary"><div class="filter-search"><label for="store-q">Cari paket</label><div class="search-field"><x-icon name="search" size="17"/><input id="store-q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama paket atau deskripsi" maxlength="100"></div></div><div><label for="store-game">Game</label><select id="store-game" name="game"><option value="">Semua game</option>@foreach($games as $game)<option value="{{ $game->id }}" @selected((string)request('game')===(string)$game->id)>{{ $game->name }}</option>@endforeach</select></div><div><label for="store-sort">Urutan harga</label><select id="store-sort" name="sort"><option value="newest" @selected(request('sort','newest')==='newest')>Paket terbaru</option><option value="amount_asc" @selected(request('sort')==='amount_asc')>Harga terendah</option><option value="amount_desc" @selected(request('sort')==='amount_desc')>Harga tertinggi</option></select></div><button class="btn primary compact">Cari paket</button><a class="btn secondary compact" href="{{ route('shop') }}">Reset</a></div></form>@endif
<div class="game-grid store-products">@forelse($products as $product)@php($coverGame=$games->get($product->game_ids[0]??0))@php($pending=($pendingInvoices ?? collect())->firstWhere('license_product_id', $product->id))<article class="panel game-card store-premium-card">
@if($coverGame)<x-game-cover :game="$coverGame" :badge="\App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400)"/>@else<div class="store-cover-fallback"><x-icon name="key" size="40"/></div>@endif
<div class="game-card-body">
<div class="store-product-heading"><span class="store-product-type"><x-icon name="package" size="13"/> {{ $renewal?'RENEWAL PACKAGE':'DIGITAL LICENSE' }}</span><span class="store-device-tag"><x-icon name="shield" size="13"/> 1 perangkat</span></div>
<h2>{{ $product->name }}</h2><p class="game-card-description">{{ $product->description ?: 'Lisensi perangkat dengan akses terverifikasi.' }}</p>
<div class="store-price-panel"><div><small>Harga lisensi</small><strong class="product-price"><span>Rp</span> {{ number_format($product->price,0,',','.') }}</strong></div><div class="store-duration-tile"><x-icon name="clock" size="17"/><span><small>Durasi akses</small><strong>{{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400) }}</strong></span></div></div>
<div class="store-access-block"><span>{{ $renewal?'AKSES GAME TETAP':'GAME DALAM PAKET' }}</span><div class="product-access">@foreach(($renewal ? $license->games->modelKeys() : $product->game_ids) as $gameId)<span><x-icon name="check" size="13"/> {{ $games->get($gameId)?->name }}</span>@endforeach</div></div>
<div class="store-card-footer">@if($pending)<div class="store-pending-indicator {{ in_array($pending->gateway_status,['failed','expired'])?'needs-review':'is-waiting' }}"><span class="store-wait-clock"><x-icon name="clock" size="14"/></span><span>{{ in_array($pending->gateway_status,['failed','expired'])?'Invoice perlu pemeriksaan':(in_array($pending->gateway_status,['creating','uncertain'])?'Menunggu konfirmasi':'Invoice belum selesai') }}</span><small>{{ $pending->number }}</small></div><a class="btn primary full" href="{{ route('checkout.receipt', ['order' => $pending->number]) }}"><span><x-icon name="receipt" size="16"/> Lanjutkan invoice</span><x-icon name="arrow" size="16"/></a>@else<button type="button" class="btn primary full" data-open-dialog="checkout-product-{{ $product->id }}"><span><x-icon name="key" size="16"/> {{ $renewal?'Pilih perpanjangan':'Pilih paket' }}</span><x-icon name="arrow" size="16"/></button>@endif<div class="store-card-note"><x-icon name="shield" size="12"/> Pembayaran terverifikasi <i></i> {{ $renewal?'Key tetap sama':'Aktivasi melalui key' }}</div></div>
</div></article>
@if(!$pending)<dialog id="checkout-product-{{ $product->id }}" class="modal checkout-dialog"><form method="post" action="{{ $renewal?route('member.renew.store'):route('checkout.store') }}" data-once>@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="idempotency_key" value="{{ Illuminate\Support\Str::uuid() }}"><div class="modal-heading"><div><span class="eyebrow">{{ $renewal?'RENEWAL / CONFIRM':'CHECKOUT / YOUR DETAILS' }}</span><h2>{{ $renewal?'Perpanjang akses Anda':'Satu langkah menuju akses.' }}</h2></div><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></div><div class="checkout-summary"><span>{{ $product->name }}<small>{{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days*86400) }}</small></span><strong>Rp {{ number_format($product->price,0,',','.') }}</strong></div>
@if($renewal)<div class="hint-box"><x-icon name="key" size="17"/><span>Key tujuan <strong>{{ $license->key_hint }}</strong><br>HWID tetap. Durasi aktif ditambah dari expired; jika kedaluwarsa dimulai setelah pembayaran terverifikasi.</span></div>@else<label for="buyer-name-{{ $product->id }}">Nama pemilik lisensi</label><input id="buyer-name-{{ $product->id }}" name="member_name" value="{{ old('member_name') }}" maxlength="100" autocomplete="name" required><label for="buyer-contact-{{ $product->id }}">WhatsApp / kontak pembeli</label><input id="buyer-contact-{{ $product->id }}" name="customer_contact" value="{{ old('customer_contact') }}" maxlength="100" autocomplete="tel" required placeholder="Nomor yang dapat dihubungi"><label for="buyer-email-{{ $product->id }}">Email (opsional)</label><input id="buyer-email-{{ $product->id }}" name="customer_email" value="{{ old('customer_email') }}" type="email" maxlength="255" autocomplete="email"><p class="field-help">Simpan halaman pesanan. Akses key pembelian dilindungi sesi browser ini.</p>@endif
<div class="modal-actions"><button type="button" class="btn secondary" data-close-dialog>Batal</button><button class="btn primary" type="submit">Lanjut ke pembayaran <x-icon name="arrow" size="16"/></button></div></form></dialog>@endif
@empty @if($renewal)<div class="renewal-empty"><span class="renewal-empty-icon"><x-icon name="key" size="34"/></span><div><span class="renewal-small-label">KATALOG / BELUM TERSEDIA</span><h3>Paket perpanjangan belum tersedia.</h3><p>Belum ada paket aktif. Hubungi admin untuk perpanjangan key Anda.</p></div><a class="btn secondary" href="{{ route('member.dashboard') }}">Kembali ke dashboard <x-icon name="arrow" size="15"/></a></div>@else<div class="empty-state storefront-empty"><x-icon name="key" size="36"/><strong>{{ $renewal?'Paket perpanjangan belum tersedia.':'Paket segera tersedia.' }}</strong><p>{{ $renewal?'Belum ada paket aktif. Hubungi admin untuk perpanjangan key Anda.':'Admin belum menerbitkan paket lisensi.' }}</p><a class="btn secondary" href="{{ route('member.login') }}">Portal member <x-icon name="arrow" size="15"/></a></div>@endif @endforelse</div><x-pagination :paginator="$products"/></div>

<style>
.store-redesign{--store-radius:20px;display:grid;gap:20px;min-width:0}
.store-redesign .storefront-intro{position:relative;isolation:isolate;overflow:hidden;margin:0;padding:clamp(24px,4vw,52px);min-height:220px;border:1px solid var(--line);border-radius:26px;background:radial-gradient(ellipse at 95% 0%,#bd79cb30,transparent 62%),linear-gradient(125deg,var(--panel),var(--soft));align-items:center}
.store-redesign .storefront-intro:after{content:"";position:absolute;z-index:-1;right:-90px;top:-170px;width:440px;height:440px;border:1px solid #c38bc43b;border-radius:50%;box-shadow:0 0 0 34px #c38bc40b,0 0 0 68px #c38bc408;pointer-events:none}
.store-redesign .storefront-intro h1{font-size:clamp(32px,4.5vw,60px);letter-spacing:-.055em;font-weight:750;line-height:1.08;margin:12px 0 16px}
.store-redesign .storefront-intro p{font-size:15px;max-width:520px}
.store-redesign .storefront-assurance{border-radius:16px;padding:20px;background:var(--panel);max-width:100%}
.store-redesign>.hint-box{margin:0;border-radius:12px}
.store-redesign .checkout-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin:0;padding:0;border:0;gap:10px}
.store-redesign .checkout-steps>i{display:none}
.store-redesign .checkout-steps>span{padding:15px;min-width:0;border:1px solid var(--line);border-radius:14px;background:var(--panel);font-weight:600}
.store-redesign .checkout-steps>.current{border-color:var(--mint);background:var(--mint-bg)}
.store-catalog-heading{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-top:12px}
.store-catalog-heading h2{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.04em}
.store-catalog-heading>span{color:var(--muted);font-size:12px}
.store-redesign .storefront-filters{margin:0;padding:16px;border-radius:16px}
.store-redesign .storefront-filters input,.store-redesign .storefront-filters select{min-height:44px;border-radius:10px}
.store-redesign .storefront-filters .btn{min-height:44px;border-radius:10px}
.store-redesign .store-products{grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));max-width:none;width:100%;justify-content:start;gap:18px;margin:0}
.store-redesign .store-products .game-card{border-radius:var(--store-radius);overflow:hidden;margin:0;background:var(--panel);border:1px solid var(--line)}
.store-redesign .store-products .game-card:hover{transform:translateY(-3px);border-color:var(--mint)}
.store-redesign .game-cover{height:clamp(150px,17vw,210px)}
.store-redesign .game-card-body{padding:22px;gap:10px}
.store-redesign .game-card-body h2{font-size:21px;font-weight:700;line-height:1.3;margin:0}
.store-redesign .product-price{font-size:32px;letter-spacing:-.05em;font-variant-numeric:tabular-nums;line-height:1.2}
.store-redesign .game-card-body .btn{margin-top:auto;min-height:48px;border-radius:12px;justify-content:space-between;padding-inline:17px}
.store-redesign .game-card-description{line-height:1.7;overflow-wrap:anywhere}
.store-redesign .checkout-dialog{border-radius:22px}
.store-redesign .checkout-dialog input{min-height:46px;border-radius:10px}
.store-redesign .checkout-dialog .checkout-summary{padding:20px;border-radius:14px}
.store-redesign .checkout-dialog .modal-actions .btn{min-height:44px;border-radius:10px}
@media(max-width:850px){.store-redesign .storefront-intro{align-items:stretch;min-height:0}.store-redesign .storefront-assurance{width:auto}.store-redesign .checkout-steps>span{padding:10px;flex-direction:column;align-items:flex-start;gap:8px}}
@media(max-width:480px){.store-redesign{gap:14px}.store-redesign .checkout-steps{gap:6px}.store-redesign .checkout-steps>span{font-size:11px;padding:9px}.store-redesign .store-catalog-heading{align-items:start}.store-redesign .game-card-body{padding:18px}}
@media(prefers-reduced-motion:reduce){.store-redesign .store-products .game-card{animation:none;transition:none}.store-redesign .store-products .game-card:hover{transform:none}}

.store-redesign .store-pending-invoices{padding:18px;border:1px solid var(--mint);border-radius:16px;background:var(--panel);display:grid;gap:10px}
.store-pending-heading{display:flex;align-items:center;gap:12px;color:var(--mint)}
.store-pending-heading strong{font-size:15px;color:var(--text)}
.store-pending-heading p{font-size:12px;color:var(--muted);margin-top:3px}
.store-pending-row{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--line);background:var(--soft);border-radius:10px;min-width:0}
.store-pending-row:hover{border-color:var(--mint)}
.store-pending-row>div:first-child{flex:1;min-width:0}
.store-pending-row strong,.store-pending-row small{display:block;overflow-wrap:anywhere}
.store-pending-row strong{font-size:13px}
.store-pending-row small{color:var(--muted);font-size:11px;margin-top:4px}
.store-pending-price{text-align:right}
.store-pending-indicator{display:flex;align-items:center;gap:7px;color:var(--mint);font-size:12px}

.store-redesign .store-products{grid-template-columns:repeat(auto-fill,minmax(min(100%,310px),1fr));align-items:stretch;gap:20px}
.store-redesign .store-products:has(>.store-premium-card:only-of-type){grid-template-columns:minmax(0,440px);justify-content:start}
.store-redesign .store-products .store-premium-card{display:flex;flex-direction:column;min-width:0;width:100%;max-width:none;border:1px solid color-mix(in srgb,var(--accent-purple) 30%,var(--line));border-radius:20px;background:var(--panel);box-shadow:0 12px 32px #00000016;transition:transform .2s,border-color .2s,box-shadow .2s}
.store-redesign .store-products .store-premium-card:hover{border-color:var(--accent-purple);box-shadow:0 14px 35px #9462b327;transform:translateY(-3px)}
.store-redesign .store-premium-card .game-cover{height:185px;flex:none}
.store-cover-fallback{height:185px;display:grid;place-items:center;background:linear-gradient(135deg,#32223e,#5b386b);color:#e9b6ed}
.store-redesign .store-premium-card .game-card-body{display:flex;flex-direction:column;flex:1;gap:12px;padding:19px;min-height:0}
.store-product-heading{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
.store-product-type,.store-device-tag{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:650;line-height:1.4}
.store-product-type{color:var(--mint);letter-spacing:.8px}
.store-device-tag{border:1px solid var(--line);border-radius:6px;padding:4px 7px;color:var(--muted)}
.store-redesign .store-premium-card .game-card-body h2{font-size:24px;line-height:1.25;margin:0;letter-spacing:-.035em}
.store-redesign .store-premium-card .game-card-description{font-size:13px;margin:0;min-height:0;line-height:1.55}
.store-price-panel{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:center;gap:12px;padding:14px;border:1px solid color-mix(in srgb,var(--accent-purple) 35%,var(--line));border-radius:13px;background:linear-gradient(120deg,#a76cc418,transparent),var(--soft)}
.store-price-panel small{display:block;font-size:10px;color:var(--muted);margin-bottom:4px}
.store-redesign .store-price-panel .product-price{display:block;font-size:28px;font-weight:750;line-height:1.2;margin:0;white-space:normal;overflow-wrap:anywhere}
.store-price-panel .product-price span{font-size:15px;color:var(--mint)}
.store-duration-tile{display:flex;align-items:center;gap:8px;border-left:1px solid var(--line);padding-left:12px;color:var(--mint);min-width:0}
.store-duration-tile strong{font-size:12px;display:block;color:var(--text);line-height:1.4}
.store-access-block{display:grid;gap:7px}
.store-access-block>span{font-size:10px;letter-spacing:1px;color:var(--muted);font-weight:650}
.store-redesign .store-premium-card .product-access{display:flex;flex-wrap:wrap;gap:6px;margin:0;padding:0}
.store-redesign .store-premium-card .product-access>span{display:inline-flex;align-items:center;gap:5px;background:var(--soft);border:1px solid var(--line);border-radius:7px;padding:5px 8px;font-size:11px;color:var(--text)}
.store-card-footer{display:grid;gap:9px;margin-top:auto;padding-top:12px;border-top:1px solid var(--line)}
.store-redesign .store-card-footer .btn{margin:0;min-height:46px;font-weight:700}
.store-card-footer .btn>span{display:flex;align-items:center;gap:8px}
.store-card-footer .store-pending-indicator{padding:8px 9px;background:var(--mint-bg);border-radius:7px;font-size:11px;flex-wrap:wrap}
.store-pending-indicator small{font-size:10px;margin-left:auto;overflow-wrap:anywhere}
.store-card-note{display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;font-size:10px;color:var(--muted)}
.store-card-note i{width:3px;height:3px;background:var(--muted);border-radius:50%}
:root[data-theme="light"] .store-redesign .store-price-panel{background:linear-gradient(120deg,#292330,#403148);--text:#fff;--muted:#d7cadd;--mint:#efb3df;--line:#584961;color:#fff}
@media(max-width:400px){.store-redesign .store-premium-card .game-card-body{padding:15px}.store-price-panel{padding:11px;gap:8px}.store-redesign .store-price-panel .product-price{font-size:23px}.store-duration-tile{padding-left:8px}}
@media(prefers-reduced-motion:reduce){.store-redesign .store-products .store-premium-card:hover{transform:none}}

/* Gentle waiting motion: never animate failed/expired invoices as active processing. */
.store-redesign .store-pending-row .store-wait-status{display:flex;align-items:center;justify-content:flex-end;gap:7px}
.store-wait-status i{width:7px;height:7px;border-radius:50%;background:var(--mint);position:relative;flex:none}
.store-wait-status.is-waiting i:after{content:"";position:absolute;inset:-3px;border:1px solid var(--mint);border-radius:50%;animation:kz-wait-ring 2.4s ease-out infinite}
.store-wait-clock{display:grid;place-items:center;position:relative;flex:none;width:20px;height:20px}
.store-pending-indicator.is-waiting .store-wait-clock:after{content:"";position:absolute;inset:0;border:1px solid currentColor;border-radius:50%;animation:kz-wait-ring 2.4s ease-out infinite}
.store-pending-indicator.is-waiting .store-wait-clock svg{animation:kz-wait-breathe 2.4s ease-in-out infinite}
.store-redesign .store-wait-status.needs-review i{background:var(--muted)}
@keyframes kz-wait-ring{0%{transform:scale(.85);opacity:.65}80%,100%{transform:scale(1.7);opacity:0}}
@keyframes kz-wait-breathe{0%,100%{opacity:.75}50%{opacity:1}}
@media(prefers-reduced-motion:reduce){.store-wait-status i:after,.store-wait-clock:after,.store-wait-clock svg{animation:none!important}}

/* Premium pending invoice: traveling highlight, soft halo, and animated status. */
.store-redesign .store-pending-invoices{position:relative;isolation:isolate;overflow:hidden;border-color:color-mix(in srgb,var(--mint) 55%,var(--line));background:linear-gradient(115deg,color-mix(in srgb,var(--mint) 7%,var(--panel)),var(--panel) 55%);box-shadow:0 8px 24px #00000012}
.store-redesign .store-pending-live:before{content:"";position:absolute;inset:0;pointer-events:none;z-index:-1;background:linear-gradient(110deg,transparent 25%,color-mix(in srgb,var(--mint) 13%,transparent) 48%,transparent 70%);transform:translateX(-110%);animation:kz-invoice-sweep 5.5s ease-in-out 3}
.store-redesign .store-pending-live{animation:kz-invoice-halo 3s ease-in-out 5}
.store-pending-symbol{width:44px;height:44px;display:grid;place-items:center;flex:none;border-radius:12px;border:1px solid color-mix(in srgb,var(--mint) 40%,var(--line));background:var(--mint-bg);color:var(--mint);position:relative}
.store-pending-live .store-pending-symbol:after{content:"";position:absolute;inset:-3px;border:1px solid var(--mint);border-radius:15px;pointer-events:none;animation:kz-invoice-symbol 2.8s ease-out 5}
.store-redesign .store-pending-row{position:relative;overflow:hidden;transition:transform .2s,border-color .2s,background .2s}
.store-redesign .store-pending-row:hover{transform:translateY(-2px);border-color:var(--mint);background:color-mix(in srgb,var(--mint) 6%,var(--soft))}
.store-redesign .store-pending-row:hover>svg:last-child{transform:translateX(3px)}
.store-pending-row>svg:last-child{transition:transform .2s;color:var(--mint)}
.store-redesign .store-pending-row .store-wait-status{font-weight:600;color:var(--mint)}
.store-redesign .store-pending-row .store-wait-status.needs-review{color:var(--muted)}
@keyframes kz-invoice-sweep{0%{transform:translateX(-110%)}45%,100%{transform:translateX(110%)}}
@keyframes kz-invoice-halo{0%,100%{box-shadow:0 8px 24px #00000012}50%{box-shadow:0 0 22px color-mix(in srgb,var(--mint) 16%,transparent),0 8px 24px #00000012}}
@keyframes kz-invoice-symbol{0%{transform:scale(.94);opacity:.45}75%,100%{transform:scale(1.25);opacity:0}}
@media(prefers-reduced-motion:reduce){.store-redesign .store-pending-live,.store-redesign .store-pending-live:before,.store-pending-live .store-pending-symbol:after{animation:none!important}.store-redesign .store-pending-row,.store-pending-row>svg:last-child{transition:none}.store-redesign .store-pending-row:hover,.store-redesign .store-pending-row:hover>svg:last-child{transform:none}}

/* Renewal workspace: a consistent charcoal surface in both themes. */
.member-with-sidebar .renewal-studio{gap:18px}
.member-with-sidebar .renewal-studio{--panel:#292233;--soft:#382e43;--line:#594963;--text:#f9f3fc;--muted:#d4c5de;--mint:#efb3df;--mint-bg:#503449;--field:#211b2b;--accent-purple:#cdb3f3}
.member-with-sidebar .renewal-studio .storefront-intro{background:radial-gradient(ellipse at 100% 0%,#624770,transparent 65%),linear-gradient(120deg,#241d2e,#30243d);color:var(--text);border-color:var(--line);min-height:180px;padding:30px;box-shadow:0 10px 25px #241b3020}
.member-with-sidebar .renewal-studio .storefront-intro h1{font-size:clamp(30px,4vw,48px);color:#fff;letter-spacing:-.045em;max-width:100%;overflow-wrap:anywhere}
.member-with-sidebar .renewal-studio .storefront-intro p{color:var(--muted);margin-bottom:0}
.member-with-sidebar .renewal-studio .storefront-assurance{background:#382e43;color:#efb3df;border:1px solid #695575;min-width:0}
.member-with-sidebar .renewal-studio .storefront-assurance small{color:var(--muted)}
.renewal-benefits{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.renewal-benefits>div{display:flex;align-items:center;gap:11px;padding:14px;background:var(--panel);border:1px solid var(--line);border-radius:13px;color:var(--text);min-width:0}
.renewal-benefit-icon{width:39px;height:39px;display:grid;place-items:center;flex:none;border:1px solid var(--line);border-radius:10px;background:var(--soft);color:var(--mint)}
.renewal-benefits strong,.renewal-benefits small{display:block;line-height:1.5}
.renewal-benefits strong{font-size:13px;color:var(--text)}.renewal-benefits small{font-size:11px;color:var(--muted);margin-top:3px}
.member-with-sidebar .renewal-studio>.hint-box{background:var(--panel);border:1px solid var(--line);color:var(--muted)}
.member-with-sidebar .renewal-studio .checkout-steps>span{background:var(--panel);color:var(--muted);border-color:var(--line)}
.member-with-sidebar .renewal-studio .checkout-steps>.current{background:#503449;color:#ffd3ef;border-color:#d29ac4}
.member-with-sidebar .renewal-studio .checkout-steps>span b{background:var(--soft);color:var(--muted);border-color:var(--line)}
.member-with-sidebar .renewal-studio .checkout-steps>.current b{background:#68415e;color:#ffd3ef;border-color:#d29ac4}
.member-with-sidebar .renewal-studio .store-catalog-heading{padding:14px 18px;background:var(--panel);border:1px solid var(--line);border-radius:14px;color:var(--text);margin:0}
.member-with-sidebar .renewal-studio .store-catalog-heading h2{color:var(--text);margin:5px 0 0;font-size:24px}
.member-with-sidebar .renewal-studio .store-catalog-heading>span{padding:6px 9px;border-radius:7px;border:1px solid var(--line);background:var(--soft)}
.member-with-sidebar .renewal-studio .store-products:has(>.renewal-empty){grid-template-columns:minmax(0,1fr);width:100%;max-width:none;padding:0;margin:0}
.renewal-empty{grid-column:1/-1;display:flex;align-items:center;gap:18px;padding:24px;background:var(--panel);border:1px solid var(--line);border-radius:18px;color:var(--text);min-width:0}
.renewal-empty-icon{width:65px;height:65px;border-radius:16px;display:grid;place-items:center;flex:none;background:var(--soft);border:1px solid var(--line);color:var(--mint)}
.renewal-empty>div{flex:1;min-width:0}
.renewal-small-label{font-size:10px;letter-spacing:1px;color:var(--mint);font-weight:700}
.renewal-empty h3{font-size:19px;color:var(--text);margin:6px 0;line-height:1.35}.renewal-empty p{font-size:13px;line-height:1.6;color:var(--muted);margin:0}
.member-with-sidebar .renewal-studio .btn.secondary{background:var(--soft);border-color:var(--line);color:var(--text)}
.member-with-sidebar .renewal-studio .btn.primary{background:linear-gradient(110deg,#efb3df,#c5a8ef);border-color:transparent;color:#30203a}
.member-with-sidebar .renewal-studio .store-premium-card,.member-with-sidebar .renewal-studio .checkout-dialog{background:var(--panel);color:var(--text);border-color:var(--line)}
.member-with-sidebar .renewal-studio .store-premium-card h2,.member-with-sidebar .renewal-studio .checkout-dialog h2,.member-with-sidebar .renewal-studio .store-price-panel .product-price{color:var(--text)}
.member-with-sidebar .renewal-studio .game-card-description,.member-with-sidebar .renewal-studio .store-card-note{color:var(--muted)}
.member-with-sidebar .renewal-studio .store-pending-heading strong,.member-with-sidebar .renewal-studio .store-pending-row strong{color:var(--text)}
.member-with-sidebar .renewal-studio .checkout-summary,.member-with-sidebar .renewal-studio .checkout-dialog .hint-box{background:var(--soft);border-color:var(--line);color:var(--text)}
.member-with-sidebar .renewal-studio .checkout-summary small{color:var(--muted)}
.renewal-studio>.storefront-intro,.renewal-benefits,.renewal-empty{animation:renewal-enter .5s ease both}
@keyframes renewal-enter{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:850px){.renewal-benefits{grid-template-columns:1fr}.member-with-sidebar .renewal-studio .storefront-intro{padding:22px}.renewal-empty{flex-wrap:wrap}.renewal-empty>div{flex:1 1 180px}}
@media(max-width:480px){.renewal-empty{padding:16px}.renewal-empty .btn{width:100%;justify-content:center}.renewal-empty-icon{width:48px;height:48px}}
@media(prefers-reduced-motion:reduce){.renewal-studio>.storefront-intro,.renewal-benefits,.renewal-empty{animation:none!important}}
</style>
</x-dynamic-component>
