@props(['title'=>'KazuhaDev.','license'=>null])
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title }} · KazuhaDev.</title><link rel="icon" href="{{ asset('kz-brand.webp') }}" type="image/webp"><x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])<style>
.storefront-body .store-header-v2{position:relative;display:flex;align-items:center;justify-content:space-between;gap:20px;max-width:1110px;margin:16px auto 0;padding:14px 20px;min-height:76px;height:auto;border:1px solid var(--line);border-radius:18px;background:linear-gradient(110deg,#b580c60e,transparent 52%),var(--panel);box-shadow:0 8px 26px #00000012}
.storefront-body .store-header-v2 .brand{display:flex;align-items:center;gap:11px;flex-shrink:0}
.storefront-body .store-header-v2 .brand-mark{width:44px;height:44px;object-fit:cover;border-radius:12px}
.store-brand-copy{display:grid;gap:5px}
.store-brand-copy strong{font-size:21px;line-height:1;font-weight:750;letter-spacing:-.055em}
.store-brand-copy small{font-size:9px;line-height:1.2;letter-spacing:1.8px;color:var(--muted);font-weight:650}
.store-header-nav{display:flex;align-items:center;justify-content:center;gap:5px}
.store-header-nav a{display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:9px;color:var(--muted);font-size:12px;font-weight:600;white-space:nowrap;transition:background .15s,color .15s}
.store-header-nav a:hover,.store-header-nav a[aria-current=page]{background:var(--soft);color:var(--mint)}
.storefront-body .store-header-v2 .member-header-actions{display:flex;align-items:center;gap:8px;margin:0}
.storefront-body .store-header-v2 .member-header-actions .btn{min-height:40px;border-radius:10px}
.storefront-body .store-header-v2 .store-member-cta{padding-inline:14px}
@media(max-width:1150px){.storefront-body .store-header-v2{margin-inline:16px}}
@media(max-width:760px){.storefront-body .store-header-v2{flex-wrap:wrap;gap:12px;padding:12px 14px;margin:10px 12px 0}.store-header-nav{order:3;width:100%;justify-content:flex-start;border-top:1px solid var(--line);padding-top:8px}.store-header-nav a{flex:1;justify-content:center}.storefront-body .store-header-v2 .brand-mark{width:36px;height:36px}.store-brand-copy strong{font-size:18px}}
@media(max-width:440px){.storefront-body .store-header-v2 .member-header-actions{width:100%;justify-content:space-between}.storefront-body .store-header-v2 .store-member-cta{flex:1}.store-header-nav a{padding:8px;font-size:11px}}

/* Light theme: neutral canvas with charcoal accents and original pink branding. */
:root[data-theme="light"] .storefront-body{--bg:#f3f3f5;--panel:#fff;--panel-hover:#f7f5f8;--line:#d6d1da;--muted:#59545f;--text:#201d24;--soft:#eeebf1;--field:#fff;--mint:#a12770;--mint-bg:#f7e3ef;background:var(--bg);color:var(--text)}
:root[data-theme="light"] .storefront-body .store-header-v2{background:#201c27;border-color:#39313f;color:#faf7fc;box-shadow:0 5px 18px #201c2720;--text:#faf7fc;--muted:#c6bccc;--line:#49404f;--soft:#352c3d;--mint:#efb3df}
:root[data-theme="light"] .storefront-body .store-header-v2 .brand{color:#faf7fc}
:root[data-theme="light"] .storefront-body .store-header-v2 .btn.secondary{background:#332b3c;color:#f2eaf5;border-color:#514358}
:root[data-theme="light"] .storefront-body .storefront-intro{background:linear-gradient(115deg,#fff 35%,#f1e4f4);border-color:var(--line)}
:root[data-theme="light"] .storefront-body .storefront-assurance{background:#fff;border-color:var(--line)}
:root[data-theme="light"] .storefront-body .store-pending-invoices{background:#fff;border-color:#c8b8d0;border-left:4px solid #27212e}
:root[data-theme="light"] .storefront-body .store-pending-row{background:#f3eff5;border-color:var(--line)}
:root[data-theme="light"] .storefront-body .storefront-filters,:root[data-theme="light"] .storefront-body .store-products .game-card,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-summary,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-payment{background:#fff;border-color:var(--line);box-shadow:0 4px 14px #201c2709}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card{background:linear-gradient(125deg,#26202e,#42334d);border-color:#493b55;color:#fff;--text:#fff;--muted:#ddd1e3;--mint:#efb3df;--invoice-violet:#e0c1fa;--invoice-pink:#efb3df;--invoice-success:#a0e9ca}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card>strong{color:#fff}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card>span,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card>div{color:#ddd1e3}
:root[data-theme="light"] .storefront-body input:not([type=checkbox]):not([type=radio]),:root[data-theme="light"] .storefront-body select{background:#fff;color:#201d24;border-color:#c9c3cf}
:root[data-theme="light"] .storefront-body .btn.primary{background:linear-gradient(110deg,#a62e76,#7145b4);color:#fff}
</style></head><body class="member-body storefront-body"><header class="member-header store-header-v2"><a class="brand" href="{{ route('home') }}"><img class="brand-mark admin-brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="44" height="44" alt=""><span class="store-brand-copy"><strong>KazuhaDev<span class="brand-dot">.</span></strong><small>LICENSE STORE</small></span></a><nav class="store-header-nav" aria-label="Navigasi toko"><a href="{{ route('shop') }}" @if(request()->routeIs('home','shop')) aria-current="page" @endif><x-icon name="package" size="16"/> Katalog lisensi</a></nav><div class="member-header-actions"><a class="btn primary compact store-member-cta" href="{{ $license ? route('member.dashboard') : route('member.login') }}"><x-icon name="user" size="16"/> {{ $license?'Dashboard member':'Masuk member' }} <x-icon name="arrow" size="14"/></a><x-theme-toggle/></div></header><main class="storefront-main"><x-flash-messages/><div data-live-content>{{ $slot }}</div><footer class="footer"><span>© {{ now()->year }} KazuhaDev.</span><span>Lisensi perangkat · Masa berlaku sejak aktivasi</span></footer></main><x-feedback/></body></html>
