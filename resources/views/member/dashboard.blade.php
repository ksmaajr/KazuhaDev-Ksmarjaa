<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Member · KazuhaDev.</title><link rel="icon" href="{{ asset('kz-brand.webp') }}" type="image/webp"><x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])<style>
.member-studio{font-family:'Manrope','Plus Jakarta Sans','Segoe UI Variable','Segoe UI',system-ui,sans-serif}
.member-studio .member-app-shell .member-main{max-width:1440px;padding:28px clamp(16px,3vw,40px) 24px}
.member-studio .ms-content{display:grid;gap:20px;min-width:0}
.member-studio .ms-welcome{position:relative;isolation:isolate;overflow:hidden;padding:clamp(22px,3vw,36px);margin:0;min-height:150px;border:1px solid var(--line);border-radius:22px;background:radial-gradient(ellipse at 95% 0%,#a76ecb38,transparent 65%),var(--panel);align-items:center}
.ms-welcome:after{content:"";position:absolute;right:-65px;top:-130px;width:330px;height:330px;border:1px solid #b88ed238;border-radius:50%;box-shadow:0 0 0 30px #b88ed20b,0 0 0 60px #b88ed208;z-index:-1;pointer-events:none}
.member-studio .ms-welcome h1{font-family:inherit;font-size:clamp(28px,4vw,46px);font-weight:750;letter-spacing:-.045em;line-height:1.15;margin:10px 0;overflow-wrap:anywhere}
.member-studio .ms-welcome p{font-size:14px;line-height:1.65;margin:0;color:var(--muted)}
.member-studio .ms-welcome .badge{flex:none;padding:9px 12px;border-radius:9px;font-weight:600}
.ms-overview-grid{display:grid;grid-template-columns:minmax(0,.85fr) minmax(0,1.25fr);gap:18px;align-items:stretch}
.member-studio .ms-overview-grid .member-key,.member-studio .ms-overview-grid .duration-panel{margin:0!important;min-width:0;border-radius:18px;border:1px solid var(--line)}
.member-studio .ms-overview-grid .member-key{display:flex;flex-direction:column;align-items:stretch;justify-content:space-between;gap:16px;padding:22px;background:var(--panel)}
.ms-kicker{display:flex;align-items:center;gap:7px;color:var(--mint);font-size:10px;letter-spacing:1.1px;font-weight:700;margin-bottom:10px}
.member-studio .member-key h2,.member-studio .duration-panel h2{font-size:21px;letter-spacing:-.025em;margin:0 0 10px}
.member-studio .member-key code{display:block;white-space:normal;overflow-wrap:anywhere;padding:13px;background:var(--soft);border:1px solid var(--line);border-radius:9px;font-size:13px;line-height:1.7}
.member-studio .member-key p{font-size:12px;line-height:1.6;margin:12px 0 0}
.member-studio .member-key .btn{min-height:42px;justify-content:center}
.member-studio .duration-panel{overflow:hidden;background:radial-gradient(ellipse at 90% 0%,#b380ca35,transparent 70%),var(--panel)}
.member-studio .duration-panel .panel-heading{padding:22px 22px 10px}
.member-studio .member-countdown{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:12px 22px 20px;margin:0}
.member-studio .member-countdown>div{display:grid;justify-items:center;gap:6px;padding:13px 3px;border:1px solid var(--line);border-radius:12px;background:var(--soft);min-width:0}
.member-studio .member-countdown strong{font-size:clamp(24px,3vw,44px);line-height:1.05;font-weight:750;letter-spacing:-.05em;font-variant-numeric:tabular-nums}
.member-studio .member-countdown span{font-size:10px;letter-spacing:1px}
.member-studio .duration-meta{display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;padding:12px 22px;border-top:1px solid var(--line);font-size:11px}
.ms-section-label{display:grid;gap:6px;margin-top:4px}.ms-section-label>span{font-size:10px;letter-spacing:1.3px;color:var(--mint);font-weight:700}.ms-section-label h2{font-size:23px;letter-spacing:-.03em;margin:0}
.member-studio .member-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:0;align-items:stretch}
.member-studio .member-grid>.panel{border-radius:18px;overflow:hidden;display:flex;flex-direction:column;min-width:0;transition:border-color .2s,box-shadow .2s}
.member-studio .member-grid>.panel:hover{border-color:var(--mint);box-shadow:0 8px 24px #a170b515}
.member-studio .member-grid .panel-heading{padding:20px 22px;border-bottom:1px solid var(--line);background:var(--soft)}
.member-studio .member-grid .panel-heading h2{font-size:19px;letter-spacing:-.02em;margin:0 0 5px}
.member-studio .member-grid .form-content{padding:20px 22px;display:flex;flex-direction:column;gap:12px;flex:1}
.member-studio .member-grid .form-content p{margin:0;line-height:1.65}
.member-studio .member-grid .hwid-value{border-radius:10px;padding:12px}
.member-studio .member-grid .member-facts{margin:0}
.member-studio .member-grid .member-facts>div{padding:11px 0;gap:10px;border-bottom:1px solid var(--line)}
.member-studio .member-grid .btn{min-height:44px;border-radius:10px;font-weight:650}
.member-studio .renewal-steps{display:grid;gap:7px;margin:0}
.member-studio .renewal-steps>span{padding:9px;border:1px solid var(--line);border-radius:8px;background:var(--soft)}
.member-studio .ms-library{margin:0}.member-studio .ms-library .page-heading{margin:8px 0 16px}
.member-studio .ms-library .game-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));gap:16px;justify-content:start}
.member-studio .ms-library .game-card{border-radius:18px;overflow:hidden;transition:transform .2s,border-color .2s}
.member-studio .ms-library .game-card:hover{transform:translateY(-3px);border-color:var(--mint)}
.member-studio .ms-library .game-card-body{padding:18px;gap:10px}
.member-studio .ms-footer{margin-top:24px;padding:16px 20px;border:1px solid #574461;border-radius:13px;background:linear-gradient(115deg,#201b29,#35283e);color:#e3d3ec;font-size:12px}
.member-studio .ms-content>.ms-welcome,.member-studio .ms-content>.ms-overview-grid,.member-studio .ms-content>.member-grid,.member-studio .ms-content>.ms-library{animation:ms-enter .5s ease both}
.member-studio .ms-content>.ms-overview-grid{animation-delay:.06s}.member-studio .ms-content>.member-grid{animation-delay:.12s}.member-studio .ms-content>.ms-library{animation-delay:.18s}
@keyframes ms-enter{from{opacity:0;transform:translateY(9px)}to{opacity:1;transform:translateY(0)}}
:root[data-theme="light"] .member-studio .ms-welcome,:root[data-theme="light"] .member-studio .duration-panel{--panel:#292233;--soft:#3c3047;--line:#65536f;--text:#fff;--muted:#e0d3e7;--mint:#f2b3df;color:var(--text)}
:root[data-theme="light"] .member-studio .ms-welcome h1,:root[data-theme="light"] .member-studio .duration-panel h2{color:#fff}
:root[data-theme="light"] .member-studio .ms-overview-grid .member-key,:root[data-theme="light"] .member-studio .member-grid>.panel{background:#fff;border-color:#cbbfd3;box-shadow:0 6px 18px #34213e0d}
@media(max-width:1050px){.ms-overview-grid{grid-template-columns:minmax(0,1fr)}}
@media(max-width:700px){.member-studio .member-grid{grid-template-columns:minmax(0,1fr)}.member-studio .ms-content{gap:14px}.member-studio .ms-welcome{flex-wrap:wrap;gap:12px}.member-studio .member-countdown{padding:10px 14px 16px;gap:5px}.member-studio .member-countdown>div{padding:10px 2px}.member-studio .duration-panel .panel-heading,.member-studio .duration-meta{padding-inline:14px}}
@media(prefers-reduced-motion:reduce){.member-studio .ms-content>*{animation:none!important}.member-studio .ms-library .game-card:hover{transform:none}}

/* Consistent charcoal dashboard cards on the light canvas. */
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card){--panel:#292233;--soft:#382e43;--field:#211b2b;--line:#594963;--text:#f9f3fc;--muted:#d4c5de;--mint:#efb3df;--mint-bg:#493349;--red:#ffb5c4;--accent-purple:#cdb3f3;color:var(--text);background:radial-gradient(ellipse at 100% 0%,#a77ac315,transparent 65%),var(--panel);border-color:var(--line);box-shadow:0 7px 20px #30213d16}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card) :is(h2,h3,strong,dd){color:var(--text)}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card) :is(p,dt,small,.field-help,.game-card-description){color:var(--muted)}
:root[data-theme="light"] .member-studio .member-grid .panel-heading{background:var(--soft);border-color:var(--line)}
:root[data-theme="light"] .member-studio :is(.member-key code,.hwid-value,.renewal-steps>span,.member-countdown>div){background:var(--soft);border-color:var(--line);color:var(--mint)}
:root[data-theme="light"] .member-studio .member-countdown strong{color:var(--mint)}
:root[data-theme="light"] .member-studio .member-facts>div{border-color:var(--line)}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.member-device,.renewal-panel,.ms-library .game-card) .btn.secondary{background:var(--soft);color:var(--text);border-color:var(--line)}
:root[data-theme="light"] .member-studio .ms-content :is(.renewal-panel,.ms-library .game-card) .btn.primary{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border-color:transparent}
:root[data-theme="light"] .member-studio .ms-content .btn:disabled{opacity:.55;cursor:not-allowed}
:root[data-theme="light"] .member-studio .ms-library .empty-state{background:#292233;border:1px solid #594963;border-radius:18px;color:#f9f3fc;padding:26px;--muted:#d4c5de;--mint:#efb3df}
:root[data-theme="light"] .member-studio .ms-library .empty-state p{color:#d4c5de}
</style><style>
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
</style></head>
<body class="member-body member-with-sidebar member-studio" data-sidebar-scope="member" data-feed-url="{{ route('notifications.member',absolute:false) }}">
<x-member-sidebar :license="$license"/><div class="app-shell member-app-shell"><header class="member-header"><button class="icon-btn sidebar-toggle" type="button" id="menu-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu" size="20"/></button><div class="breadcrumbs">Member portal <x-icon name="chevron" size="13"/><strong>Dashboard saya</strong></div><div class="member-header-actions"><a class="btn secondary compact" href="{{ route('member.renew') }}"><x-icon name="clock" size="15"/> Perpanjang</a><x-notification-bell/><x-theme-toggle/><form method="post" action="{{ route('member.logout') }}">@csrf<button class="btn secondary compact" type="submit"><x-icon name="logout" size="15"/> Keluar</button></form></div></header>
<main class="member-main" data-member-status-url="{{ route('member.status',absolute:false) }}" data-member-login-url="{{ route('member.login',absolute:false) }}">
<div data-live-content class="ms-content" data-no-ui-icon><x-flash-messages/>
<div class="page-heading ms-welcome"><div><div class="eyebrow">MEMBER WORKSPACE / OVERVIEW</div><h1>Halo, {{ $license->member_name ?: $license->label }}.</h1><p>Kelola masa berlaku, perangkat, dan akses lisensi Anda dalam satu ruang.</p></div><span id="member-status" class="badge {{ $license->status() }}"><i></i>{{ ['active'=>'Lisensi aktif','unbound'=>'Menunggu perangkat','expired'=>'Kedaluwarsa','revoked'=>'Dicabut'][$license->status()] }}</span></div>
<div class="ms-overview-grid"><section class="panel member-key"><div><span class="ms-kicker"><x-icon name="key" size="16"/> IDENTITAS LISENSI</span><h2>License key Anda</h2><code>{{ $license->key_value ?? $license->key_hint }}</code><p>Simpan key ini dengan aman. Gunakan key yang sama setelah perpanjangan.</p></div>@if($license->key_value)<button class="btn secondary" data-copy="{{ $license->key_value }}"><x-icon name="copy" size="16"/> Salin key</button>@endif</section>

<section class="panel duration-panel"><div class="panel-heading"><div><span class="ms-kicker"><x-icon name="clock" size="15"/> MASA BERLAKU</span><h2>Durasi realtime</h2><p>{{ $license->expires_at ? 'Waktu terus berjalan sejak aktivasi pertama.' : 'Hitung mundur dimulai setelah aktivasi di injector.' }}</p></div><span class="api-symbol"><x-icon name="clock" size="24"/></span></div>
<div class="member-countdown" @if($license->expires_at) data-member-expires="{{ $license->expires_at->toIso8601String() }}" @endif>
@foreach(['Hari','Jam','Menit','Detik'] as $i=>$unit)<div><strong data-countdown-part="{{ $i }}">{{ !$license->expires_at ? str_pad((string) [intdiv($license->duration_seconds,86400),intdiv($license->duration_seconds % 86400,3600),intdiv($license->duration_seconds % 3600,60),$license->duration_seconds % 60][$i],2,'0',STR_PAD_LEFT) : '00' }}</strong><span>{{ $unit }}</span></div>@endforeach
</div><div class="duration-meta"><span id="member-timing-status"><span class="status-dot"></span> {{ !$license->activated_at ? 'Belum diaktifkan · durasi belum berjalan' : ($license->status()==='expired' ? 'Masa berlaku sudah habis' : 'Mengikuti waktu server') }}</span><span id="member-expiry-label">{{ $license->expires_at ? 'Expired: '.$license->expires_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB' : '1 hari = 24 jam' }}</span></div></section></div>
<div class="ms-section-label"><span>02 / PENGELOLAAN LISENSI</span><h2>Perangkat & perpanjangan</h2></div><div class="member-grid"><section class="panel member-device"><div class="panel-heading"><div><h2>Perangkat terhubung</h2><p>Satu key terikat ke satu HWID.</p></div><x-icon name="device" size="22"/></div><div class="form-content"><label>HWID SAAT INI</label><div class="hwid-value"><code id="member-hwid">{{ $license->hwid ?? 'Belum ada perangkat terikat' }}</code>@if($license->hwid)<button class="icon-btn" id="member-hwid-copy" data-copy="{{ $license->hwid }}" aria-label="Salin HWID"><x-icon name="copy" size="17"/></button>@endif</div><dl class="member-facts"><div><dt>Aktivasi pertama</dt><dd id="member-activated">{{ $license->activated_at ? $license->activated_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB' : 'Belum aktif' }}</dd></div><div><dt>Terakhir terhubung</dt><dd id="member-last-seen">{{ $license->last_seen_at?->timezone('Asia/Jakarta')->format('d M Y, H:i:s') ?? 'Belum ada' }}</dd></div><div><dt>Total reset</dt><dd id="member-reset-count">{{ $license->reset_count }} kali</dd></div></dl><form method="post" action="{{ route('member.reset') }}" data-confirm-title="Reset perangkat Anda?" data-confirm-action="Ya, reset HWID" data-confirm-icon="reset" data-confirm-hint="Reset tidak menambah atau mengulang durasi." data-confirm="Reset HWID perangkat Anda? Setelah reset, perangkat pertama yang melakukan validasi di injector akan terikat. Sisa durasi tidak berubah.">@csrf<button id="member-reset-button" type="submit" class="btn secondary full" @disabled($license->status()!=='active')><x-icon name="reset" size="16"/> Reset HWID saya</button></form><p class="field-help mb-0">Reset tidak menambah durasi. Tutup injector di perangkat lama, lalu buka kembali injector untuk mengikat perangkat. Maksimal satu reset per menit.</p></div></section>
<section class="panel renewal-panel"><div class="panel-heading"><div><h2>Butuh waktu lebih?</h2><p>Pilih paket untuk key yang sedang Anda gunakan.</p></div><x-icon name="key" size="22"/></div><div class="form-content"><p>Pilih paket yang sesuai dengan akses key Anda, lanjutkan pembayaran, dan pantau statusnya. Durasi ditambahkan setelah pembayaran terverifikasi.</p><a class="btn primary full" href="{{ route('member.renew') }}">Pilih paket perpanjangan <x-icon name="arrow" size="16"/></a><div class="renewal-steps"><span><b>01</b> Pilih paket perpanjangan</span><span><b>02</b> Pilih durasi & lakukan pembayaran</span><span><b>03</b> Key yang sama mendapat tambahan waktu</span></div>@if($renewalUrl)<a class="btn secondary full" href="{{ $renewalUrl }}" target="_blank" rel="noopener noreferrer"><x-icon name="message" size="17"/> Perpanjang via WhatsApp <x-icon name="arrow" size="16"/></a>@else<button class="btn secondary full" disabled>Nomor WhatsApp admin belum diatur</button>@endif<small>Pembayaran online dikonfirmasi gateway. Pembayaran manual diverifikasi administrator.</small></div></section></div>
<section class="member-games ms-library"><div class="page-heading"><div><div class="eyebrow">YOUR GAME LIBRARY</div><h2>Game & unduhan Anda</h2><p>Daftar game dan unduhan yang tersedia untuk lisensi Anda.</p></div><span class="subtle-chip">{{ $license->games->count() }} game</span></div><div class="game-grid">
@forelse($license->games as $game)<article class="panel game-card"><x-game-cover :game="$game" badge="AKSES KEY"/><div class="game-card-body"><div class="game-card-title"><div><span class="eyebrow">EXCLUSIVE ACCESS</span><h2>{{ $game->name }}</h2></div><span class="release-version">v{{ $game->release_version ?: '—' }}</span></div><p class="game-card-description">Akses terhubung ke lisensi dan perangkat Anda.</p>
<div class="download-facts"><span><x-icon name="device" size="15"/> Windows</span><span><x-icon name="download" size="15"/> {{ $game->injector_path ? number_format($game->injector_size/1048576,2).' MB' : 'Belum ada rilis' }}</span><span><x-icon name="shield" size="15"/> Key terverifikasi</span></div>
@if(!in_array($license->status(),['expired','revoked']) && $game->injector_path)<a class="btn primary full" href="{{ route('member.games.download',[$game,'injector']) }}"><x-icon name="download" size="18"/> Unduh Injector <x-icon name="arrow" size="16"/></a><details class="checksum"><summary>Verifikasi SHA-256 injector</summary><code>{{ $game->injector_sha256 }}</code></details>
@elseif(in_array($license->status(),['expired','revoked']))<button class="btn secondary full" disabled><x-icon name="lock" size="16"/> Perpanjang key untuk mengunduh</button>
@else<button class="btn secondary full" disabled>Injector sedang disiapkan</button>@endif
<div class="game-card-foot"><x-icon name="lock" size="12"/> Unduhan khusus pemilik key</div></div></article>@empty<div class="empty-state"><x-icon name="gamepad" size="38"/><strong>Library Anda menunggu.</strong><p>Hubungi admin untuk menambahkan akses game.</p></div>@endforelse</div></section>
</div><footer class="footer ms-footer"><span>© {{ now()->year }} KazuhaDev. · Member portal</span><span><span class="status-dot"></span> Server time <time id="server-clock" data-time="{{ now()->toIso8601String() }}">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</time> WIB</span></footer>
</main></div><x-feedback/></body></html>
