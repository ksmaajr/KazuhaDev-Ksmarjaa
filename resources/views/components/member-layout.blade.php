@props(['title'=>'Member','license'])
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>{{ $title }} · KazuhaDev.</title><link rel="icon" href="{{ asset('kz-brand.webp') }}"><x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])<style>
body.member-with-sidebar{font-family:'Manrope','Plus Jakarta Sans','Segoe UI Variable','Segoe UI',system-ui,sans-serif}
.member-with-sidebar .member-app-shell .member-header{background:var(--panel);border-bottom:1px solid var(--line);box-shadow:0 3px 18px #0000000b}
.member-with-sidebar .member-header .breadcrumbs{font-weight:500;min-width:0}
.member-with-sidebar .member-header .breadcrumbs strong{font-weight:700}
.member-with-sidebar .member-header-actions .btn{min-height:36px;border-radius:9px}
.member-with-sidebar .member-header-actions>a.btn{background:var(--mint-bg);color:var(--mint);border-color:var(--line)}
.member-with-sidebar .member-header-actions>a.btn:hover{border-color:var(--mint)}
.member-with-sidebar .member-main .ms-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:24px;padding:16px 20px;border:1px solid #574461;border-radius:13px;background:linear-gradient(115deg,#201b29,#35283e);color:#e3d3ec;font-size:12px}
:root[data-theme="light"] .member-with-sidebar .member-app-shell .member-header{background:#fff;border-color:#cbbfd3}
@media(max-width:650px){.member-with-sidebar .member-app-shell .member-header{height:auto;min-height:68px;padding-block:10px;gap:7px;flex-wrap:wrap}.member-with-sidebar .member-header-actions{gap:5px}.member-with-sidebar .member-main .ms-footer{padding:12px}}
</style></head><body class="member-body member-with-sidebar" data-sidebar-scope="member" data-feed-url="{{ route('notifications.member',absolute:false) }}">
<x-member-sidebar :license="$license"/><div class="app-shell member-app-shell"><header class="member-header"><button class="icon-btn sidebar-toggle" type="button" id="menu-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu" size="20"/></button><div class="breadcrumbs">Member portal <x-icon name="chevron" size="13"/><strong>{{ $title }}</strong></div><div class="member-header-actions"><x-notification-bell/><x-theme-toggle/><form method="post" action="{{ route('member.logout') }}">@csrf<button class="btn secondary compact">Keluar</button></form></div></header><main class="member-main"><div data-live-content><x-flash-messages/>{{ $slot }}</div><footer class="footer ms-footer"><span>© {{ now()->year }} KazuhaDev. · Member portal</span><span>Pesanan dan akses tersimpan aman.</span></footer></main></div><x-feedback/></body></html>
