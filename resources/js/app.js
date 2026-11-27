import {loadView,getViewUrl} from './navigation';
import './sidebar';
import './ui-icons';
import './currency';
import './controls';
import './details';
import { notify as toast, confirmAction } from './feedback';
import './live';
const $=selector=>document.querySelector(selector);
const approvedForms=new WeakSet(),pendingForms=new WeakSet();
document.addEventListener('click',async event=>{
 const target=event.target.closest('button,a');if(!target)return;
 if(target.matches('[data-copy]')){
  const value=target.dataset.copy;let copied=false;
  try{await navigator.clipboard.writeText(value);copied=true;}catch{
   const input=document.createElement('textarea');input.value=value;input.style.cssText='position:fixed;opacity:0';document.body.append(input);input.select();
   try{copied=document.execCommand('copy');}catch{}input.remove();
  }
  if(copied)toast('Berhasil disalin ke clipboard.');else await confirmAction({title:'Salin teks secara manual',message:'Tekan Ctrl+C atau tahan teks lalu pilih Salin.',action:'Selesai',tone:'info',icon:'copy',copyValue:value});
 }
 if(target.matches('[data-open-dialog]'))document.getElementById(target.dataset.openDialog)?.showModal();
 if(target.matches('[data-close-dialog]'))target.closest('dialog')?.close();
 if(target.matches('[data-chart-toggle]')){const chart=target.closest('.finance-chart'),on=target.getAttribute('aria-pressed')!=='true';target.setAttribute('aria-pressed',String(on));chart.querySelectorAll('[data-chart-series="'+target.dataset.chartToggle+'"]').forEach(node=>node.style.display=on?'':'none');}
 if(target.matches('.btn')&&!target.disabled&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const box=target.getBoundingClientRect(),size=Math.max(box.width,box.height),ripple=document.createElement('span');ripple.className='ripple';ripple.style.width=ripple.style.height=size+'px';ripple.style.left=(event.clientX-box.left-size/2)+'px';ripple.style.top=(event.clientY-box.top-size/2)+'px';target.append(ripple);setTimeout(()=>ripple.remove(),600);}
});
document.addEventListener('click',event=>{
 const dialog=event.target;
 if(!dialog.matches('dialog:not(#universal-confirm)'))return;
 const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)dialog.close();
});
document.addEventListener('submit',async event=>{
 const form=event.target;if(!form.matches('form[data-confirm]'))return;
 if(approvedForms.has(form))return;event.preventDefault();if(pendingForms.has(form)||form.dataset.submitting)return;
 pendingForms.add(form);const submitter=event.submitter;
 const accepted=await confirmAction({title:form.dataset.confirmTitle||'Lanjutkan tindakan ini?',message:form.dataset.confirm,action:form.dataset.confirmAction||'Ya, lanjutkan',tone:form.dataset.confirmTone||'warning',icon:form.dataset.confirmIcon||'warning',hint:form.dataset.confirmHint||'Periksa kembali sebelum melanjutkan.'});
 pendingForms.delete(form);if(!accepted||!form.isConnected)return;approvedForms.add(form);
 try{form.dataset.ajaxConfirmed='true';form.requestSubmit(submitter?.isConnected?submitter:undefined);}finally{approvedForms.delete(form);delete form.dataset.ajaxConfirmed;}
});
document.addEventListener('submit',event=>{
 const form=event.target;if(event.defaultPrevented||!form.matches('form[data-confirm]'))return;
 if(form.dataset.submitting){event.preventDefault();return;}form.dataset.submitting='true';
 setTimeout(()=>{if(event.submitter){event.submitter.disabled=true;event.submitter.classList.add('is-submitting');event.submitter.setAttribute('aria-busy','true');}},0);
});
window.addEventListener('pageshow',()=>{document.querySelectorAll('form[data-submitting]').forEach(form=>{delete form.dataset.submitting;form.querySelectorAll('.is-submitting').forEach(b=>{b.disabled=false;b.classList.remove('is-submitting');b.removeAttribute('aria-busy');});});});
document.addEventListener('change',event=>{
 const input=event.target;if(!input.matches('[data-image-preview]'))return;const file=input.files?.[0],box=input.closest('.artwork-upload');if(!file)return;
 if(!['image/jpeg','image/png','image/webp'].includes(file.type)){toast('Gunakan gambar JPG, PNG atau WebP.',{type:'error'});input.value='';return;}
 let preview=box.querySelector('.artwork-preview');if(!preview){preview=document.createElement('img');preview.className='artwork-preview';preview.alt='Preview gambar baru';input.before(preview);}
 if(preview.dataset.objectUrl)URL.revokeObjectURL(preview.dataset.objectUrl);const url=URL.createObjectURL(file);preview.src=url;preview.dataset.objectUrl=url;
});
if($('#reopen-create'))$('#create-key')?.showModal();
$('#password-toggle')?.addEventListener('click', () => {
    const input = $('#password'); const showing = input.type === 'password';
    input.type = showing ? 'text' : 'password';
    $('#password-toggle').setAttribute('aria-label', showing ? 'Sembunyikan password' : 'Tampilkan password');
});
const clock = $('#server-clock');
let serverStart = clock ? Date.parse(clock.dataset.time) : Date.now();
let localStart = Date.now();
function updateTimers() {
    const now = serverStart + Date.now() - localStart;
    if (clock) clock.textContent = new Intl.DateTimeFormat('id-ID', {timeZone:'Asia/Jakarta',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(now).replaceAll('.', ':');
    document.querySelectorAll('[data-member-expires]').forEach(el => {
        let total = Math.max(0, Math.floor((Date.parse(el.dataset.memberExpires) - now) / 1000));
        const parts = [Math.floor(total/86400), Math.floor(total%86400/3600), Math.floor(total%3600/60), total%60];
        el.querySelectorAll('[data-countdown-part]').forEach((node,i) => {node.textContent = String(parts[i]).padStart(2,'0');});
    });
    document.querySelectorAll('[data-expires]').forEach(el => {
        let seconds = Math.max(0, Math.floor((Date.parse(el.dataset.expires) - now)/1000));
        if (seconds <= 0) { el.textContent = 'Masa berlaku habis'; return; }
        const days = Math.floor(seconds / 86400); seconds %= 86400;
        const hours = Math.floor(seconds / 3600); seconds %= 3600;
        const minutes = Math.floor(seconds / 60); seconds %= 60;
        el.textContent = days + ' hari ' + hours + 'j ' + minutes + 'm ' + seconds + 'd';
    });
}
updateTimers(); setInterval(updateTimers, 1000);
document.addEventListener('submit', async (event) => {
    if(!event.target.matches('#api-tester'))return;
    event.preventDefault();
    const form = event.target, button = $('#send-request'), response = $('#api-response'), status = $('#response-status');
    const method = $('#test-method').value, key = $('#test-key').value.trim(), hwid = $('#test-hwid').value.trim();
    if(button.dataset.pending)return;button.dataset.pending='true';
    if(method==='POST'&&!await confirmAction({title:'Kirim aktivasi perangkat?',message:'Request ini dapat mengikat HWID dan memulai durasi key pada aktivasi pertama. Gunakan key uji untuk playground.',action:'Kirim request',icon:'code',tone:'warning',hint:'Pastikan key, HWID dan game sesuai.'})){delete button.dataset.pending;return;}
    button.disabled = true; status.textContent = 'Mengirim…';
    const controller = new AbortController(); const timeout = setTimeout(() => controller.abort(), 10000);
    try {
        const options = { method, headers: {Accept: 'application/json'}, signal: controller.signal, cache: 'no-store' };
        if (method === 'POST') { options.headers['Content-Type'] = 'application/json'; options.body = JSON.stringify({key,hwid,game_code: $('#test-game').value}); }
        else { options.headers.Authorization = 'Bearer ' + key; options.headers['X-HWID'] = hwid; options.headers['X-Game'] = $('#test-game').value; }
        const result = await fetch(method === 'POST' ? form.dataset.postUrl : form.dataset.getUrl, options);
        const json = await result.json();
        status.textContent = 'HTTP ' + result.status;
        response.textContent = JSON.stringify(json, null, 2);
        toast(json.message||'Respons diterima.',{type:result.ok&&json.success?'success':'error',title:'API · HTTP '+result.status});
    } catch(error) {
        status.textContent = 'Request gagal';
        toast(error.name==='AbortError'?'Server tidak merespons dalam 10 detik.':'Tidak dapat menghubungi server API.',{type:'error',title:'Request gagal'});
        response.textContent = JSON.stringify({success: false, message: error.name === 'AbortError' ? 'Request timeout (10 detik). Tolak akses.' : 'Tidak dapat menghubungi API. Tolak akses.'}, null, 2);
    } finally { clearTimeout(timeout); button.disabled = false; delete button.dataset.pending; }
});

const memberPortal = document.querySelector('[data-member-status-url]');
if (memberPortal) {
    const formatDate = value => value ? new Intl.DateTimeFormat('id-ID',{timeZone:'Asia/Jakarta',dateStyle:'medium',timeStyle:'medium'}).format(new Date(value)) + ' WIB' : 'Belum aktif';
    let syncing = false;
    const syncMember = async () => {
        if (syncing || document.hidden) return;
        syncing = true;
        const controller = new AbortController();
        const timeout = setTimeout(()=>controller.abort(),10000);
        try {
            const response = await fetch(memberPortal.dataset.memberStatusUrl,{headers:{Accept:'application/json'},cache:'no-store',signal:controller.signal});
            if(response.status===401 || response.redirected){location.assign(memberPortal.dataset.memberLoginUrl);return;}
            if(!response.ok) return;
            const json=await response.json(); if(json.success!==true) return;
            const data=json.data;
            serverStart=Date.parse(data.server_time);localStart=Date.now();
            const timer=$('.member-countdown');
            if(data.expires_at){timer.dataset.memberExpires=data.expires_at;}
            else {delete timer.dataset.memberExpires;timer.querySelectorAll('[data-countdown-part]').forEach((el,i)=>{el.textContent=String([Math.floor(data.duration_seconds/86400),Math.floor(data.duration_seconds%86400/3600),Math.floor(data.duration_seconds%3600/60),data.duration_seconds%60][i]).padStart(2,'0');});}
            $('#member-hwid').textContent=data.hwid || 'Belum ada perangkat terikat';
            const copy=$('#member-hwid-copy'); if(copy){copy.hidden=!data.hwid;copy.dataset.copy=data.hwid || '';}
            $('#member-reset-count').textContent=data.reset_count+' kali';
            $('#member-activated').textContent=formatDate(data.activated_at);
            $('#member-last-seen').textContent=data.last_seen_at ? formatDate(data.last_seen_at) : 'Belum ada';
            $('#member-expiry-label').textContent=data.expires_at ? 'Expired: '+formatDate(data.expires_at) : '1 hari = 24 jam';
            $('#member-status').className='badge '+data.status;
            $('#member-status').textContent={active:'Lisensi aktif',unbound:'Menunggu perangkat',expired:'Kedaluwarsa'}[data.status] || data.status;
            $('#member-reset-button').disabled=data.status!=='active';
            $('#member-timing-status').textContent=!data.activated_at?'Belum diaktifkan · durasi belum berjalan':(data.status==='expired'?'Masa berlaku sudah habis':'Mengikuti waktu server');
            updateTimers();
        } catch {} finally {clearTimeout(timeout);syncing=false;}
    };
    setInterval(syncMember,10000);
    document.addEventListener('visibilitychange',()=>{if(!document.hidden) syncMember();});
}

function applyTheme(theme) {
    const selected = theme === 'light' ? 'light' : 'dark';
    document.documentElement.dataset.theme = selected;
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        const destination = selected === 'dark' ? 'light' : 'dark';
        button.setAttribute('aria-label','Gunakan ' + destination + ' mode');
        button.setAttribute('title','Gunakan ' + destination + ' mode');
        button.setAttribute('aria-pressed',String(selected === 'light'));
        const label=button.querySelector('[data-theme-label]');
        if(label) label.textContent=destination==='light'?'Light mode':'Dark mode';
    });
}
applyTheme(document.documentElement.dataset.theme || 'dark');
document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click',() => {
    const next=document.documentElement.dataset.theme==='dark'?'light':'dark';
    try {localStorage.setItem('kazuhadev-theme',next);} catch {}
    applyTheme(next);
    document.documentElement.classList.remove('theme-flash');
    void document.documentElement.offsetWidth;
    document.documentElement.classList.add('theme-flash');
    setTimeout(()=>document.documentElement.classList.remove('theme-flash'),500);
}));
window.addEventListener('storage',event=>{if(event.key==='kazuhadev-theme') applyTheme(event.newValue || 'dark');});

// Prevent repeated checkout submission while the server owns the request.
document.addEventListener('submit',event=>{const form=event.target;if(!form.matches('form[data-once]')||event.defaultPrevented)return;if(form.dataset.pending){event.preventDefault();return;}form.dataset.pending='true';const button=event.submitter;if(button)setTimeout(()=>{button.disabled=true;button.setAttribute('aria-busy','true');button.classList.add('is-submitting');},0);});
window.addEventListener('pageshow',()=>document.querySelectorAll('form[data-once]').forEach(form=>{delete form.dataset.pending;form.querySelectorAll('.is-submitting').forEach(button=>{button.disabled=false;button.removeAttribute('aria-busy');button.classList.remove('is-submitting');});}));
let paymentChecking=false;
async function checkPayment(){const watch=document.querySelector('[data-payment-watch]');if(!watch||watch.dataset.orderStatus!=='pending'||paymentChecking||document.hidden)return;paymentChecking=true;const label=watch.querySelector('[data-payment-poll-label]');try{const response=await fetch(watch.dataset.paymentWatch,{headers:{Accept:'application/json'},cache:'no-store',signal:AbortSignal.timeout(8000)});if(!response.ok)throw Error();const data=await response.json();if(!watch.isConnected)return;if(data.status!==watch.dataset.orderStatus||data.gateway_status!==(watch.dataset.gatewayStatus||null)){await loadView(getViewUrl(),{historyMode:'none'});return;}if(label)label.textContent='Status diperiksa · '+new Intl.DateTimeFormat('id-ID',{timeZone:'Asia/Jakarta',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date());}catch{if(label)label.textContent='Koneksi terputus · pesanan tersimpan, jangan bayar ulang';}finally{paymentChecking=false;}}
document.addEventListener('click',event=>{if(event.target.closest('[data-payment-refresh]'))checkPayment();});setInterval(checkPayment,10000);document.addEventListener('visibilitychange',()=>{if(!document.hidden)checkPayment();});
