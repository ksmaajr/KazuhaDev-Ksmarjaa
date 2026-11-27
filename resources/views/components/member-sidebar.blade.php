@props(['license'])
<button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Tutup navigasi" tabindex="-1" hidden></button>
<aside class="sidebar member-sidebar" id="sidebar" aria-label="Navigasi member"><a class="brand" href="{{ route('member.dashboard') }}"><img class="brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="38" height="38" alt=""><span class="ms-brand-copy"><strong>KazuhaDev<span class="brand-dot">.</span></strong><small>MEMBER WORKSPACE</small></span></a><button type="button" class="icon-btn sidebar-close" data-sidebar-close aria-label="Tutup sidebar"><x-icon name="close" size="17"/></button><div class="workspace"><span class="workspace-avatar"><x-icon name="key" size="17"/></span><div><strong>Lisensi saya</strong><small>{{ $license->key_hint }}</small><span class="ms-workspace-tag">PERSONAL ACCESS</span></div></div><nav aria-label="Menu member"><div class="ms-nav-caption">WORKSPACE</div><a href="{{ route('member.dashboard') }}" class="nav-item {{ request()->routeIs('member.dashboard')?'selected':'' }}"><x-icon name="grid" size="18"/><span>Dashboard saya</span></a></nav><div class="sidebar-bottom"><div class="ms-sidebar-note"><x-icon name="shield" size="17"/><span>Member workspace<small>Akses melalui license key</small></span></div><div class="profile"><div class="avatar">{{ strtoupper(substr($license->member_name ?: $license->label,0,1)) }}</div><div><strong>{{ $license->member_name ?: $license->label }}</strong><small>Member portal</small></div><form method="post" action="{{ route('member.logout') }}">@csrf<button class="icon-btn" aria-label="Keluar"><x-icon name="logout" size="17"/></button></form></div></div></aside>
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

.member-sidebar{background:linear-gradient(160deg,#241b30,#19141f 60%);border-right:1px solid #42354c;--panel:#241b30;--soft:#30253c;--line:#4b3c57;--text:#f5edf8;--muted:#c4b5d0;--mint:#efb3df;--mint-bg:#49324c;color:var(--text);padding:20px 14px 12px}
.member-sidebar .brand{padding:5px 2px;min-width:0;color:var(--text);gap:10px}
.member-sidebar .brand-mark{width:42px;height:42px;box-shadow:0 4px 16px #a76bc634}
.ms-brand-copy{display:grid;gap:4px;min-width:0}
.ms-brand-copy strong{font-size:18px;letter-spacing:-.045em;line-height:1.2;color:#fff}
.ms-brand-copy small{font-size:8px;letter-spacing:1.3px;color:#c4b5d0;font-weight:650}
.member-sidebar .workspace{position:relative;margin:20px 0 8px;padding:13px 10px;background:linear-gradient(130deg,#3b2b49,#292033);border-color:#594367;gap:10px}
.ms-workspace-tag{display:block;font-size:8px;letter-spacing:.8px;color:#efb3df;margin-top:7px;font-weight:650}
.member-sidebar .nav-item{margin:3px 0;gap:10px;padding:10px 11px;color:var(--muted)}
.member-sidebar .nav-item.selected{border:1px solid #9a668e;background:linear-gradient(100deg,#56384f,#36283e);box-shadow:0 3px 12px #0002;color:#ffd5f0}
.member-sidebar .nav-item:hover{background:#3b2d47;color:#fff}
.member-sidebar .ms-nav-caption{margin:23px 8px 8px;color:#b8a6c5}
.member-sidebar .sidebar-bottom .profile{background:#30253c;border:1px solid #4b3c57;border-radius:12px;padding:11px 8px;margin-bottom:4px;color:#fff}
.member-sidebar .profile small{color:#c4b5d0}
.ms-sidebar-note{display:flex;gap:9px;align-items:center;padding:12px 5px;color:#efb3df;font-size:11px}
.ms-sidebar-note small{display:block;color:#c4b5d0;font-size:10px;margin-top:3px}
.member-sidebar .nav-item:focus-visible{outline:2px solid #efb3df;outline-offset:2px}
</style>