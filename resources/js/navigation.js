import {notify} from './feedback';
let currentUrl=location.href,controller=null,sequence=0,debounce=null;
const adminPaths=new Set(['/dashboard','/licenses','/orders','/products','/finance','/activity','/games','/admins','/settings','/payment-settings','/api-console']);
const root=()=>document.querySelector('[data-live-content]');
export const getViewUrl=()=>currentUrl;
function cleanHistory(url,mode='replace'){const target=new URL(url,location.origin);const state={...history.state,kzViewUrl:target.href};history[mode==='push'?'pushState':'replaceState'](state,'',target.pathname+target.hash);}
function busy(value){document.documentElement.dataset.viewBusy=String(value);root()?.setAttribute('aria-busy',String(value));document.body.classList.toggle('view-loading',value);}
function flash(html){html.querySelectorAll('[data-flash-feedback]').forEach(el=>{notify(el.querySelector('[data-feedback-message]')?.textContent?.trim()||el.textContent.trim(),{type:el.dataset.flashFeedback,title:el.dataset.feedbackTitle});});}
function render(html,url,{historyMode='replace',keepFocus=false,scroll=false}={}){
 const incoming=html.querySelector('[data-live-content]'),current=root();if(!incoming||!current)throw Error('Halaman belum dapat diperbarui. Muat ulang hanya jika sesi Anda sudah berakhir.');
 const focus=document.activeElement;const saved=keepFocus&&focus?.id?{id:focus.id,start:focus.selectionStart,end:focus.selectionEnd}:null;
 current.querySelectorAll('dialog[open]').forEach(dialog=>dialog.close());current.replaceChildren(...incoming.childNodes);currentUrl=url;document.title=html.title||document.title;
 const breadcrumbs=document.querySelector('.breadcrumbs'),nextCrumb=html.querySelector('.breadcrumbs');if(breadcrumbs&&nextCrumb)breadcrumbs.replaceChildren(...nextCrumb.childNodes);
 document.querySelectorAll('.sidebar .nav-item').forEach(link=>{const matching=Array.from(html.querySelectorAll('.sidebar .nav-item')).find(next=>next.href===link.href);if(matching)link.className=matching.className;});
 if(historyMode!=='none')cleanHistory(url,historyMode);document.dispatchEvent(new Event('live:updated'));flash(html);
 if(saved){const next=document.getElementById(saved.id);if(next){next.focus({preventScroll:true});try{next.setSelectionRange(saved.start,saved.end);}catch{}}}
 if(scroll)window.scrollTo({top:0,behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});
 document.querySelector('#sidebar')?.classList.remove('open');document.querySelector('#menu-toggle')?.setAttribute('aria-expanded','false');
}
async function readResponse(response){if(response.status===401||response.status===419){throw Error('Sesi Anda telah berakhir. Silakan masuk kembali.');}if(!response.ok){let message='Permintaan belum berhasil. Data dan isian Anda tetap aman.';try{const data=await response.json();message=data.errors?Object.values(data.errors).flat().join('\n'):data.message||message;}catch{}throw Error(message);}return new DOMParser().parseFromString(await response.text(),'text/html');}
export async function loadView(url,options={}){
 const requested=new URL(url,location.origin);if(requested.origin!==location.origin)return;controller?.abort();controller=new AbortController();const activeController=controller,ticket=++sequence;busy(true);const timeout=setTimeout(()=>activeController.abort(),15000);
 try{const response=await fetch(requested.href,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},cache:'no-store',signal:controller.signal});const html=await readResponse(response);if(ticket!==sequence)return;render(html,response.url||requested.href,options);}catch(error){if(ticket===sequence)notify(error.name==='AbortError'?'Koneksi terlalu lama. Silakan coba lagi; filter Anda tetap tersimpan.':error.message,{type:'error',title:'Belum dapat memperbarui data'});}finally{clearTimeout(timeout);if(ticket===sequence)busy(false);}
}
function filter(form){if(!form.isConnected||!form.checkValidity())return;const url=new URL(form.action||location.pathname,location.origin);url.search='';for(const [name,value] of new FormData(form)){if(typeof value==='string'&&value.trim()!=='')url.searchParams.append(name,value.trim());}loadView(url.href,{keepFocus:true});}
function schedule(form,delay=350){clearTimeout(debounce);sequence++;controller?.abort();busy(false);debounce=setTimeout(()=>filter(form),delay);}
document.addEventListener('input',event=>{const form=event.target.closest('form.list-filters');if(form&&event.target.matches('input:not([type="hidden"])'))schedule(form,event.target.type==='search'?300:500);});
document.addEventListener('change',event=>{const form=event.target.closest('form.list-filters');if(form)schedule(form,0);});
document.addEventListener('submit',event=>{
 const form=event.target;if(form.matches('form.list-filters')){event.preventDefault();clearTimeout(debounce);filter(form);return;}
 if(document.body.dataset.sidebarScope!=='admin'||form.method.toLowerCase()!=='post'||new URL(form.action,location.origin).pathname==='/logout'||form.id==='api-tester'||event.defaultPrevented)return;
 if(form.hasAttribute('data-confirm')&&!form.dataset.ajaxConfirmed)return;
 event.preventDefault();if(form.dataset.ajaxPending)return;form.dataset.ajaxPending='true';const button=event.submitter;const mutationTicket=++sequence;controller?.abort();const data=new FormData(form);if(button?.name)data.append(button.name,button.value);if(button){button.disabled=true;button.setAttribute('aria-busy','true');}busy(true);
 (async()=>{try{const response=await fetch(form.action,{method:'POST',body:data,headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},signal:AbortSignal.timeout(45000)});const html=await readResponse(response);if(mutationTicket!==sequence){notify('Perubahan berhasil disimpan.',{type:'success'});return;}render(html,response.url,{historyMode:new URL(response.url).pathname===location.pathname?'replace':'push'});}catch(error){notify(error.name==='TimeoutError'?'Respons server belum pasti. Periksa data sebelum mengirim ulang.':error.message,{type:'error',title:'Perubahan belum dapat dipastikan'});}finally{delete form.dataset.ajaxPending;if(button?.isConnected){button.disabled=false;button.removeAttribute('aria-busy');}if(mutationTicket===sequence)busy(false);}})();
});
document.addEventListener('click',event=>{
 const link=event.target.closest('a[href]');if(!link||event.defaultPrevented||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey||link.target==='_blank'||link.hasAttribute('download')||link.getAttribute('aria-disabled')==='true')return;
 const url=new URL(link.href,location.origin);if(url.origin!==location.origin||url.hash)return;
 const filtered=!!link.closest('.list-filters,.record-pagination,.pagination,.table-footer');const admin=document.body.dataset.sidebarScope==='admin'&&adminPaths.has(url.pathname);
 if(!filtered&&!admin)return;event.preventDefault();clearTimeout(debounce);loadView(url.href,{historyMode:filtered?'replace':'push',scroll:admin&&!filtered});
});
window.addEventListener('popstate',event=>{
 const target=new URL(event.state?.kzViewUrl||location.href,location.origin);
 if(document.body.dataset.sidebarScope!=='admin' && (target.pathname.startsWith('/member') || document.body.dataset.sidebarScope==='member')){location.reload();return;}
 loadView(target.href,{historyMode:'none',scroll:true});
});
window.addEventListener('pageshow',event=>{
 if(event.persisted && (document.body.dataset.sidebarScope==='member' || location.pathname.startsWith('/member') || document.body.classList.contains('storefront-body'))) location.reload();
});
const saved=history.state?.kzViewUrl;if(location.search){cleanHistory(currentUrl);}else if(saved&&new URL(saved).pathname===location.pathname&&new URL(saved).search){queueMicrotask(()=>loadView(saved,{historyMode:'none'}));}else{cleanHistory(currentUrl);}
