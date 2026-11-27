<x-public-layout title="Status Pesanan">
@php
    $isPaid = $order->status === 'paid';
    $isCancelled = $order->status === 'cancelled';
    $awaitingGateway = in_array($order->gateway_status, ['creating','uncertain','pending']);
    $invoiceState = $isPaid ? 'success' : ($isCancelled ? 'cancelled' : ($awaitingGateway ? 'processing' : 'pending'));
@endphp
<div class="receipt-wrap invoice-experience" data-invoice-state="{{ $invoiceState }}" data-payment-watch="{{ route('checkout.status',$order) }}" data-order-status="{{ $order->status }}" data-gateway-status="{{ $order->gateway_status }}">
    <div class="invoice-ambience" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="receipt-topline"><a class="text-link" href="{{ $order->purpose==='renewal'?route('member.renew'):route('shop') }}"><x-icon name="arrow" class="invoice-back-arrow" size="15"/> {{ $order->purpose==='renewal'?'Pilihan perpanjangan':'Kembali ke paket' }}</a><span class="invoice-security"><x-icon name="lock" size="13"/> KAZUHADEV CHECKOUT</span></div>
    <section class="panel receipt-card invoice-shell">
        <div class="invoice-card-glow" aria-hidden="true"></div>
        <header class="invoice-hero">
            <div class="invoice-hero-top"><span class="invoice-brand-chip"><x-icon name="sparkles" size="14"/> PAYMENT EXPERIENCE</span><span class="invoice-status-pill"><i aria-hidden="true"></i>{{ $order->gateway_mode==='sandbox'?'SANDBOX / DEMO':($isPaid?'PEMBAYARAN BERHASIL':($isCancelled?'DIBATALKAN':($awaitingGateway?'MENUNGGU GATEWAY':'MENUNGGU PEMBAYARAN'))) }}</span></div>
            @if($isPaid)
                <div class="payment-result success"><div class="success-orbit"><svg viewBox="0 0 72 72" fill="none" aria-hidden="true"><circle cx="36" cy="36" r="31"/><path d="m22 36 10 10 19-21"/></svg></div><span class="eyebrow">{{ $order->gateway_mode==='sandbox'?'SANDBOX CONFIRMED':'PAYMENT VERIFIED' }}</span><h1>{{ $order->gateway_mode==='sandbox'?'Simulasi berhasil.':($order->purpose==='renewal'?'Akses Anda diperpanjang.':'Pembayaran berhasil.') }}</h1><p>{{ $order->gateway_mode==='sandbox'?'Ini transaksi demo. Tidak ada key diterbitkan, durasi ditambah, atau pendapatan riil dicatat.':($order->purpose==='renewal'?'Durasi ditambahkan pada key yang sama. HWID dan akses game tetap.':'Key baru Anda siap digunakan. Masa berlaku dimulai saat aktivasi pertama.') }}</p></div>
            @elseif($isCancelled)
                <div class="payment-result"><div class="invoice-hero-icon"><x-icon name="ban" size="30"/></div><span class="eyebrow">ORDER CANCELLED</span><h1>Pesanan dibatalkan.</h1><p>{{ $order->cancellation_reason ?: 'Tidak ada key atau perpanjangan diterbitkan dari pesanan ini.' }}</p></div>
            @else
                <div class="payment-result"><div class="invoice-hero-icon"><x-icon :name="$awaitingGateway ? 'clock' : 'wallet'" size="30"/><span class="invoice-orbit-dot" aria-hidden="true"></span></div><span class="eyebrow">{{ in_array($order->gateway_status,['uncertain','creating'])?'WAITING FOR RECONCILIATION':'PAYMENT / SECURE CHECKOUT' }}</span><h1>{{ in_array($order->gateway_status,['failed','expired'])?'Pembayaran belum selesai.':(in_array($order->gateway_status,['uncertain','creating'])?'Memastikan pembayaran Anda.':'Selesaikan langkah terakhir.') }}</h1><p>{{ in_array($order->gateway_status,['uncertain','creating'])?'Pesanan tersimpan. Jangan membuat pembayaran ulang; tunggu konfirmasi gateway atau hubungi admin dengan nomor pesanan.':'Pilih metode pembayaran. Status diperbarui otomatis setelah gateway mengonfirmasi.' }}</p></div>
            @endif
            <ol class="invoice-progress" aria-label="Tahapan pembayaran"><li class="is-complete"><span><x-icon name="check" size="15"/></span><div><strong>Invoice dibuat</strong><small>Pesanan tersimpan</small></div></li><li class="{{ $isPaid ? 'is-complete' : ($isCancelled ? '' : 'is-current') }}"><span><x-icon :name="$isPaid ? 'check' : 'wallet'" size="15"/></span><div><strong>Pembayaran</strong><small>{{ $isPaid ? 'Terverifikasi' : ($isCancelled ? 'Dibatalkan' : 'Pilih & selesaikan') }}</small></div></li><li class="{{ $isPaid ? 'is-complete' : '' }}"><span><x-icon name="key" size="15"/></span><div><strong>{{ $order->gateway_mode==='sandbox'?'Hasil simulasi':'Akses lisensi' }}</strong><small>{{ $isPaid ? ($order->gateway_mode==='sandbox'?'Demo selesai':'Akses tersedia') : 'Setelah konfirmasi' }}</small></div></li></ol>
        </header>
        <div class="invoice-grid">
            <section class="invoice-summary" aria-label="Ringkasan invoice"><h2 class="invoice-section-title"><span><x-icon name="receipt" size="18"/></span> Ringkasan invoice</h2>
                <div class="receipt-reference"><span><x-icon name="hash" size="13"/> Nomor invoice</span><strong>{{ $order->number }}</strong><button type="button" class="icon-btn" data-copy="{{ $order->number }}" aria-label="Salin nomor invoice"><x-icon name="copy" size="16"/></button></div>
                <dl class="invoice-facts">
                    @foreach([
                        ['user','Pemilik',$order->member_name],
                        ['package','Paket',$order->product_name],
                        ['shopping','Jenis',$order->purpose==='renewal'?'Perpanjangan key yang sama':'Pembelian key baru'],
                        ['clock','Durasi',\App\Support\LicenseDuration::label($order->duration_seconds ?? $order->days*86400)],
                        ['wallet','Metode',$order->payment_provider ? $order->payment_provider.' · '.strtoupper($order->payment_method) : 'Belum dipilih'],
                        ['calendar','Dibuat',$order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB']
                    ] as [$icon,$label,$value])<div><dt><x-icon :name="$icon" size="15"/>{{ $label }}</dt><dd>{{ $value }}</dd></div>@endforeach
                    @if($order->target_key_hint)<div><dt><x-icon name="key" size="15"/>Key tujuan</dt><dd>{{ $order->target_key_hint }}</dd></div>@endif
                </dl>
                <div class="invoice-assurance"><x-icon name="shield" size="19"/><div><strong>Konfirmasi dari server</strong><p>Nominal mengikuti invoice. Status lunas hanya diproses setelah pembayaran terverifikasi.</p></div></div>
            </section>
            <section class="invoice-payment" aria-label="Pembayaran invoice"><h2 class="invoice-section-title"><span><x-icon name="wallet" size="18"/></span>{{ $isPaid?'Hasil pembayaran':'Pembayaran' }}</h2>
                <div class="invoice-total-card"><span><x-icon name="wallet" size="14"/> {{ $isPaid?'Total pembayaran':'Total invoice' }}</span><strong><small>Rp</small> {{ number_format($order->amount,0,',','.') }}</strong><div><x-icon :name="$isPaid ? 'check' : 'clock'" size="13"/><span>{{ $isPaid ? ($order->gateway_mode==='sandbox'?'Terkonfirmasi · simulasi':'Lunas · terverifikasi') : ($isCancelled?'Pesanan dibatalkan':'Belum dibayar') }}</span></div></div>
                @if($isPaid && $order->gateway_mode!=='sandbox' && $order->issuedLicense)
                    <div class="paid-key"><span class="eyebrow"><x-icon name="key" size="15"/> {{ $order->purpose==='renewal'?'KEY ANDA TETAP SAMA':'LICENSE KEY ANDA' }}</span><code>{{ $order->issuedLicense->key_value }}</code><button type="button" class="btn secondary compact" data-copy="{{ $order->issuedLicense->key_value }}"><x-icon name="copy" size="15"/> Salin key</button><a class="btn primary compact" href="{{ $order->purpose==='renewal'?route('member.dashboard'):route('member.login') }}"><x-icon name="device" size="15"/> Buka portal member <x-icon name="arrow" size="15"/></a></div>
                @elseif($isPaid)
                    <div class="invoice-notice"><x-icon name="check" size="20"/><div><strong>Simulasi pembayaran selesai</strong><p>Sandbox tidak menerbitkan lisensi atau mencatat pemasukan riil.</p></div></div>
                @elseif($order->status==='pending')
                    @if($order->gateway_payment_url && $order->gateway_status==='pending')
                        <div class="checkout-pay-area"><span class="eyebrow"><x-icon name="receipt" size="14"/> INVOICE SIAP</span><p>Lanjutkan ke halaman SumoPod. Gunakan invoice yang sama jika halaman pembayaran tertutup.</p><a class="btn primary full" href="{{ $order->gateway_payment_url }}" target="_blank" rel="noopener noreferrer"><x-icon name="wallet" size="16"/> Lanjutkan pembayaran QRIS <x-icon name="arrow" size="17"/></a><small>Kedaluwarsa {{ $order->gateway_expires_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? 'mengikuti invoice' }} WIB</small></div>
                    @elseif(!$order->gateway_status && $gatewayReady)
                        <form class="checkout-pay-area" method="post" action="{{ route('checkout.payment',$order) }}" data-once>@csrf<span class="eyebrow"><x-icon name="wallet" size="14"/> PILIH METODE PEMBAYARAN</span><label for="checkout-method"><x-icon name="wallet" size="14"/> Metode</label><select id="checkout-method" name="method"><option value="QRIS">QRIS · semua aplikasi pembayaran</option>@if($gatewayMode==='sandbox')<option value="QRIS_INSTANT">QRIS Instant · demo</option>@endif</select><button class="btn primary full" type="submit"><x-icon name="receipt" size="16"/> Buat invoice pembayaran <x-icon name="arrow" size="17"/></button><p class="field-help">Nominal mengikuti pesanan server. Klik ganda tidak membuat invoice baru.</p></form>
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
</x-public-layout>
