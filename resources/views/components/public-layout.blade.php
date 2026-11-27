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

/* Public light theme: distinct canvas, surfaces, controls and readable contrast. */
:root[data-theme="light"] .storefront-body{--bg:#f2edf6;--panel:#ffffff;--panel-hover:#f9f4fb;--line:#d9cce1;--muted:#63556d;--text:#2c2035;--mint:#a12770;--mint-bg:#f8dfef;--soft:#f3edf8;--field:#ffffff;--accent-purple:#7041ac;background:#f2edf6;color:var(--text)}
:root[data-theme="light"] .storefront-body .store-header-v2{background:#fff;border-color:#d9cce1;box-shadow:0 5px 18px #39234d12}
:root[data-theme="light"] .storefront-body .storefront-intro{background:linear-gradient(120deg,#fff 22%,#f3e1f5 100%);border-color:#d9c4e3;box-shadow:0 8px 24px #38234c0b}
:root[data-theme="light"] .storefront-body .storefront-intro:after{border-color:#d5b6e1;box-shadow:0 0 0 34px #d8b9e329,0 0 0 68px #d8b9e318}
:root[data-theme="light"] .storefront-body .storefront-assurance{background:#fff;border-color:#d8c5e1;box-shadow:0 3px 12px #38234c0e}
:root[data-theme="light"] .storefront-body .store-redesign>.hint-box{background:#eae1f2;border-color:#d7c6e2;color:#594366}
:root[data-theme="light"] .storefront-body .store-pending-invoices{background:#fff9ed;border-color:#d9b56e;box-shadow:0 5px 16px #674c1c0c}
:root[data-theme="light"] .storefront-body .store-pending-heading{color:#8a5b15}
:root[data-theme="light"] .storefront-body .store-pending-row{background:#fff;border-color:#e5d1ad}
:root[data-theme="light"] .storefront-body .store-pending-row:hover{border-color:#a87325;background:#fffaf1}
:root[data-theme="light"] .storefront-body .checkout-steps>span{background:#fff;border-color:#d9cce1}
:root[data-theme="light"] .storefront-body .checkout-steps>.current{background:#f8dfef;border-color:#b85a90}
:root[data-theme="light"] .storefront-body .storefront-filters,:root[data-theme="light"] .storefront-body .store-products .game-card,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-summary,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-payment{background:#fff;border-color:#d9cce1;box-shadow:0 5px 20px #38234c0c}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero{background:linear-gradient(115deg,#fff,#eee1f5);border-color:#d9c4e3}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card{background:linear-gradient(125deg,#f6e3f1,#e9e1f7);border-color:#cdb6dc}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-method-choice,:root[data-theme="light"] .storefront-body .checkout-studio .studio-terms{background:#f6f1f9;border-color:#d9cce1}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-method-choice:has(input:checked){background:#f8dfef;border-color:#a12770}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-assurance{background:#edf6fa;border-color:#c2dbe7}
:root[data-theme="light"] .storefront-body .checkout-dialog{background:#fff;border-color:#d9cce1}
:root[data-theme="light"] .storefront-body input:not([type=checkbox]):not([type=radio]),:root[data-theme="light"] .storefront-body select{background:#fff;border-color:#cdbed6;color:#2c2035}
:root[data-theme="light"] .storefront-body input::placeholder{color:#766780}
:root[data-theme="light"] .storefront-body .btn.primary{background:linear-gradient(110deg,#a62e76,#7145b4);color:#fff;border-color:transparent;box-shadow:0 3px 8px #71348024}
:root[data-theme="light"] .storefront-body .btn.secondary{background:#f3edf8;color:#493452;border-color:#d3c3dd}
:root[data-theme="light"] .storefront-body .eyebrow{color:#962968}
</style></head><body class="member-body storefront-body"><header class="member-header store-header-v2"><a class="brand" href="{{ route('home') }}"><img class="brand-mark admin-brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="44" height="44" alt=""><span class="store-brand-copy"><strong>KazuhaDev<span class="brand-dot">.</span></strong><small>LICENSE STORE</small></span></a><nav class="store-header-nav" aria-label="Navigasi toko"><a href="{{ route('shop') }}" @if(request()->routeIs('home','shop')) aria-current="page" @endif><x-icon name="package" size="16"/> Katalog lisensi</a></nav><div class="member-header-actions"><a class="btn primary compact store-member-cta" href="{{ $license ? route('member.dashboard') : route('member.login') }}"><x-icon name="user" size="16"/> {{ $license?'Dashboard member':'Masuk member' }} <x-icon name="arrow" size="14"/></a><x-theme-toggle/></div></header><main class="storefront-main"><x-flash-messages/><div data-live-content>{{ $slot }}</div><footer class="footer"><span>© {{ now()->year }} KazuhaDev.</span><span>Lisensi perangkat · Masa berlaku sejak aktivasi</span></footer></main><x-feedback/></body></html>
