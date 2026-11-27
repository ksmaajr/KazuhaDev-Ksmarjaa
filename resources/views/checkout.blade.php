<x-public-layout title="Status Pesanan">
@php
    $isPaid = $order->status === 'paid';
    $isCancelled = $order->status === 'cancelled';
    $awaitingGateway = in_array($order->gateway_status, ['creating','uncertain','pending']);
    $invoiceState = $isPaid ? 'success' : ($isCancelled ? 'cancelled' : ($awaitingGateway ? 'processing' : 'pending'));
@endphp
<div class="receipt-wrap invoice-experience checkout-studio" data-invoice-state="{{ $invoiceState }}" data-payment-watch="{{ route('checkout.status',$order) }}" data-order-status="{{ $order->status }}" data-gateway-status="{{ $order->gateway_status }}">
    <div class="invoice-ambience" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="receipt-topline"><a class="text-link" href="{{ $order->purpose==='renewal'?route('member.renew'):route('shop') }}"><x-icon name="arrow" class="invoice-back-arrow" size="15"/> {{ $order->purpose==='renewal'?'Pilihan perpanjangan':'Kembali ke paket' }}</a><span class="invoice-security"><x-icon name="lock" size="13"/> KAZUHADEV CHECKOUT</span></div>
    <section class="panel receipt-card invoice-shell">
        <div class="invoice-card-glow" aria-hidden="true"></div>
        <header class="invoice-hero">
            <div class="invoice-hero-top"><span class="invoice-brand-chip"><x-icon name="sparkles" size="14"/> CHECKOUT / 02</span><span class="invoice-status-pill"><i aria-hidden="true"></i>{{ $order->gateway_mode==='sandbox'?'SANDBOX / DEMO':($isPaid?'PEMBAYARAN BERHASIL':($isCancelled?'DIBATALKAN':($awaitingGateway?'MENUNGGU GATEWAY':'MENUNGGU PEMBAYARAN'))) }}</span></div>
            @if($isPaid)
                <div class="payment-result success"><div class="success-orbit"><svg viewBox="0 0 72 72" fill="none" aria-hidden="true"><circle cx="36" cy="36" r="31"/><path d="m22 36 10 10 19-21"/></svg></div><span class="eyebrow">{{ $order->gateway_mode==='sandbox'?'SANDBOX CONFIRMED':'PAYMENT VERIFIED' }}</span><h1>{{ $order->gateway_mode==='sandbox'?'Simulasi berhasil.':($order->purpose==='renewal'?'Akses Anda diperpanjang.':'Pembayaran berhasil.') }}</h1><p>{{ $order->gateway_mode==='sandbox'?'Transaksi demo terverifikasi. Key demo hanya untuk pengujian portal member; tidak ada pendapatan riil.':($order->purpose==='renewal'?'Durasi ditambahkan pada key yang sama. HWID dan akses game tetap.':'Key baru Anda siap digunakan. Masa berlaku dimulai saat aktivasi pertama.') }}</p></div>
            @elseif($isCancelled)
                <div class="payment-result"><div class="invoice-hero-icon"><x-icon name="ban" size="30"/></div><span class="eyebrow">ORDER CANCELLED</span><h1>Pesanan dibatalkan.</h1><p>{{ $order->cancellation_reason ?: 'Tidak ada key atau perpanjangan diterbitkan dari pesanan ini.' }}</p></div>
            @else
                <div class="payment-result"><div class="invoice-hero-icon"><x-icon :name="$awaitingGateway ? 'clock' : 'wallet'" size="30"/><span class="invoice-orbit-dot" aria-hidden="true"></span></div><span class="eyebrow">{{ in_array($order->gateway_status,['uncertain','creating'])?'WAITING FOR RECONCILIATION':'PAYMENT / SECURE CHECKOUT' }}</span><h1>{{ in_array($order->gateway_status,['failed','expired'])?'Pembayaran belum selesai.':(in_array($order->gateway_status,['uncertain','creating'])?'Memastikan pembayaran Anda.':'Selesaikan pembayaran.') }}</h1><p>{{ in_array($order->gateway_status,['uncertain','creating'])?'Pesanan tersimpan. Jangan membuat pembayaran ulang; tunggu konfirmasi gateway atau hubungi admin dengan nomor pesanan.':'Pilih metode pembayaran. Status diperbarui otomatis setelah gateway mengonfirmasi.' }}</p></div>
            @endif
            <ol class="invoice-progress" aria-label="Tahapan pembayaran"><li class="is-complete"><span><x-icon name="check" size="15"/></span><div><strong>Invoice dibuat</strong><small>Pesanan tersimpan</small></div></li><li class="{{ $isPaid ? 'is-complete' : ($isCancelled ? '' : 'is-current') }}"><span><x-icon :name="$isPaid ? 'check' : 'wallet'" size="15"/></span><div><strong>Pembayaran</strong><small>{{ $isPaid ? 'Terverifikasi' : ($isCancelled ? 'Dibatalkan' : 'Pilih & selesaikan') }}</small></div></li><li class="{{ $isPaid ? 'is-complete' : '' }}"><span><x-icon name="key" size="15"/></span><div><strong>{{ $order->gateway_mode==='sandbox'?'Hasil simulasi':'Akses lisensi' }}</strong><small>{{ $isPaid ? ($order->gateway_mode==='sandbox'?'Demo selesai':'Akses tersedia') : 'Setelah konfirmasi' }}</small></div></li></ol>
        </header>
        <div class="invoice-grid">
            <section class="invoice-summary" aria-label="Ringkasan invoice"><h2 class="invoice-section-title"><span><x-icon name="receipt" size="18"/></span> Detail pesanan</h2>
                <div class="receipt-reference"><span><x-icon name="hash" size="13"/> Nomor invoice</span><strong>{{ $order->number }}</strong><button type="button" class="icon-btn" data-copy="{{ $order->number }}" aria-label="Salin nomor invoice"><x-icon name="copy" size="16"/></button></div>
                <div class="studio-order-group"><span class="studio-order-caption">01 / INFORMASI PEMBELI</span>
                <dl class="invoice-facts studio-buyer-facts">
                    <div><dt><x-icon name="user" size="15"/>Nama pemilik</dt><dd>{{ $order->member_name }}</dd></div>
                    <div><dt>WhatsApp / kontak</dt><dd>{{ $order->customer_contact ?: 'Tidak dicantumkan' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $order->customer_email ?: 'Tidak dicantumkan' }}</dd></div>
                </dl></div>
                <div class="studio-order-group"><span class="studio-order-caption">02 / INFORMASI LISENSI</span>
                <dl class="invoice-facts">
                    @foreach([
                        ['package','Paket',$order->product_name],
                        ['shopping','Jenis',$order->purpose==='renewal'?'Perpanjangan key yang sama':'Pembelian key baru'],
                        ['clock','Durasi',\App\Support\LicenseDuration::label($order->duration_seconds ?? $order->days*86400)],
                        ['key','Aktivasi',$order->purpose==='renewal'?'Setelah pembayaran terverifikasi':'Sejak aktivasi pertama']
                    ] as [$icon,$label,$value])<div><dt><x-icon :name="$icon" size="15"/>{{ $label }}</dt><dd>{{ $value }}</dd></div>@endforeach
                    @if($order->target_key_hint)<div><dt><x-icon name="key" size="15"/>Key tujuan</dt><dd>{{ $order->target_key_hint }}</dd></div>@endif
                </dl>
                @if(!empty($order->game_snapshot))<div class="studio-order-games"><span>Game dalam paket</span><div>@foreach($order->game_snapshot as $game)<span class="studio-game-chip"><x-icon name="check" size="13"/> {{ $game['name'] ?? $game['code'] ?? 'Game' }}</span>@endforeach</div></div>@endif
                @if($order->product_description)<p class="studio-order-description">{{ $order->product_description }}</p>@endif
                </div>
                <div class="studio-order-group"><span class="studio-order-caption">03 / INFORMASI TRANSAKSI</span><dl class="invoice-facts">
                    <div><dt><x-icon name="wallet" size="15"/>Metode</dt><dd>{{ $order->payment_provider ? $order->payment_provider.' · '.strtoupper($order->payment_method) : 'Belum dipilih' }}</dd></div>
                    <div><dt>Status pesanan</dt><dd class="studio-order-status {{ $isPaid ? 'is-paid' : '' }}">{{ $isPaid ? 'Terverifikasi' : ($isCancelled ? 'Dibatalkan' : 'Menunggu pembayaran') }}</dd></div>
                    <div><dt>Mode transaksi</dt><dd>{{ ($order->gateway_mode ?: $gatewayMode)==='sandbox' ? 'Sandbox / Demo' : 'Production' }}</dd></div>
                    <div><dt><x-icon name="calendar" size="15"/>Dibuat</dt><dd>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</dd></div>
                    @if($order->gateway_expires_at && !$isPaid && !$isCancelled)<div><dt>Batas pembayaran</dt><dd>{{ $order->gateway_expires_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</dd></div>@endif
                    @if($order->paid_at)<div><dt>Dibayar</dt><dd>{{ $order->paid_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</dd></div>@endif
                    @if($order->issuedLicense)<div><dt>Key terdaftar</dt><dd>{{ $order->issuedLicense->key_hint }}</dd></div>@endif
                    <div class="studio-order-total"><dt>Total invoice</dt><dd>Rp {{ number_format($order->amount,0,',','.') }}</dd></div>
                </dl></div>
                <div class="invoice-assurance"><x-icon name="shield" size="19"/><div><strong>Konfirmasi dari server</strong><p>Nominal mengikuti invoice. Status lunas hanya diproses setelah pembayaran terverifikasi.</p></div></div>
            </section>
            <section class="invoice-payment" aria-label="Pembayaran invoice"><h2 class="invoice-section-title"><span><x-icon name="wallet" size="18"/></span>{{ $isPaid?'Hasil pembayaran':'Metode & pembayaran' }}</h2>
                <div class="invoice-total-card"><div class="studio-total-kicker">KAZUHADEV <span> / </span> INVOICE</div><span><x-icon name="wallet" size="14"/> {{ $isPaid?'Total pembayaran':'Total invoice' }}</span><strong><small>Rp</small> {{ number_format($order->amount,0,',','.') }}</strong><div><x-icon :name="$isPaid ? 'check' : 'clock'" size="13"/><span>{{ $isPaid ? ($order->gateway_mode==='sandbox'?'Terkonfirmasi · simulasi':'Lunas · terverifikasi') : ($isCancelled?'Pesanan dibatalkan':'Belum dibayar') }}</span></div></div>
                @if($isPaid && $order->issuedLicense)
                    <div class="paid-key"><span class="eyebrow"><x-icon name="key" size="15"/> {{ $order->gateway_mode==='sandbox'?'LICENSE KEY DEMO':($order->purpose==='renewal'?'KEY ANDA TETAP SAMA':'LICENSE KEY ANDA') }}</span><code>{{ $order->issuedLicense->key_value }}</code>@if($order->gateway_mode==='sandbox')<p class="field-help">Key demo hanya untuk login portal member, tanpa akses game produksi.</p>@endif<button type="button" class="btn secondary compact" data-copy="{{ $order->issuedLicense->key_value }}"><x-icon name="copy" size="15"/> Salin key</button><a class="btn primary compact" href="{{ route('member.login') }}"><x-icon name="device" size="15"/> Buka portal member <x-icon name="arrow" size="15"/></a></div>
                @elseif($isPaid)
                    <div class="invoice-notice"><x-icon name="check" size="20"/><div><strong>Simulasi pembayaran selesai</strong><p>Tidak ada akses produksi atau pemasukan riil dari simulasi ini.</p></div></div>
                    <div class="studio-member-action"><a class="btn primary full" href="{{ route('member.login') }}"><x-icon name="user" size="17"/> Masuk ke portal member <x-icon name="arrow" size="17"/></a><p class="field-help">Gunakan license key aktif untuk masuk ke akun member.</p></div>
                @elseif($order->status==='pending')
                    @if($order->gateway_payment_url && $order->gateway_status==='pending')
                        <div class="checkout-pay-area"><span class="eyebrow"><x-icon name="receipt" size="14"/> INVOICE SIAP</span><p>Lanjutkan ke halaman SumoPod. Gunakan invoice yang sama jika halaman pembayaran tertutup.</p><a class="btn primary full" href="{{ $order->gateway_payment_url }}" target="_blank" rel="noopener noreferrer"><x-icon name="wallet" size="16"/> Lanjutkan pembayaran QRIS <x-icon name="arrow" size="17"/></a><small>Kedaluwarsa {{ $order->gateway_expires_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? 'mengikuti invoice' }} WIB</small></div>
                    @elseif(!$order->gateway_status && $gatewayReady)
                        <form class="checkout-pay-area" method="post" action="{{ route('checkout.payment',$order) }}" data-once>@csrf<span class="eyebrow"><x-icon name="wallet" size="14"/> PILIH METODE PEMBAYARAN</span><fieldset class="studio-method-options"><legend>Metode pembayaran</legend><label class="studio-method-choice"><input type="radio" name="method" value="QRIS" checked required><span class="studio-method-icon"><x-icon name="wallet" size="24"/></span><span class="studio-method-copy"><strong>QRIS</strong><small>Semua aplikasi pembayaran QRIS</small></span><span class="studio-method-check" aria-hidden="true"><x-icon name="check" size="15"/></span></label>@if($gatewayMode==='sandbox')<label class="studio-method-choice"><input type="radio" name="method" value="QRIS_INSTANT"><span class="studio-method-icon"><x-icon name="sparkles" size="24"/></span><span class="studio-method-copy"><strong>QRIS Instant <small class="studio-demo-tag">DEMO</small></strong><small>Simulasi pembayaran instan</small></span><span class="studio-method-check" aria-hidden="true"><x-icon name="check" size="15"/></span></label>@endif</fieldset>
                        <div class="studio-terms"><label class="studio-terms-consent"><input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))><span class="studio-consent-mark" aria-hidden="true"><x-icon name="check" size="13"/></span><span class="studio-consent-copy">Saya menyetujui syarat &amp; ketentuan yang berlaku.</span></label><details><summary>Baca S&amp;K <span aria-hidden="true">↗</span></summary><ul><li>Pastikan paket, durasi, dan data pembeli sudah benar sebelum melanjutkan.</li><li>Lisensi berlaku untuk satu perangkat; masa berlaku key baru dimulai saat aktivasi pertama.</li><li>Perpanjangan lisensi mengikuti konfirmasi pembayaran terverifikasi.</li><li>Status pembayaran ditentukan oleh konfirmasi server, bukan bukti transfer atau redirect semata.</li>@if($gatewayMode==='sandbox')<li>Transaksi demo tidak mencatat pendapatan riil. Key demo hanya untuk pengujian portal member.</li>@endif</ul></details>@error('accept_terms')<p class="studio-terms-error" role="alert">{{ $message }}</p>@enderror</div>
                        <button class="btn primary full studio-pay-submit" type="submit"><x-icon name="receipt" size="16"/> Lanjutkan ke pembayaran <x-icon name="arrow" size="17"/></button><p class="field-help">Nominal mengikuti pesanan server. Klik ganda tidak membuat invoice baru.</p></form>
                    @elseif(!$gatewayReady && !$order->gateway_status)
                        <div class="invoice-notice is-warning" role="status"><x-icon name="info" size="20"/><div><strong>Pembayaran belum siap</strong><p>{{ $gatewayIssue }} Pesanan Anda tersimpan; hubungi admin dan sertakan nomor pesanan. Setelah konfigurasi lengkap, muat ulang halaman ini untuk memilih metode pembayaran.</p></div></div>
                    @else
                        <div class="invoice-notice" role="status"><x-icon name="shield" size="20"/><div><strong>Menunggu konfirmasi gateway</strong><p>Status gateway: {{ $order->gateway_status }}. Jangan membayar ulang sebelum status dikonfirmasi. Hubungi admin dengan nomor pesanan jika memerlukan bantuan.</p></div></div>
                    @endif
                @else
                    <a class="btn secondary full" href="{{ route('shop') }}"><x-icon name="shopping" size="16"/> Kembali ke paket</a>
                @endif
            </section>
        </div>
        @if($order->status==='pending')<div class="receipt-live"><span class="invoice-live-dot" aria-hidden="true"></span><span data-payment-poll-label>Menunggu konfirmasi server · diperiksa otomatis</span><button type="button" class="text-link" data-payment-refresh><x-icon name="reset" size="13"/> Periksa sekarang</button></div>@endif
    </section>
    <p class="receipt-safety"><x-icon name="shield" size="14"/> Pesanan dilindungi sesi browser Anda. Redirect pembayaran tidak dianggap sebagai bukti lunas.</p>
</div>
<style>
.checkout-studio.invoice-experience{max-width:1100px;margin-inline:auto;--studio-radius:20px}
.checkout-studio .invoice-ambience,.checkout-studio .invoice-card-glow{display:none}
.checkout-studio .invoice-shell.receipt-card{background:transparent;border:0;box-shadow:none;border-radius:0;overflow:visible;padding:0}
.checkout-studio .invoice-hero{border:1px solid var(--line);border-radius:24px;padding:24px 30px;background:radial-gradient(ellipse at 100% 0%,#b77ccc25,transparent 65%),var(--panel)}
.checkout-studio .invoice-hero-top{margin:0}
.checkout-studio .invoice-hero .payment-result{display:grid;grid-template-columns:76px minmax(0,1fr);column-gap:20px;align-items:center;text-align:left;max-width:none;margin:0;padding:26px 0}
.checkout-studio .payment-result .invoice-hero-icon,.checkout-studio .payment-result .success-orbit{grid-column:1;grid-row:1/4;margin:0;width:70px;height:70px;align-self:center}
.checkout-studio .payment-result .eyebrow,.checkout-studio .payment-result h1,.checkout-studio .payment-result p{grid-column:2;margin:0;text-align:left;max-width:680px}
.checkout-studio .payment-result h1{font-size:clamp(28px,3.5vw,44px);letter-spacing:-.045em;line-height:1.15;font-weight:750;margin:6px 0 8px}
.checkout-studio .payment-result p{font-size:14px;line-height:1.7}
.checkout-studio .invoice-progress{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:0;margin:0}
.checkout-studio .invoice-progress>li{padding:13px;border:1px solid var(--line);border-radius:12px;background:var(--soft);min-width:0}
.checkout-studio .invoice-progress>li:after{display:none}
.checkout-studio .invoice-progress>li.is-current{border-color:var(--mint);background:var(--mint-bg)}
.checkout-studio .invoice-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:18px;margin-top:18px;align-items:start;border:0}
.checkout-studio .invoice-summary,.checkout-studio .invoice-payment{padding:25px;border:1px solid var(--line);border-radius:var(--studio-radius);background:var(--panel);min-width:0}
.checkout-studio .invoice-section-title{font-size:19px;font-weight:700;letter-spacing:-.025em;margin-bottom:20px}
.checkout-studio .receipt-reference{border-radius:13px;background:var(--soft);padding:16px}
.checkout-studio .receipt-reference>strong{font-size:14px;overflow-wrap:anywhere}
.checkout-studio .invoice-facts>div{padding:12px 0;gap:12px}
.checkout-studio .invoice-facts dt{font-size:13px}
.checkout-studio .invoice-facts dd{font-size:14px;text-align:right;overflow-wrap:anywhere;min-width:0}
.checkout-studio .invoice-assurance{padding:14px;border:1px solid var(--line);border-radius:12px;background:var(--soft);margin-top:18px}
.checkout-studio .invoice-total-card{position:relative;overflow:hidden;padding:24px;border-radius:17px;background:radial-gradient(ellipse at 100% 0%,#a887df35,transparent 70%),var(--soft);border:1px solid var(--line)}
.checkout-studio .studio-total-kicker{display:block;font-size:10px;letter-spacing:2px;font-weight:700;color:var(--muted);margin-bottom:18px}
.checkout-studio .studio-total-kicker span{color:var(--mint)}
.checkout-studio .invoice-total-card>strong{font-size:clamp(36px,4vw,48px);font-weight:750;letter-spacing:-.055em;font-variant-numeric:tabular-nums;line-height:1.2}
.checkout-studio .checkout-pay-area{margin-top:24px}
.checkout-studio .studio-method{display:flex;align-items:center;gap:13px;padding:16px;border:1px solid var(--line);border-radius:13px;background:var(--soft);margin:12px 0 18px}
.checkout-studio .studio-method-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:10px;background:var(--panel);color:var(--mint)}
.checkout-studio .studio-method strong,.checkout-studio .studio-method small{display:block}
.checkout-studio .studio-method small{font-size:12px;color:var(--muted);margin-top:3px}
.checkout-studio .checkout-pay-area select{min-height:48px;border-radius:11px;font-size:14px}
.checkout-studio .checkout-pay-area .btn.primary{min-height:54px;border-radius:12px;font-size:14px;font-weight:700;justify-content:center;white-space:normal}
.checkout-studio .checkout-pay-area .btn.primary:after{animation:none}
.checkout-studio .paid-key{padding:20px;border-radius:16px}
.checkout-studio .paid-key code{user-select:all;font-size:16px}
.checkout-studio .receipt-live{margin-top:18px;border:1px solid var(--line);border-radius:14px;padding:16px;background:var(--panel)}
@media(max-width:800px){.checkout-studio .invoice-grid{grid-template-columns:1fr}.checkout-studio .invoice-payment{grid-row:1}.checkout-studio .invoice-hero{padding:20px}.checkout-studio .invoice-summary,.checkout-studio .invoice-payment{padding:20px}}
@media(max-width:480px){.checkout-studio .invoice-hero{padding:16px}.checkout-studio .invoice-hero .payment-result{grid-template-columns:1fr;gap:8px;padding:20px 0}.checkout-studio .payment-result .invoice-hero-icon,.checkout-studio .payment-result .success-orbit{grid-column:1;grid-row:auto;width:52px;height:52px}.checkout-studio .payment-result .eyebrow,.checkout-studio .payment-result h1,.checkout-studio .payment-result p{grid-column:1}.checkout-studio .invoice-progress{gap:5px}.checkout-studio .invoice-progress>li{padding:8px}.checkout-studio .invoice-summary,.checkout-studio .invoice-payment{padding:16px}.checkout-studio .invoice-total-card{padding:18px}.checkout-studio .invoice-progress small{overflow-wrap:anywhere}}
.checkout-studio .studio-member-action{margin-top:20px}.checkout-studio .studio-member-action .btn{min-height:50px;border-radius:12px;justify-content:center;font-size:14px}.checkout-studio .studio-member-action .field-help{margin:10px 0 0;text-align:center}
.checkout-studio .invoice-summary,.checkout-studio .invoice-payment{display:flex;flex-direction:column}
@media(min-width:801px){.checkout-studio .invoice-summary .invoice-assurance{margin-top:auto}.checkout-studio .invoice-summary .invoice-facts{margin-bottom:18px}}
 
.checkout-studio .studio-method-options{border:0;padding:0;margin:14px 0 18px;min-width:0;display:grid;gap:10px}
.checkout-studio .studio-method-options legend{font-size:12px;color:var(--muted);margin-bottom:10px}
.checkout-studio .checkout-pay-area .studio-method-choice{position:relative;display:flex;align-items:center;gap:12px;margin:0;padding:15px;border:1px solid var(--line);border-radius:14px;background:var(--soft);cursor:pointer;transition:border-color .18s,background .18s}
.checkout-studio .studio-method-choice:hover{border-color:var(--mint)}
.checkout-studio .studio-method-choice:has(input:checked){border-color:var(--mint);background:var(--mint-bg);box-shadow:0 0 0 1px var(--mint)}
.checkout-studio .studio-method-choice:has(input:focus-visible){outline:2px solid var(--mint);outline-offset:4px}
.checkout-studio .studio-method-choice input{position:absolute;width:1px;height:1px;min-height:0;padding:0;opacity:0}
.checkout-studio .studio-method-copy{flex:1;min-width:0}
.checkout-studio .studio-method-copy strong{display:block;font-size:14px;color:var(--text)}
.checkout-studio .studio-method-copy>small{display:block;font-size:12px;color:var(--muted);margin-top:3px}
.checkout-studio .studio-demo-tag{display:inline-block;font-size:9px;letter-spacing:.08em;color:var(--mint);margin-left:5px}
.checkout-studio .studio-method-check{display:grid;place-items:center;width:24px;height:24px;border:1px solid var(--line);border-radius:50%;flex-shrink:0}
.checkout-studio .studio-method-check svg{opacity:0}
.checkout-studio .studio-method-choice:has(input:checked) .studio-method-check{background:var(--mint);color:#25182b;border-color:var(--mint)}
.checkout-studio .studio-method-choice:has(input:checked) .studio-method-check svg{opacity:1}

.checkout-studio .studio-order-group{margin:14px 0 0;min-width:0}
.checkout-studio .studio-order-caption{display:block;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:1.2px;padding:8px 0;border-bottom:1px solid var(--line)}
.checkout-studio .studio-order-group .invoice-facts{margin:0 0 8px}
.checkout-studio .studio-order-group .invoice-facts>div{padding:9px 0;display:flex;justify-content:space-between;align-items:baseline}
.checkout-studio .studio-order-group .invoice-facts dt{font-size:12px;flex:0 0 40%}
.checkout-studio .studio-order-group .invoice-facts dd{font-size:13px;flex:1}
.checkout-studio .studio-order-games{display:grid;gap:8px;padding:10px 0;font-size:12px;color:var(--muted)}
.checkout-studio .studio-order-games>div{display:flex;flex-wrap:wrap;gap:6px}
.checkout-studio .studio-game-chip{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line);background:var(--soft);color:var(--text);border-radius:8px;padding:6px 9px}
.checkout-studio .studio-order-description{font-size:12px;line-height:1.7;color:var(--muted);overflow-wrap:anywhere;padding-bottom:8px}
.checkout-studio .studio-order-status{color:var(--mint);font-weight:600}
.checkout-studio .studio-order-status.is-paid{color:var(--invoice-success)}
.checkout-studio .studio-order-group .studio-order-total dd{font-size:20px;font-weight:750;letter-spacing:-.04em}
.checkout-studio .invoice-summary .invoice-assurance{margin-top:18px}
.checkout-studio .invoice-payment{align-self:start;height:auto;min-height:0}

.checkout-studio .studio-terms{padding:14px;border:1px solid var(--line);border-radius:12px;background:var(--soft);margin:16px 0}
.checkout-studio .checkout-pay-area .studio-terms-consent{display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin:0;font-size:13px;line-height:1.65;color:var(--text)}
.checkout-studio .studio-terms-consent input{appearance:auto;accent-color:var(--mint);width:18px;height:18px;min-height:0;flex:0 0 18px;margin:3px 0 0;padding:0;cursor:pointer}
.checkout-studio .studio-terms-consent strong{color:var(--mint);font-weight:650}
.checkout-studio .studio-terms details{margin:9px 0 0 28px;font-size:12px;color:var(--muted);line-height:1.65}
.checkout-studio .studio-terms summary{cursor:pointer;color:var(--mint);width:fit-content}
.checkout-studio .studio-terms ul{padding-left:17px;list-style:disc;margin:8px 0 0}
.checkout-studio .studio-terms li+li{margin-top:5px}
.checkout-studio .studio-terms-error{color:var(--red);font-size:12px;margin-top:8px}
.checkout-studio .checkout-pay-area:has(.studio-terms-consent input:not(:checked)) .studio-pay-submit{opacity:.48;box-shadow:none;cursor:not-allowed}

/* Compact, aligned consent component */
.checkout-studio .studio-terms{padding:16px;margin:16px 0;border-radius:14px}
.checkout-studio .checkout-pay-area .studio-terms-consent{position:relative;display:grid;grid-template-columns:20px minmax(0,1fr);align-items:start;column-gap:12px;margin:0;font-weight:400}
.checkout-studio .studio-terms-consent input{position:absolute;width:20px;height:20px;opacity:0;margin:0;inset:0 auto auto 0}
.checkout-studio .studio-consent-mark{display:grid;place-items:center;width:20px;height:20px;margin-top:1px;border:1px solid var(--muted);border-radius:6px;background:var(--panel);color:transparent;transition:background .15s,border-color .15s}
.checkout-studio .studio-terms-consent input:checked+.studio-consent-mark{background:var(--mint);border-color:var(--mint);color:#25182b}
.checkout-studio .studio-terms-consent input:focus-visible+.studio-consent-mark{outline:2px solid var(--mint);outline-offset:3px}
.checkout-studio .studio-consent-copy{display:grid;gap:4px;min-width:0}
.checkout-studio .studio-consent-copy strong{color:var(--text);font-size:13px;font-weight:650;line-height:20px}
.checkout-studio .studio-consent-copy small{color:var(--muted);font-size:12px;font-weight:400;line-height:1.6}
.checkout-studio .studio-terms details{margin:8px 0 0 32px}
.checkout-studio .studio-terms summary{display:flex;align-items:center;gap:6px;list-style:none;font-size:12px;font-weight:500}
.checkout-studio .studio-terms summary::-webkit-details-marker{display:none}
.checkout-studio .studio-terms summary span{font-size:13px}

/* Dense consent row */
.checkout-studio .studio-terms{padding:10px 12px;margin:12px 0;border-radius:10px;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:6px 10px}
.checkout-studio .checkout-pay-area .studio-terms-consent{grid-template-columns:18px minmax(0,1fr);align-items:center;column-gap:8px;font-size:12px;line-height:1.5}
.checkout-studio .studio-consent-mark{width:18px;height:18px;margin:0;border-radius:5px}
.checkout-studio .studio-terms-consent input{width:18px;height:18px}
.checkout-studio .studio-consent-copy{display:block;font-size:12px;line-height:1.5}
.checkout-studio .studio-terms details{margin:0;display:contents}
.checkout-studio .studio-terms summary{font-size:11px;white-space:nowrap;justify-self:end}
.checkout-studio .studio-terms details[open] ul{grid-column:1/-1;margin:5px 0 0 26px;padding-top:8px;border-top:1px solid var(--line)}
.checkout-studio .studio-terms-error{grid-column:1/-1;margin:0}
@media(max-width:520px){.checkout-studio .studio-terms{grid-template-columns:1fr;gap:4px}.checkout-studio .studio-terms summary{justify-self:start;margin-left:26px}}
</style>
</x-public-layout>
