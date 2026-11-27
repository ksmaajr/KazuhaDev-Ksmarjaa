<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Masuk · KazuhaDev.</title>
    <link rel="icon" href="{{ asset('kz-brand.webp') }}" type="image/webp">
    <x-theme-head/>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="kz-auth">
    <main class="kz-auth-shell" data-no-ui-icon>
        <section class="kz-auth-editorial" aria-labelledby="auth-heading">
            <div class="kz-auth-brand"><a href="{{ route('home') }}" class="kz-auth-wordmark" aria-label="KazuhaDev, beranda"><img class="kz-auth-monogram kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="42" height="42" alt=""><span>KazuhaDev<span class="kz-auth-period">.</span></span></a><span class="kz-auth-edition">ADMIN / 01</span></div>
            <div class="kz-auth-story">
                <span class="kz-auth-kicker"><span></span> LICENSE MANAGEMENT</span>
                <h1 id="auth-heading">Kendali penuh.<br><em>Akses terjaga.</em></h1>
                <p>Kelola lisensi, perangkat, dan akses member dari satu workspace yang terpusat.</p>
                <div class="kz-auth-diagram" aria-hidden="true">
                    <div class="kz-auth-diagram-head"><span>ACCESS WORKFLOW</span><span>01 — 03</span></div>
                    <div class="kz-auth-track">
                        <div class="kz-auth-stage"><span class="kz-auth-stage-icon"><x-icon name="key" size="23"/></span><strong>Lisensi</strong><small>Validasi key</small></div>
                        <div class="kz-auth-connector"></div>
                        <div class="kz-auth-stage"><span class="kz-auth-stage-icon"><x-icon name="device" size="23"/></span><strong>Perangkat</strong><small>Verifikasi HWID</small></div>
                        <div class="kz-auth-connector"></div>
                        <div class="kz-auth-stage"><span class="kz-auth-stage-icon"><x-icon name="shield" size="23"/></span><strong>Akses</strong><small>Kontrol member</small></div>
                    </div>
                    <div class="kz-auth-diagram-foot"><span><span class="kz-auth-square"></span> Ilustrasi alur akses</span><span>KEY / HWID / ACCESS</span></div>
                </div>
            </div>
            <div class="kz-auth-editorial-foot"><span>PRIVATE WORKSPACE</span><span>SELF-HOSTED &nbsp; / &nbsp; KAZUHADEV</span></div>
        </section>
        <section class="kz-auth-panel" aria-labelledby="login-heading">
            <header class="kz-auth-panel-head">
                <a class="kz-auth-mobile-brand kz-auth-wordmark" href="{{ route('home') }}"><img class="kz-auth-monogram kz-brand-image" src="{{ asset('kz-brand.webp') }}" width="42" height="42" alt="">KazuhaDev.</a>
                <span class="kz-auth-breadcrumb">Workspace <span>/</span> Administrator</span>
                <x-theme-toggle/>
            </header>
            <div class="kz-auth-form-wrap">
                <div class="kz-auth-form-intro">
                    <span class="kz-auth-lock"><x-icon name="lock" size="22"/></span>
                    <p class="kz-auth-overline">ADMINISTRATOR LOGIN</p>
                    <h2 id="login-heading">Selamat datang.</h2>
                    <p class="kz-auth-description">Masuk untuk melanjutkan ke workspace Anda.</p>
                </div>
                <x-flash-messages/>
                <form method="post" action="{{ route('login.store') }}" class="kz-auth-form" data-kz-auth-form>
                    @csrf
                    <div class="kz-auth-field">
                        <label for="email">Alamat email</label>
                        <div class="kz-auth-input"><span class="kz-auth-input-icon"><x-icon name="users" size="18"/></span><input id="email" name="email" type="email" placeholder="admin@domain.com" value="{{ old('email') }}" autocomplete="username" maxlength="255" required @if($errors->has('email')) aria-invalid="true" @endif></div>
                    </div>
                    <div class="kz-auth-field">
                        <label for="password">Password</label>
                        <div class="kz-auth-input"><span class="kz-auth-input-icon"><x-icon name="lock" size="18"/></span><input id="password" name="password" type="password" placeholder="Masukkan password Anda" autocomplete="current-password" maxlength="255" required @if($errors->has('password')) aria-invalid="true" @endif><button type="button" class="kz-auth-reveal" id="password-toggle" aria-label="Tampilkan password" aria-pressed="false"><x-icon name="eye" size="18"/></button></div>
                        <p class="kz-auth-caps" data-kz-caps hidden role="status">Caps Lock sedang aktif.</p>
                    </div>
                    <label class="kz-auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Ingat saya selama 30 hari</span></label>
                    <button type="submit" class="kz-auth-submit"><span data-kz-submit-label>Masuk ke dashboard</span><span class="kz-auth-submit-arrow"><x-icon name="arrow" size="19"/></span><span class="kz-auth-spinner" aria-hidden="true"></span></button>
                    <p class="kz-auth-sr" data-kz-auth-status role="status"></p>
                </form>
                <div class="kz-auth-assurance"><x-icon name="shield" size="17"/><span>Akses terbatas untuk administrator.</span></div>
                <a class="kz-auth-member" href="{{ route('member.login') }}"><span><small>Masuk dengan license key?</small><strong>Buka member portal</strong></span><x-icon name="arrow" size="19"/></a>
            </div>
            <footer class="kz-auth-panel-foot"><span>© {{ now()->year }} KazuhaDev.</span><a href="{{ route('home') }}">Kembali ke toko <x-icon name="arrow" size="13"/></a></footer>
        </section>
    </main>
    <x-feedback/>
</body>
</html>
