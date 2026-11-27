@props(['report'])
@php
$daily=$report['daily'];$max=max(1,$daily->max(fn($d)=>max($d['income'],$d['expense'])));$count=$daily->count();$step=max(1,(int)ceil($count/6));
$points=fn($key)=>$daily->map(fn($d,$i)=>round(56+$i*(620/max(1,$count-1)),2).','.round(188-$d[$key]/$max*142,2))->implode(' ');
@endphp
<section class="panel finance-chart"><div class="panel-heading"><div><div class="eyebrow">CASH FLOW / IDR</div><h2>Arus keuangan masuk</h2><p>{{ $report['from'] }} — {{ $report['to'] }} · waktu pembayaran WIB</p></div><span class="subtle-chip">Ledger tersimpan</span></div>
<div class="finance-chart-summary"><div><strong>Rp {{ number_format($report['income'],0,',','.') }}</strong><span>Pemasukan bruto terkonfirmasi</span></div><div class="chart-controls"><button type="button" class="chart-series-toggle" data-chart-toggle="income" aria-pressed="true"><i></i> Pemasukan</button><button type="button" class="chart-series-toggle expense" data-chart-toggle="expense" aria-pressed="true"><i></i> Pengeluaran</button></div></div>
<div class="finance-plot"><svg viewBox="0 0 720 230" role="img" aria-label="Grafik pemasukan dan pengeluaran IDR, hanya transaksi terkonfirmasi">
@foreach(range(0,3) as $tick)<line x1="55" x2="685" y1="{{ 46+$tick*47.33 }}" y2="{{ 46+$tick*47.33 }}" stroke="currentColor" stroke-opacity=".12" stroke-dasharray="4 6"/><text x="46" y="{{ 50+$tick*47.33 }}" text-anchor="end">{{ number_format($max*(3-$tick)/3,0,',','.') }}</text>@endforeach
<polyline data-chart-series="income" points="{{ $points('income') }}" fill="none" stroke="var(--mint)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><polyline data-chart-series="expense" points="{{ $points('expense') }}" fill="none" stroke="var(--accent-purple)" stroke-width="2" stroke-dasharray="5 5"/>
@foreach($daily as $i=>$day)@if($i%$step===0 || $i===$count-1)<text x="{{ 56+$i*(620/max(1,$count-1)) }}" y="220" text-anchor="middle">{{ $day['label'] }}</text>@endif
<circle data-chart-series="income" class="finance-point" tabindex="0" cx="{{ 56+$i*(620/max(1,$count-1)) }}" cy="{{ 188-$day['income']/$max*142 }}" r="4" fill="var(--mint)"><title>{{ $day['date'] }} · Pemasukan Rp {{ number_format($day['income'],0,',','.') }} · Pengeluaran Rp {{ number_format($day['expense'],0,',','.') }}</title></circle>@endforeach
</svg></div>
@if($report['income']===0 && $report['expenses']===0)<p class="chart-empty-note">Belum ada transaksi terkonfirmasi pada periode ini. Grafik tidak menggunakan data contoh.</p>@endif
<div class="finance-chart-note"><x-icon name="shield" size="14"/> Pemasukan gateway: Rp {{ number_format($report['gateway'],0,',','.') }}. Hanya pembayaran terverifikasi; simulasi sandbox tidak termasuk pendapatan.</div></section>
