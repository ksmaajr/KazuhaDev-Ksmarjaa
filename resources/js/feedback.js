function setIcon(target,type) {
    const icon=document.querySelector('[data-feedback-icon="'+type+'"]') || document.querySelector('[data-feedback-icon="info"]');
    if(target&&icon)target.replaceChildren(icon.content.cloneNode(true));
}
export function notify(message,{type='success',title,duration}={}) {
    const center=document.getElementById('notification-center'),template=document.getElementById('notification-template');
    if(!center||!template)return;
    const item=template.content.firstElementChild.cloneNode(true);
    const variant=['success','error','warning','info'].includes(type)?type:'info';
    item.dataset.tone=variant;item.setAttribute('role',variant==='error'?'alert':'status');item.setAttribute('aria-atomic','true');
    setIcon(item.querySelector('[data-toast-icon]'),variant);
    item.querySelector('[data-toast-title]').textContent=title||{success:'Berhasil',error:'Ada kendala',warning:'Perlu perhatian',info:'Informasi'}[variant];
    item.querySelector('[data-toast-message]').textContent=message;
    const life=duration??(variant==='error'?0:6000);item.style.setProperty('--toast-duration',life+'ms');
    if(!life)item.classList.add('is-persistent');
    let timer,remaining=life,started=0;
    const close=()=>{clearTimeout(timer);item.remove();if(!center.children.length&&center.matches(':popover-open'))center.hidePopover();};
    const resume=()=>{if(!life||item.matches(':hover')||item.contains(document.activeElement))return;started=Date.now();timer=setTimeout(close,remaining);item.classList.remove('is-paused');};
    const pause=()=>{if(!life||!timer)return;clearTimeout(timer);remaining=Math.max(0,remaining-(Date.now()-started));timer=null;item.classList.add('is-paused');};
    item.querySelector('button').addEventListener('click',close);
    item.addEventListener('mouseenter',pause);item.addEventListener('mouseleave',resume);item.addEventListener('focusin',pause);item.addEventListener('focusout',()=>queueMicrotask(resume));
    while(center.children.length>=4)center.firstElementChild.dispatchEvent(new Event('feedback-dismiss'));
    item.addEventListener('feedback-dismiss',close);center.append(item);try{if(center.showPopover&&!center.matches(':popover-open'))center.showPopover();}catch{}resume();
}
let queue=Promise.resolve();
function openConfirmation({title='Lanjutkan tindakan ini?',message='',action='Lanjutkan',tone='warning',icon='warning',hint='Periksa kembali sebelum melanjutkan.',copyValue}={}) {
    return new Promise(resolve=>{
        const dialog=document.getElementById('universal-confirm');
        if(!dialog){notify('Muat ulang halaman untuk membuka konfirmasi.',{type:'error'});resolve(false);return;}
        const opener=document.activeElement,cancel=dialog.querySelector('[data-confirm-cancel]'),accept=dialog.querySelector('[data-confirm-accept]'),copy=dialog.querySelector('#confirm-copy-value');
        dialog.dataset.tone=tone;dialog.querySelector('#confirm-title').textContent=title;dialog.querySelector('#confirm-message').textContent=message;dialog.querySelector('[data-confirm-label]').textContent=action;dialog.querySelector('[data-confirm-hint]').textContent=hint;dialog.querySelector('[data-confirm-eyebrow]').textContent=copyValue!==undefined?'SALIN MANUAL':'KONFIRMASI TINDAKAN';
        setIcon(dialog.querySelector('[data-confirm-icon]'),icon);copy.hidden=copyValue===undefined;copy.value=copyValue??'';cancel.hidden=copyValue!==undefined;
        let result=false;const events=new AbortController(),options={signal:events.signal};
        dialog.addEventListener('close',()=>{events.abort();copy.value='';if(opener?.isConnected)opener.focus({preventScroll:true});resolve(result);},{once:true});
        accept.addEventListener('click',()=>{result=true;dialog.close();},options);cancel.addEventListener('click',()=>dialog.close(),options);dialog.querySelector('[data-confirm-close]').addEventListener('click',()=>dialog.close(),options);
        dialog.addEventListener('click',event=>{if(event.target!==dialog)return;const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)dialog.close();},options);
        dialog.showModal();if(copyValue!==undefined){copy.focus();copy.select();}else{cancel.focus();}
    });
}
export function confirmAction(options) {
    const next=queue.then(()=>openConfirmation(options));queue=next.catch(()=>false);return next;
}
document.querySelectorAll('[data-flash-feedback]').forEach(el=>{notify((el.querySelector('[data-feedback-message]')?.innerText||el.textContent).trim(),{type:el.dataset.flashFeedback,title:el.dataset.feedbackTitle});el.remove();});
let reportingInvalid=false;
document.addEventListener('invalid',event=>{
    event.preventDefault();event.target.setAttribute('aria-invalid','true');if(reportingInvalid)return;
    reportingInvalid=true;event.target.focus();
    notify((event.target.labels?.[0]?.textContent.trim()||'Isian')+': '+event.target.validationMessage,{type:'error',title:'Periksa isian formulir'});
    queueMicrotask(()=>{reportingInvalid=false;});
},true);
document.addEventListener('input',event=>{if(event.target.validity?.valid)event.target.removeAttribute('aria-invalid');});
