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
</style><style>
.member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{background:linear-gradient(110deg,#211a2b,#30243c);border-bottom:1px solid #51415d;color:#f8effb;--panel:#251d30;--soft:#382c45;--line:#574663;--text:#f8effb;--muted:#d0c0da;--mint:#efb3df;--mint-bg:#4c334d;min-height:76px;height:auto;padding-block:12px;box-shadow:0 5px 20px #0002}
.ms-topbar-v6 .sidebar-toggle{background:#34283f;border:1px solid #51415d;color:#eed9f4;border-radius:10px;width:38px;height:38px}
.ms-topbar-v6 .breadcrumbs{display:flex;align-items:center;gap:9px;color:#cdbed7}
.ms-topbar-v6 .breadcrumbs strong{color:#fff;font-size:14px}
.ms-topbar-label{display:flex;flex-direction:column;gap:3px;min-width:0}
.ms-topbar-label small{font-size:9px;letter-spacing:1.2px;font-weight:650;color:#d2b2dd}
.member-with-sidebar .ms-topbar-v6 .member-header-actions .btn{min-height:38px;border-radius:9px}
.member-with-sidebar .ms-topbar-v6 .member-header-actions>a.btn{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border:0;font-weight:700;padding-inline:14px}
.member-with-sidebar .ms-topbar-v6 .member-header-actions .btn.secondary{background:#382c45;border-color:#594764;color:#f8effb}
.member-with-sidebar .ms-topbar-v6 .member-header-actions>a.btn.secondary{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border:0}
.ms-topbar-v6 .member-header-actions .notification-panel{background:var(--panel);color:var(--text)}
.member-with-sidebar .member-main .ms-footer-v6{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;padding:19px 22px;margin-top:24px;border:1px solid #594763;border-radius:16px;background:linear-gradient(115deg,#241b30,#35283e);color:#f4eaf8}
.ms-footer-v6 .ms-footer-brand,.ms-footer-v6 .ms-footer-status{display:flex;align-items:center;gap:10px}
.ms-footer-brand img{width:36px;height:36px;object-fit:cover;border-radius:9px}
.ms-footer-brand strong,.ms-footer-brand small,.ms-footer-status strong,.ms-footer-status small{display:block;line-height:1.6}
.ms-footer-brand strong,.ms-footer-status strong{font-size:12px;color:#fff}
.ms-footer-brand small,.ms-footer-status small{font-size:11px;color:#d5c5df}
.ms-footer-v6 .ms-footer-links{display:flex;gap:14px;flex-wrap:wrap}
.ms-footer-links a{display:inline-flex;align-items:center;gap:5px;color:#efd5ec;font-size:11px;text-decoration:none}
.ms-footer-links a:hover{color:#fff}
.ms-footer-v6 .status-dot{background:#9de1c5;box-shadow:0 0 0 3px #9de1c520}
@media(max-width:650px){.member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{padding:10px 12px;gap:8px;min-height:64px;flex-wrap:wrap}.ms-topbar-v6 .breadcrumbs{max-width:none!important;flex:1;gap:5px}.ms-topbar-v6 .breadcrumbs strong{font-size:12px}.ms-topbar-v6 .member-header-actions{flex-wrap:wrap;justify-content:flex-end}.member-with-sidebar .member-main .ms-footer-v6{padding:15px;gap:12px}.ms-footer-v6 .ms-footer-links{width:100%;padding-top:10px;border-top:1px solid #594763}}
</style><style>
/* Override the older light topbar rule with matching specificity. */
:root[data-theme="light"] .member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{background:linear-gradient(110deg,#211a2b,#30243c);border-color:#51415d;color:#f8effb}
:root[data-theme="light"] .member-with-sidebar .ms-topbar-v6 .breadcrumbs strong{color:#fff}
:root[data-theme="light"] .member-with-sidebar .ms-topbar-v6 .ms-topbar-label small{color:#d2b2dd}
</style></head><body class="member-body member-with-sidebar" data-sidebar-scope="member" data-feed-url="{{ route('notifications.member',absolute:false) }}">
<x-member-sidebar :license="$license"/><div class="app-shell member-app-shell"><header class="member-header ms-topbar-v6"><button class="icon-btn sidebar-toggle" type="button" id="menu-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu" size="20"/></button><div class="breadcrumbs"><span class="ms-topbar-label"><small>MEMBER WORKSPACE</small><strong>{{ $title }}</strong></span></div><div class="member-header-actions"><x-notification-bell/><x-theme-toggle/><form method="post" action="{{ route('member.logout') }}">@csrf<button class="btn secondary compact">Keluar</button></form></div></header><main class="member-main"><div data-live-content><x-flash-messages/>{{ $slot }}</div><footer class="footer ms-footer ms-footer-v6"><div class="ms-footer-brand"><img src="{{ asset('kz-brand.webp') }}" width="36" height="36" alt=""><span><strong>KazuhaDev.</strong><small>© {{ now()->year }} · Member portal</small></span></div><span class="ms-footer-status"><x-icon name="shield" size="20"/><span><strong>Member workspace</strong><small>Pesanan dan akses lisensi</small></span></span></footer></main></div><x-feedback/></body></html>
