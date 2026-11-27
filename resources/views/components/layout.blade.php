@props(['title' => 'Overview', 'section' => 'Workspace'])
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex,nofollow">
<title>{{ $title }} · KazuhaDev.</title>
<link rel="icon" href="{{ asset('kz-brand.webp') }}" type="image/webp">
<x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body data-sidebar-scope="admin" data-feed-url="{{ route('notifications.admin',absolute:false) }}">
<a href="#main" class="skip-link">Lewati navigasi</a>
<button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Tutup navigasi" tabindex="-1" hidden></button><aside class="sidebar admin-sidebar" id="sidebar" aria-label="Navigasi administrator" data-no-ui-icon>
<a class="brand" href="{{ route('dashboard') }}"><img class="brand-mark admin-brand-mark kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="35" height="35" alt=""><span>KazuhaDev<span class="brand-dot">.</span></span><span class="version">PRO</span></a>
<div class="workspace"><span class="workspace-avatar"><x-icon name="shield" size="18"/></span><div><strong>License workspace</strong><small>{{ app()->environment('local') ? 'Local environment' : 'Server environment' }}</small></div><span class="admin-workspace-tag">ADMIN</span></div>
<button type="button" class="icon-btn sidebar-close" data-sidebar-close aria-label="Tutup sidebar"><x-icon name="close" size="17"/></button>
<nav aria-label="Menu utama"><div class="nav-caption">WORKSPACE</div><a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard')?'selected':'' }}"><x-icon name="grid" size="18"/><span>Overview</span></a>
<x-sidebar-group id="licenses" title="Lisensi" icon="key" :items="[['licenses','key','License keys'],['activity','activity','Riwayat aktivitas']]"/>
<x-sidebar-group id="sales" title="Penjualan & pembayaran" icon="shopping" :items="[['orders.index','receipt','Pesanan lisensi'],['products.index','package','Paket lisensi'],['finance.index','wallet','Keuangan & laporan']]"/>
<x-sidebar-group id="integration" title="Integrasi & game" icon="code" :items="[['games.index','gamepad','Konfigurasi game'],['api.console','code','API console']]"/>
<div class="nav-caption">SYSTEM</div>
<x-sidebar-group id="settings" title="Pengaturan" icon="settings" :items="[['settings','settings','Pengaturan API'],['payments.settings','wallet','Payment gateway']]"/>
<x-sidebar-group id="administration" title="Administrasi" icon="shield" :items="[['admins.index','users','Kelola administrator']]"/>
<div class="nav-caption">PORTAL</div><a href="{{ route('member.login') }}" class="nav-item"><x-icon name="device" size="18"/><span>Portal member</span><x-icon name="arrow" size="13"/></a><a href="{{ route('shop') }}" class="nav-item"><x-icon name="shopping" size="18"/><span>Toko publik</span><x-icon name="arrow" size="13"/></a></nav>
<div class="sidebar-bottom">
<div class="profile"><div class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div><div><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div><form action="{{ route('logout') }}" method="post">@csrf<button class="icon-btn" title="Keluar dari workspace" aria-label="Keluar dari workspace"><x-icon name="logout" size="18"/></button></form></div>
</div>
</aside>
<div class="app-shell">
<header class="topbar"><button class="icon-btn sidebar-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false" id="menu-toggle"><x-icon name="menu"/></button><div class="breadcrumbs">{{ $section }} <x-icon name="chevron" size="13"/> <strong>{{ $title }}</strong></div><div class="topbar-right"><x-notification-bell/><x-theme-toggle/><span class="environment">{{ app()->environment('local') ? 'LOCAL' : 'SERVER' }}</span><span class="topbar-date">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y') }} <span>WIB</span></span><div class="avatar small">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div></div></header>
<main id="main">
<x-flash-messages/>
<div data-live-content>{{ $slot }}</div>
<footer class="footer"><span>© {{ now()->year }} KazuhaDev. Built for your infrastructure.</span><span><span class="status-dot"></span> Laravel {{ Illuminate\Foundation\Application::VERSION }} <span class="footer-separator">/</span> Server time: <time id="server-clock" data-time="{{ now()->toIso8601String() }}">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</time> WIB</span></footer>
</main>
</div>
<x-feedback/>
</body>
</html>
