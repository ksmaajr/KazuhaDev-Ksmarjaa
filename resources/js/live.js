import {getViewUrl} from './navigation';
import {notify} from './feedback';
const endpoint=document.body.dataset.feedUrl;
if(endpoint){
 let latest=null,revision=null,scope=null,read=0,busy=false,pendingRevision=null;
 const panel=document.querySelector('.notification-panel'),toggle=document.querySelector('[data-notification-toggle]');
 const safeToRefresh=()=>{
  if(document.hidden||document.documentElement.dataset.viewBusy==='true'||document.querySelector('dialog[open],.custom-select.is-open'))return false;
  const area=document.querySelector('[data-live-content]');if(!area)return false;
  if(area.contains(document.activeElement)&&document.activeElement.matches('input,textarea,select'))return false;
  if(window.getSelection()?.toString())return false;
  return !Array.from(area.querySelectorAll('form')).some(form=>{
   if(form.closest('dialog')&&!form.closest('dialog').open)return false;
   return Array.from(form.elements).some(el=>{
    if((!el.name&&!el.matches('[data-money-input]'))||el.type==='hidden'||el.disabled)return false;
    if(el.type==='file')return el.files.length>0;
    if(el.type==='checkbox'||el.type==='radio')return el.checked!==el.defaultChecked;
    if(el.tagName==='SELECT'){const index=Array.from(el.options).findIndex(o=>o.defaultSelected);return el.selectedIndex!==(index<0?0:index);}
    return 'defaultValue' in el&&el.value!==el.defaultValue;
   });
  });
 };
 const refresh=async()=>{
  if(!pendingRevision||!safeToRefresh()||location.pathname.includes('api-console'))return;
  const requested=pendingRevision,requestedUrl=getViewUrl();
  const response=await fetch(requestedUrl,{headers:{'X-Requested-With':'XMLHttpRequest'},cache:'no-store',signal:AbortSignal.timeout(10000)});
  if(!response.ok||response.redirected)return;
  const html=new DOMParser().parseFromString(await response.text(),'text/html');
  const incoming=html.querySelector('[data-live-content]'),current=document.querySelector('[data-live-content]');
  if(incoming&&current&&safeToRefresh()&&getViewUrl()===requestedUrl){current.replaceChildren(...incoming.childNodes);if(requested===pendingRevision)pendingRevision=null;document.dispatchEvent(new Event('live:updated'));}
 };
 const render=data=>{
  const list=document.querySelector('[data-notification-items]');if(!list)return;list.replaceChildren();
  data.items.forEach(item=>{const row=document.createElement('button');row.type='button';row.dataset.recordUrl=item.details_url;row.setAttribute('aria-label','Lihat detail: '+item.title);row.className='notification-item'+(item.id>read?' unread':'');const dot=document.createElement('i'),body=document.createElement('div'),title=document.createElement('strong'),message=document.createElement('p');title.textContent=item.title;message.textContent=item.message;body.append(title,message);row.append(dot,body);list.append(row);});
  if(!data.items.length){const empty=document.createElement('p');empty.className='notification-empty';empty.textContent='Belum ada notifikasi.';list.append(empty);}
  const count=document.querySelector('[data-notification-count]');if(count){count.hidden=data.unread===0;count.textContent=data.unread>99?'99+':String(data.unread);}
 };
 const poll=async()=>{
  if(busy||document.hidden)return;busy=true;
  try{
   const url=new URL(endpoint,location.origin);if(latest!==null)url.searchParams.set('after',latest);url.searchParams.set('read',read);
   const response=await fetch(url,{headers:{Accept:'application/json'},cache:'no-store',signal:AbortSignal.timeout(10000)});
   if(response.status===401||response.status===403||response.redirected){location.reload();return;}if(!response.ok)return;
   const data=await response.json();
   const firstScope=scope!==data.scope;
   if(firstScope){scope=data.scope;try{read=Number(localStorage.getItem('kazuhadev-read-'+scope)||0);}catch{}}
   render({...data,unread:firstScope?data.items.filter(i=>i.id>read).length:data.unread});
   if(latest!==null)data.new_items.forEach(item=>notify(item.message,{type:'info',title:item.title}));
   if(revision!==null&&revision!==data.revision)pendingRevision=data.revision;
   latest=data.latest_id;revision=data.revision;
   const label=document.querySelector('[data-live-label]');if(label){label.textContent='● Update otomatis · '+new Intl.DateTimeFormat('id-ID',{timeZone:'Asia/Jakarta',hour:'2-digit',minute:'2-digit',second:'2-digit'}).format(new Date(data.server_time));}
   await refresh();
  }catch{const label=document.querySelector('[data-live-label]');if(label)label.textContent='Menyambungkan kembali…';}finally{busy=false;}
 };
 toggle?.addEventListener('click',()=>{panel.hidden=!panel.hidden;toggle.setAttribute('aria-expanded',String(!panel.hidden));});
 document.addEventListener('click',event=>{if(panel&&!event.target.closest('.notification-menu')){panel.hidden=true;toggle?.setAttribute('aria-expanded','false');}});
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&panel&&!panel.hidden){panel.hidden=true;toggle?.setAttribute('aria-expanded','false');toggle?.focus();}});
 document.querySelector('[data-notification-read]')?.addEventListener('click',()=>{read=latest??0;try{localStorage.setItem('kazuhadev-read-'+scope,String(read));}catch{}document.querySelector('[data-notification-count]').hidden=true;document.querySelectorAll('.notification-item.unread').forEach(el=>el.classList.remove('unread'));});
 window.addEventListener('storage',event=>{if(event.key==='kazuhadev-read-'+scope){read=Number(event.newValue||0);poll();}});
 document.addEventListener('visibilitychange',()=>{if(!document.hidden)poll();});setInterval(poll,10000);poll();
}
