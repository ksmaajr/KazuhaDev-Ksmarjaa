const glyphs={"user":"<circle cx=\"12\" cy=\"8\" r=\"4\"/><path d=\"M4 21v-2a8 8 0 0 1 16 0v2\"/>","users":"<path d=\"M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M16 3a4 4 0 0 1 0 8\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/>","package":"<path d=\"m12 3 9 5v9l-9 5-9-5V8l9-5Zm0 10 9-5M12 13 3 8m9 5v9M7.5 5.5l9 5\"/>","receipt":"<path d=\"M6 3h12v18l-3-2-3 2-3-2-3 2V3Z\"/><path d=\"M9 7h6M9 11h6M9 15h3\"/>","calendar":"<rect x=\"3\" y=\"5\" width=\"18\" height=\"16\" rx=\"2\"/><path d=\"M16 3v4M8 3v4M3 11h18m-13 4h2m4 0h2\"/>","hash":"<path d=\"M5 9h14M4 15h14M11 3 7 21M17 3l-4 18\"/>","info":"<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M12 11v6m0-10h.01\"/>","sort":"<path d=\"M8 3v18m-4-4 4 4 4-4m4-14v18m-4-14 4-4 4 4\"/>","filter":"<path d=\"M4 5h16M7 12h10m-7 7h4\"/>","download":"<path d=\"M12 3v12m-5-5 5 5 5-5M4 15v6h16v-6\"/>","image":"<rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"3\"/><circle cx=\"8\" cy=\"8\" r=\"2\"/><path d=\"m21 15-5-5L5 21\"/>","gamepad":"<path d=\"M7 6h10c3 0 5 10 4 12s-5-2-6-2H9c-1 0-5 4-6 2S4 6 7 6Z\"/><path d=\"M6 10v4m-2-2h4m8-1h.1M18 14h.1\"/>","bell":"<path d=\"M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4\"/>","wallet":"<path d=\"M20 8V4H4v16h16v-4M4 8h18v8h-7V8\"/><path d=\"M18 12h.1\"/>","shopping":"<path d=\"M3 3h2l3 12h11l3-9H6M9 21h.1M18 21h.1\"/>","sparkles":"<path d=\"m12 3 2.8 6.2L21 12l-6.2 2.8L12 21l-2.8-6.2L3 12l6.2-2.8z\"/><path d=\"M20 2v4m-2-2h4\"/>","sun":"<circle cx=\"12\" cy=\"12\" r=\"4\"/><path d=\"M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.4 1.4m11.2 11.2L19 19M5 19l1.4-1.4M17.6 6.4 19 5\"/>","moon":"<path d=\"M21 13.2A9 9 0 0 1 10.8 3 9 9 0 1 0 21 13.2Z\"/>","edit":"<path d=\"m16 3 5 5-12 12-6 1 1-6Z\"/><path d=\"m14 5 5 5\"/>","message":"<path d=\"M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z\"/>","trash":"<path d=\"M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7\"/>","grid":"<rect x=\"3\" y=\"3\" width=\"7\" height=\"7\" rx=\"1.5\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\" rx=\"1.5\"/><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\" rx=\"1.5\"/><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\" rx=\"1.5\"/>","key":"<circle cx=\"8\" cy=\"8\" r=\"5\"/><path d=\"m12 12 9 9m-5-5 3-3m-6 0 3-3\"/>","activity":"<path d=\"M3 12h4l3-8 4 16 3-8h4\"/>","code":"<path d=\"m8 7-5 5 5 5m8-10 5 5-5 5M14 4l-4 16\"/>","settings":"<path d=\"m9 3-1 3-3 1 1 3-2 2 2 2-1 3 3 1 1 3h6l1-3 3-1-1-3 2-2-2-2 1-3-3-1-1-3z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/>","arrow":"<path d=\"M5 12h14m-6-6 6 6-6 6\"/>","plus":"<path d=\"M12 5v14M5 12h14\"/>","search":"<circle cx=\"10.5\" cy=\"10.5\" r=\"6.5\"/><path d=\"m16 16 5 5\"/>","copy":"<rect x=\"8\" y=\"8\" width=\"12\" height=\"13\" rx=\"2\"/><path d=\"M16 8V3H3v13h5\"/>","shield":"<path d=\"m12 3 8 3v6c0 5-8 9-8 9S4 17 4 12V6z\"/><path d=\"m8 12 3 3 5-6\"/>","clock":"<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M12 7v5l3 2\"/>","device":"<rect x=\"3\" y=\"4\" width=\"18\" height=\"13\" rx=\"2\"/><path d=\"M8 21h8m-4-4v4\"/>","reset":"<path d=\"M3 10a9 9 0 1 1 2 8M3 4v6h6\"/>","ban":"<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"m6 6 12 12\"/>","logout":"<path d=\"M9 4H4v16h5m-1-8h13m-5-5 5 5-5 5\"/>","lock":"<rect x=\"5\" y=\"10\" width=\"14\" height=\"11\" rx=\"2\"/><path d=\"M8 10V7a4 4 0 0 1 8 0v3m-4 5v2\"/>","check":"<path d=\"m5 12 4 4L19 6\"/>","menu":"<path d=\"M4 6h16M4 12h16M4 18h16\"/>","close":"<path d=\"m6 6 12 12M6 18 18 6\"/>","globe":"<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M3 12h18M12 3a17 17 0 0 1 0 18 17 17 0 0 1 0-18Z\"/>","chevron":"<path d=\"m9 5 7 7-7 7\"/>","eye":"<path d=\"M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/>"};

export function uiIcon(name='info'){
 const span=document.createElement('span');span.className='ui-glyph ui-glyph-'+name;span.setAttribute('aria-hidden','true');
 const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');for(const [key,value] of Object.entries({viewBox:'0 0 24 24',fill:'none',stroke:'currentColor','stroke-width':'1.7','stroke-linecap':'round','stroke-linejoin':'round',width:'16',height:'16'}))svg.setAttribute(key,value);
 svg.innerHTML=glyphs[name]||glyphs.info;span.append(svg);return span;
}
function iconFor(label){
 const text=label.toLowerCase();
 if(/hapus|delete|trash|bersihkan/.test(text))return 'trash';
 if(/batalkan|dibatalkan|cabut|blokir/.test(text))return 'ban';
 if(/salin|copy/.test(text))return 'copy';
 if(/edit|ubah|sunting/.test(text))return 'edit';
 if(/tambah|buat admin|terbitkan/.test(text))return 'plus';
 if(/simpan|konfirmasi|verifikasi|terapkan|lunas|berhasil/.test(text))return 'check';
 if(/reset|periksa|refresh|muat|coba lagi/.test(text))return 'reset';
 if(/cari|pencarian|search/.test(text))return 'search';
 if(/unduh|download|export|ekspor/.test(text))return 'download';
 if(/tanggal|waktu|dibuat|diperbarui|sejak|hingga|terakhir/.test(text))return 'calendar';
 if(/durasi|sisa|berlaku|perpanjang|kedaluwarsa|heartbeat|interval/.test(text))return 'clock';
 if(/harga|nominal|biaya|bruto|neto|pemasukan|pengeluaran|uang|saldo|total pembayaran|metode|payment|pembayaran/.test(text))return 'wallet';
 if(/invoice|pesanan|order|referensi/.test(text))return 'receipt';
 if(/paket|produk|katalog|package/.test(text))return 'package';
 if(/game|library|rilis/.test(text))return 'gamepad';
 if(/hwid|perangkat|device/.test(text))return 'device';
 if(/administrator|admin|role|hak akses|keamanan|status/.test(text))return 'shield';
 if(/api|kode|endpoint|webhook|integrasi/.test(text))return 'code';
 if(/password|rahasia|kredensial/.test(text))return 'lock';
 if(/member|pembeli|pemilik|pelaku|nama|pengirim|email|kontak/.test(text))return 'user';
 if(/key|lisensi|license/.test(text))return 'key';
 if(/pengaturan|konfigurasi/.test(text))return 'settings';
 if(/aktivitas|riwayat|transaksi|grafik|arus/.test(text))return 'activity';
 if(/urut/.test(text))return 'sort';
 if(/filter/.test(text))return 'filter';
 if(/logo|banner|gambar/.test(text))return 'image';
 if(/dashboard|overview|ringkasan/.test(text))return 'grid';
 if(/whatsapp|hubungi|pesan/.test(text))return 'message';
 if(/kembali|lanjut|buka|lihat|detail/.test(text))return 'arrow';
 if(/masuk|keluar/.test(text))return 'logout';
 if(/tutup|batal$/.test(text))return 'close';
 return 'info';
}
const targets='main h1,main h2,main h3,.modal-heading h2,.detail-group-heading,.detail-facts>div>span,.detail-table th,.detail-tab,label,thead th,.btn,.detail-back';
export function decorateUiIcons(root=document){
 const items=[...(root instanceof Element&&root.matches(targets)?[root]:[]),...root.querySelectorAll(targets)];
 for(const node of items){
  if((node.classList.contains('has-ui-icon')&&node.querySelector('.ui-glyph'))||node.querySelector('svg,input,select,textarea')||node.closest('.invoice-experience,.game-picker,.theme-toggle,.switch-field,[data-no-ui-icon]')||node.classList.contains('icon-btn'))continue;
  const label=node.textContent.trim();if(!label||label.length>160||/^[\d\s.,/—–:Rp]+$/.test(label))continue;
  const name=iconFor(label),content=document.createElement('span');content.className='ui-icon-label';while(node.firstChild)content.append(node.firstChild);
  node.classList.add('has-ui-icon');node.dataset.uiIcon=name;node.append(uiIcon(name),content);
 }
}
decorateUiIcons();
document.addEventListener('live:updated',()=>decorateUiIcons());
let frame=null;const pendingRoots=new Set();
new MutationObserver(records=>{
 for(const record of records){
  if(record.target instanceof Element&&record.target.matches(targets)&&!record.target.querySelector('.ui-glyph'))pendingRoots.add(record.target);
  for(const node of record.addedNodes)if(node instanceof HTMLElement&&(node.matches(targets)||node.querySelector(targets)))pendingRoots.add(node);
 }
 if(!pendingRoots.size||frame!==null)return;
 frame=requestAnimationFrame(()=>{frame=null;const roots=[...pendingRoots];pendingRoots.clear();for(const root of roots)if(root.isConnected)decorateUiIcons(root);});
}).observe(document.body,{childList:true,subtree:true});
