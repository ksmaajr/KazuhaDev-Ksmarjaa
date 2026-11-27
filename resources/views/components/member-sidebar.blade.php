@props(['license'])
<button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Tutup navigasi" tabindex="-1" hidden></button>
<aside class="sidebar member-sidebar" id="sidebar" aria-label="Navigasi member"><a class="brand" href="{{ route('member.dashboard') }}"><img class="brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="38" height="38" alt=""><span>KazuhaDev<span class="brand-dot">.</span></span><span class="version">MEMBER</span></a><button type="button" class="icon-btn sidebar-close" data-sidebar-close aria-label="Tutup sidebar"><x-icon name="close" size="17"/></button><div class="workspace"><span class="workspace-avatar"><x-icon name="key" size="17"/></span><div><strong>Ruang member</strong><small>{{ $license->key_hint }}</small></div></div><nav aria-label="Menu member"><div class="ms-nav-caption">WORKSPACE</div><a href="{{ route('member.dashboard') }}" class="nav-item {{ request()->routeIs('member.dashboard')?'selected':'' }}"><x-icon name="grid" size="18"/><span>Dashboard saya</span></a><x-sidebar-group id="member-license" title="Lisensi & akses" icon="key" :items="[['member.renew','clock','Perpanjang lisensi'],['member.shop','shopping','Pesanan saya']]"/><div class="ms-nav-caption">STORE</div><a href="{{ route('shop') }}" class="nav-item"><x-icon name="package" size="18"/><span>Katalog lisensi</span><x-icon name="arrow" size="14"/></a></nav><div class="sidebar-bottom"><div class="profile"><div class="avatar">{{ strtoupper(substr($license->member_name ?: $license->label,0,1)) }}</div><div><strong>{{ $license->member_name ?: $license->label }}</strong><small>Member portal</small></div><form method="post" action="{{ route('member.logout') }}">@csrf<button class="icon-btn" aria-label="Keluar"><x-icon name="logout" size="17"/></button></form></div></div></aside>
<style>
.member-sidebar{padding:20px 12px 0;font-family:'Manrope','Plus Jakarta Sans','Segoe UI',system-ui,sans-serif}
.member-sidebar .brand{gap:8px}.member-sidebar .brand-mark{width:38px;height:38px;object-fit:cover;border-radius:10px;flex:none}
.member-sidebar .workspace{padding:12px;border-radius:12px;margin:20px 0;background:var(--soft);border:1px solid var(--line)}
.member-sidebar .workspace small{overflow-wrap:anywhere}
.member-sidebar .ms-nav-caption{font-size:10px;letter-spacing:1.2px;color:var(--muted);font-weight:700;margin:18px 9px 8px}
.member-sidebar .nav-item{border-radius:10px;min-height:44px;transition:background .18s,color .18s}
.member-sidebar .nav-item.selected{background:var(--mint-bg);color:var(--mint);border-color:var(--mint)}
.member-sidebar .sidebar-bottom .profile{padding:10px 6px;gap:9px}
:root[data-theme="light"] .member-sidebar{--panel:#241d2e;--soft:#352a40;--line:#52425f;--muted:#d1c2da;--text:#faf4fc;--mint:#f0b4df;--mint-bg:#493049;background:var(--panel);color:var(--text);border-color:var(--line)}
:root[data-theme="light"] .member-sidebar .brand,:root[data-theme="light"] .member-sidebar .profile strong{color:#fff}
:root[data-theme="light"] .member-sidebar .nav-item{color:#e0d3e8}
:root[data-theme="light"] .member-sidebar .nav-item.selected{color:#ffd0ee}
@media(prefers-reduced-motion:reduce){.member-sidebar .nav-item{transition:none}}
</style>