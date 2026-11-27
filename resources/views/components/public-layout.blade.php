@props(['title'=>'KazuhaDev.','license'=>null])
@php($memberSessionLicense = \App\Models\LicenseKey::find(session('member_license_id')))
@php($memberSessionLicense = $memberSessionLicense && !$memberSessionLicense->revoked_at ? $memberSessionLicense : null)
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

/* Public typography: clean geometric headings and legible interface text. */
.storefront-body{--font-sans:'Manrope','Plus Jakarta Sans','Segoe UI Variable','Segoe UI',system-ui,sans-serif;font-family:var(--font-sans);font-size:14px;line-height:1.65;font-optical-sizing:auto;text-rendering:optimizeLegibility}
.storefront-body h1,.storefront-body h2,.storefront-body h3,.storefront-body .brand{font-family:var(--font-sans)}
.storefront-body button,.storefront-body input,.storefront-body select,.storefront-body textarea{font-family:inherit}
.storefront-body .store-brand-copy strong{font-weight:750;letter-spacing:-.045em}
.storefront-body .store-redesign .storefront-intro h1{font-weight:750;letter-spacing:-.045em}
.storefront-body .store-redesign .store-premium-card h2{font-weight:700;letter-spacing:-.025em}
.storefront-body .store-redesign .store-price-panel .product-price,.storefront-body .checkout-studio .invoice-total-card>strong{font-weight:750;font-variant-numeric:tabular-nums lining-nums;letter-spacing:-.04em}
.storefront-body .store-redesign .game-card-description,.storefront-body .storefront-intro p{font-size:14px;line-height:1.65}
.storefront-body .store-product-type,.storefront-body .store-access-block>span,.storefront-body .eyebrow{letter-spacing:.09em;font-weight:600}
.storefront-body .store-card-note{font-size:11px;line-height:1.5}
.storefront-body .btn{font-weight:650;letter-spacing:.005em}
.storefront-body .invoice-facts dd{font-variant-numeric:tabular-nums}
.storefront-body .receipt-reference strong,.storefront-body .paid-key code{font-family:'Cascadia Mono','Consolas',ui-monospace,monospace}

/* Premium public chrome and high-contrast light checkout. */
.storefront-body .store-header-v2{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);gap:18px;padding:13px 20px;border-radius:16px;isolation:isolate}
.storefront-body .store-header-v2 .member-header-actions{justify-self:end}
.storefront-body .store-header-v2 .store-header-nav a{padding:11px 15px;border:1px solid var(--line);border-radius:10px}
.storefront-body .store-header-v2 .brand-mark{box-shadow:0 3px 12px #ad66cf35}
.storefront-body .store-header-v2 .store-member-cta{font-weight:700;min-height:42px}
:root[data-theme="light"] .storefront-body .store-header-v2{background:linear-gradient(115deg,#201b29,#33263d);border-color:#54415f;box-shadow:0 9px 25px #281d3530}
:root[data-theme="light"] .storefront-body .store-header-v2 .store-header-nav a{background:#403349;color:#f4d6ef;border-color:#685373}
:root[data-theme="light"] .storefront-body .store-header-v2 .store-member-cta{background:linear-gradient(110deg,#b43f87,#8256bc);color:white}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero{--panel:#292233;--soft:#382e43;--line:#594963;--text:#fff;--muted:#ddd0e5;--mint:#f2b3df;--mint-bg:#503248;--invoice-pink:#f2b3df;--invoice-violet:#dac0fa;--invoice-cyan:#9ee3f2;--invoice-success:#a5e4c8;background:radial-gradient(ellipse at 100% 0%,#694775 0%,transparent 65%),linear-gradient(125deg,#231e2b,#30263b);color:var(--text);border:1px solid #594963;box-shadow:0 12px 30px #30213d22}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero .payment-result h1{color:#fff}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero .payment-result p{color:#e1d6e7}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero .payment-result .eyebrow{color:#f2b3df;justify-content:flex-start;text-align:left}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero .invoice-brand-chip{color:#dec4fa}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-hero .invoice-status-pill{color:#ffd3ee;background:#56364f;border-color:#a86d96}
:root[data-theme="light"] .storefront-body .checkout-studio[data-invoice-state="success"] .invoice-hero .invoice-status-pill{color:#b8f1d8;background:#28463e;border-color:#528774}
:root[data-theme="light"] .storefront-body .checkout-studio[data-invoice-state="cancelled"] .invoice-hero .invoice-status-pill{color:#ffd3dc;background:#50303a;border-color:#a16b7b}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li{background:#382e43;border-color:#66556f;color:#e3d9e9}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li strong{color:inherit}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li small{color:#d4c7dc}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li.is-complete{background:#253e3a;border-color:#527f71;color:#b6efd6}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li.is-current{background:#523249;border-color:#e1a2cd;color:#ffd3ef}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li.is-complete small,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-progress>li.is-current small{color:inherit}
:root[data-theme="light"] .storefront-body .store-redesign .store-pending-live{background:linear-gradient(115deg,#fff,#f4e5f3);border-color:#b579ad;border-left-color:#9d3479}
:root[data-theme="light"] .storefront-body .store-redesign .store-pending-live:before{background:linear-gradient(110deg,transparent 22%,#b64e9b25 48%,transparent 72%)}
@media(max-width:760px){.storefront-body .store-header-v2{display:flex;flex-wrap:wrap;padding:12px 14px;gap:12px}}

/* Finish the hybrid light theme with clearly separated checkout surfaces. */
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-summary,:root[data-theme="light"] .storefront-body .checkout-studio .invoice-payment{border-color:#c4b7cd;box-shadow:0 8px 24px #30213d12;background:#fff}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-section-title{padding:0 0 14px;border-bottom:2px solid #d9c8e2;color:#292131}
:root[data-theme="light"] .storefront-body .checkout-studio .receipt-reference{background:linear-gradient(115deg,#2b2335,#40314c);border-color:#554260;color:#fff;--muted:#e0d2e8;--mint:#ffc0e7}
:root[data-theme="light"] .storefront-body .checkout-studio .receipt-reference>span,:root[data-theme="light"] .storefront-body .checkout-studio .receipt-reference .icon-btn{color:#e0d2e8}
:root[data-theme="light"] .storefront-body .checkout-studio .receipt-reference>strong{color:#ffc0e7}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-order-caption{display:block;background:#eee6f2;color:#554261;padding:9px 11px;border:1px solid #dbcee4;border-radius:8px;letter-spacing:.08em}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-order-group .invoice-facts>div{border-color:#ddd3e2}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-facts dt{color:#60536a}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-facts dd{color:#2d2434}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-facts .studio-order-status{color:#97256b}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-facts .studio-order-status.is-paid{color:#216d52}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-order-total{background:#f0e7f4;padding-inline:10px!important;border-radius:8px}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-assurance{background:#e8f3f7;border-color:#b6d4df;color:#225b70;--muted:#395f6d}
:root[data-theme="light"] .storefront-body .checkout-studio .invoice-total-card>strong small{color:#efb3df}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-method-choice{background:#f5f1f7;border-color:#cbbfd3}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-method-choice:has(input:checked){background:#f7e0ef;border-color:#a12770;box-shadow:0 0 0 1px #a12770}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-method-choice:has(input:checked) .studio-method-check,:root[data-theme="light"] .storefront-body .checkout-studio .studio-terms-consent input:checked+.studio-consent-mark{color:#fff}
:root[data-theme="light"] .storefront-body .checkout-studio .studio-terms{background:#f1edf4;border-color:#cbbfd3}
:root[data-theme="light"] .storefront-body .checkout-studio .receipt-live{background:#eae3f0;border-color:#cbbbd5;color:#493753}
:root[data-theme="light"] .storefront-body .checkout-studio .receipt-safety{color:#51475a}
:root[data-theme="light"] .storefront-body .checkout-studio .paid-key{background:#e8f5ef;border-color:#add5c2}
.storefront-body .store-footer-v3{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin:28px 0 0;padding:18px 20px;border:1px solid #50405c;border-radius:15px;background:linear-gradient(115deg,#201b29,#35283e);color:#f5edf8;text-align:left}
.store-footer-brand,.store-footer-info{display:flex;align-items:center;gap:10px}
.store-footer-brand img{border-radius:9px;object-fit:cover}
.store-footer-v3 strong,.store-footer-v3 small{display:block;line-height:1.5}
.store-footer-v3 strong{font-size:13px;color:#fff}
.store-footer-v3 small{font-size:11px;color:#d5c7df}
.store-footer-info>svg{color:#efb3df}
.store-footer-v3 nav{display:flex;gap:12px;flex-wrap:wrap}
.store-footer-v3 nav a{display:inline-flex;align-items:center;gap:5px;color:#e8d8ef;font-size:12px;text-decoration:none}
.store-footer-v3 nav a:hover{color:#ffc0e7}
@media(max-width:650px){.storefront-body .store-footer-v3{align-items:flex-start;gap:14px;padding:15px}.store-footer-v3 nav{width:100%;border-top:1px solid #50405c;padding-top:12px}}
</style></head><body class="member-body storefront-body"><header class="member-header store-header-v2"><a class="brand" href="{{ route('home') }}"><img class="brand-mark admin-brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="44" height="44" alt=""><span class="store-brand-copy"><strong>KazuhaDev<span class="brand-dot">.</span></strong><small>LICENSE STORE</small></span></a><nav class="store-header-nav" aria-label="Navigasi toko"><a href="{{ route('shop') }}" @if(request()->routeIs('home','shop')) aria-current="page" @endif><x-icon name="package" size="16"/> Katalog lisensi</a></nav><div class="member-header-actions"><a class="btn primary compact store-member-cta" href="{{ $memberSessionLicense ? route('member.dashboard') : route('member.login') }}"><x-icon name="user" size="16"/> {{ $memberSessionLicense?'Dashboard member':'Masuk member' }} <x-icon name="arrow" size="14"/></a><x-theme-toggle/></div></header><main class="storefront-main"><x-flash-messages/><div data-live-content>{{ $slot }}</div><footer class="footer store-footer-v3"><div class="store-footer-brand"><img src="{{ asset('kz-brand.webp') }}" width="36" height="36" alt=""><div><strong>KazuhaDev.</strong><small>© {{ now()->year }} · License Store</small></div></div><div class="store-footer-info"><x-icon name="shield" size="19"/><div><strong>Lisensi perangkat</strong><small>Masa berlaku sejak aktivasi</small></div></div><nav aria-label="Navigasi footer"><a href="{{ route('shop') }}">Katalog lisensi <x-icon name="arrow" size="13"/></a><a href="{{ route($memberSessionLicense ? 'member.dashboard' : 'member.login') }}">{{ $memberSessionLicense ? 'Dashboard member' : 'Portal member' }} <x-icon name="arrow" size="13"/></a></nav></footer></main><x-feedback/></body></html>
