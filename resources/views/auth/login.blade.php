<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Masuk · KazuhaDev.</title><link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml"><x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="login-body">
<div class="login-art">
<a href="{{ route('login') }}" class="brand"><span class="brand-mark"><x-icon name="sparkles" size="23"/></span>KazuhaDev<span class="brand-dot">.</span><span class="version">PRO</span></a>
<div class="login-story"><div class="eyebrow"><span class="status-dot"></span> YOUR ACCESS CONTROL CENTER</div><h1>Akses yang tepat.<br>Di tangan<br><em>yang tepat.</em></h1><p>Satu tempat untuk mengelola key, mengamankan perangkat, dan mengendalikan akses ke aplikasi Anda.</p>
<div class="orbit-art" aria-hidden="true"><div class="orbit-ring ring-one"></div><div class="orbit-ring ring-two"></div><div class="orbit-ring ring-three"></div><div class="orbit-center"><x-icon name="shield" size="53"/></div><div class="orbit-label label-one"><span class="status-dot"></span> HWID verified</div><div class="orbit-label label-two"><x-icon name="key" size="15"/> License activated</div><div class="orbit-node node-one"></div><div class="orbit-node node-two"></div></div>
</div><div class="login-art-footer"><span>SELF-HOSTED. ALWAYS YOURS.</span><x-icon name="globe" size="17"/></div>
</div>
<div class="login-panel"><div class="login-theme-row"><x-theme-toggle/></div><span class="login-version">KAZUHADEV / ADMIN PORTAL</span><div class="login-form-wrap"><span class="login-lock"><x-icon name="lock" size="24"/></span><h2>Selamat datang kembali.</h2><p class="muted">Masuk untuk mengelola workspace Anda.</p>
<x-flash-messages/>
<form method="post" action="{{ route('login.store') }}" class="login-form">@csrf
<label for="email">Alamat email</label><input id="email" name="email" type="email" placeholder="admin@domain.com" value="{{ old('email') }}" autocomplete="username" required autofocus>
<label for="password">Password</label><div class="password-field"><input id="password" name="password" type="password" placeholder="Masukkan password Anda" autocomplete="current-password" required><button type="button" class="icon-btn" id="password-toggle" aria-label="Tampilkan password"><x-icon name="eye" size="18"/></button></div>
<label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Ingat saya selama 30 hari</label>
<button class="btn primary full" type="submit">Masuk ke dashboard <x-icon name="arrow" size="18"/></button>
</form><div class="login-assurance"><x-icon name="shield" size="16"/> Akses khusus administrator · Pendaftaran ditutup</div><a class="admin-login-link" href="{{ route('member.login') }}">Punya license key? Buka member portal <x-icon name="arrow" size="13"/></a>
</div><div class="login-bottom">Powered by Laravel <span>© {{ now()->year }} KazuhaDev.</span></div></div>
<x-feedback/></body></html>
