<x-layout title="Keuangan & laporan">
<div class="finance-studio" data-no-ui-icon><div class="fin-hero"><span class="fin-hero-symbol"><x-icon name="wallet" size="28"/></span><div><div class="eyebrow">FINANCE / CASH FLOW</div><h1>Keuangan & laporan.</h1><p>Pantau pemasukan, pengeluaran dan transaksi dalam satu ledger.</p></div><div class="heading-actions"><a class="btn secondary" href="{{ route('finance.export',request()->except(['page','per_page']) + ['from'=>$report['from'],'to'=>$report['to']]) }}"><x-icon name="download" size="17"/> Export Excel</a><button class="btn primary" data-open-dialog="record-transaction"><x-icon name="plus" size="17"/> Catat transaksi</button></div></div>

<div class="stats-grid finance-stats">@foreach([['income','Pemasukan bruto','wallet'],['fees','Biaya pembayaran','receipt'],['expenses','Pengeluaran','shopping'],['net','Arus kas bersih','activity']] as [$key,$label,$icon])<section class="stat-card fin-stat" data-fin-tone="{{ $key }}"><div class="stat-top"><span class="fin-symbol"><x-icon name="{{ $icon }}" size="20"/></span>{{ $label }}</div><strong class="money-value">Rp {{ number_format($report[$key],0,',','.') }}</strong><span class="field-help">{{ $key==='net'?'Bruto − biaya − pengeluaran':'Hanya transaksi posted' }}</span></section>@endforeach</div>
<div class="fin-filter"><div class="fin-section"><span class="fin-symbol"><x-icon name="filter" size="18"/></span><div><h2>Periode & filter laporan</h2><p>{{ $report['from'] }} — {{ $report['to'] }} · WIB</p></div></div><x-list-filters scope="finance"/></div>
<x-finance-chart :report="$report"/>
<section class="panel fin-ledger-panel fin-ledger-v3" aria-labelledby="fin-ledger-title">
    <header class="fin-ledger-header">
        <span class="fin-symbol"><x-icon name="receipt" size="20"/></span>
        <div><h2 id="fin-ledger-title">Ledger transaksi</h2><p>Riwayat pemasukan dan pengeluaran. Transaksi void tidak dihitung dalam laporan.</p></div>
        <span class="fin-ledger-count">{{ number_format($transactions->total(),0,',','.') }} transaksi</span>
    </header>
    <div class="table-scroll finance-ledger" tabindex="0" aria-label="Daftar transaksi keuangan, dapat digeser horizontal">
        <table class="fin-table">
            <colgroup><col class="fl-identity"><col class="fl-date"><col class="fl-status"><col class="fl-amount"><col class="fl-fee"><col class="fl-actions"></colgroup>
            <thead><tr>
                <th scope="col"><span><x-icon name="hash" size="14"/> Referensi / deskripsi</span></th>
                <th scope="col"><span><x-icon name="calendar" size="14"/> Waktu bayar</span></th>
                <th scope="col"><span><x-icon name="shield" size="14"/> Status / sumber</span></th>
                <th scope="col" class="numeric"><span><x-icon name="wallet" size="14"/> Nominal</span></th>
                <th scope="col" class="numeric"><span>Biaya</span></th>
                <th scope="col" class="align-right"><span>Tindakan</span></th>
            </tr></thead>
            <tbody>
            @forelse($transactions as $row)
                <tr class="{{ $row->status === 'void' ? 'fin-void-row' : '' }}">
                    <td>
                        <div class="fin-row-identity">
                            <span class="fin-symbol {{ $row->direction }}"><x-icon name="{{ $row->direction === 'income' ? 'wallet' : 'shopping' }}" size="19"/></span>
                            <div class="fl-copy">
                                <button type="button" class="record-link" data-record-url="{{ route('records.show',['transaction',$row->id]) }}">{{ $row->reference }} <x-icon name="arrow" size="13"/></button>
                                <span class="fl-description">{{ $row->description }}</span>
                            </div>
                        </div>
                    </td>
                    <td><div class="fl-stack"><time datetime="{{ $row->paid_at->toIso8601String() }}" class="fin-date">{{ $row->paid_at->timezone('Asia/Jakarta')->format('d M Y') }}</time><span class="fl-secondary">{{ $row->paid_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</span></div></td>
                    <td><div class="fl-stack"><span class="fin-status {{ $row->status }}"><i></i>{{ $row->status === 'posted' ? 'Tercatat' : 'Void' }}</span><span class="fl-secondary">{{ $row->source === 'gateway' ? 'Gateway' : 'Manual' }} · {{ $row->direction === 'income' ? 'Masuk' : 'Keluar' }}</span></div></td>
                    <td class="numeric"><div class="fl-stack fl-money"><strong class="fin-amount {{ $row->direction }}">{{ $row->direction === 'income' ? '+' : '−' }}Rp {{ number_format($row->amount,0,',','.') }}</strong>@if($row->status === 'void')<span class="fl-secondary">Tidak dihitung</span>@endif</div></td>
                    <td class="numeric"><span class="fl-fee-value">Rp {{ number_format($row->fee,0,',','.') }}</span></td>
                    <td><div class="fin-row-actions">
                        <button type="button" class="icon-btn" title="Detail transaksi" aria-label="Detail transaksi {{ $row->reference }}" data-record-url="{{ route('records.show',['transaction',$row->id]) }}"><x-icon name="eye" size="17"/></button>
                        @if(!$row->order_id && $row->status==='posted')
                        <form method="post" action="{{ route('finance.void',$row) }}" data-confirm-title="Void transaksi ini?" data-confirm-action="Ya, void transaksi" data-confirm-tone="danger" data-confirm-icon="ban" data-confirm="Transaksi {{ $row->reference }} akan dikeluarkan dari total. Riwayat tetap tersimpan.">@csrf<button type="submit" class="icon-btn danger" title="Void transaksi" aria-label="Void transaksi {{ $row->reference }}"><x-icon name="ban" size="17"/></button></form>
                        @else
                        <span class="fl-action-placeholder" aria-hidden="true"></span>
                        @endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><span class="fin-symbol"><x-icon name="wallet" size="24"/></span><strong>Belum ada transaksi.</strong><p>Pembayaran terverifikasi dan penerbitan lisensi berbayar masuk ke ledger ini.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$transactions"/>
</section></div>
<dialog id="record-transaction" class="modal lk-create fin-editor" aria-labelledby="fin-editor-title" data-no-ui-icon><form method="post" action="{{ route('finance.store') }}" data-fin-editor>@csrf<header class="lk-modal-header"><span class="lk-modal-symbol"><x-icon name="wallet" size="23"/></span><div><span class="lk-kicker">FINANCE / MANUAL ENTRY</span><h2 id="fin-editor-title">Catat transaksi</h2><p>Tambahkan transaksi yang sudah diterima atau dibayar.</p></div><button class="icon-btn" type="button" data-close-dialog aria-label="Tutup"><x-icon name="close"/></button></header><div class="lk-modal-content"><section class="lk-form-section"><div class="lk-section-title"><span><x-icon name="wallet" size="16"/></span><div><h3>Nominal & jenis transaksi</h3><p>Gunakan nilai aktual dalam Rupiah.</p></div></div><label for="money-direction">Jenis transaksi</label><select id="money-direction" name="direction"><option value="income">Pemasukan</option><option value="expense">Pengeluaran</option></select><label for="money-amount">Nominal bruto (Rupiah)</label><input id="money-amount" type="number" name="amount" min="1" max="100000000000" step="1" required><label for="money-fee">Biaya pembayaran (Rupiah)</label><input id="money-fee" type="number" name="fee" min="0" step="1" value="0" required><p class="field-help">Untuk pengeluaran isi biaya 0; nominal pengeluaran sudah mencakup total yang dibayar.</p></section><section class="lk-form-section"><div class="lk-section-title"><span><x-icon name="receipt" size="16"/></span><div><h3>Informasi pembayaran</h3><p>Waktu, deskripsi dan referensi untuk audit.</p></div></div><label for="money-date">Waktu pembayaran (WIB)</label><input id="money-date" type="datetime-local" name="paid_at" value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" required><label for="money-description">Deskripsi</label><input id="money-description" name="description" maxlength="255" required><label for="money-reference">Referensi pembayaran unik (opsional)</label><input id="money-reference" name="external_reference" maxlength="100"></section><div class="fin-editor-preview"><span class="fin-symbol"><x-icon name="activity" size="20"/></span><div><small>Dampak arus kas bersih</small><strong data-fin-net>Rp 0</strong></div></div><div class="hint-box"><x-icon name="shield" size="17"/><span>Hanya catat pembayaran yang sudah benar-benar diterima/dibayar. Pesanan jangan dicatat dua kali di sini.</span></div></div><footer class="modal-actions lk-modal-footer"><span><x-icon name="shield" size="15"/> Ledger audit</span><div><button class="btn secondary" type="button" data-close-dialog>Batal</button><button class="btn primary"><x-icon name="check" size="17"/> Simpan transaksi</button></div></footer></form></dialog></x-layout>
