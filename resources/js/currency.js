const enhanced=new WeakSet();
const moneyNames=new Set(['price','amount','fee','min_amount','max_amount']);
function enhanceMoney(input){
 if(enhanced.has(input)||!moneyNames.has(input.name)||input.type!=='number')return;
 const name=input.name,minimum=Number(input.min||0),maximum=Number(input.max||100000000000);
 const hidden=document.createElement('input');hidden.type='hidden';hidden.name=name;hidden.value=input.value;hidden.defaultValue=input.defaultValue;
 const wrapper=document.createElement('div');wrapper.className='rupiah-field';const prefix=document.createElement('span');prefix.className='rupiah-prefix';prefix.textContent='Rp';prefix.setAttribute('aria-hidden','true');input.before(wrapper);wrapper.append(prefix,input,hidden);
 input.type='text';input.inputMode='numeric';input.removeAttribute('name');input.dataset.moneyInput=name;input.autocomplete='off';input.spellcheck=false;input.maxLength=30;
 function format(preserveCaret=false){
  const source=input.value,caret=input.selectionStart??source.length,digitsBefore=source.slice(0,caret).replace(/[^0-9]/g,'').length;
  if(source.includes('-')||(/,/.test(source)&&!/,0{1,2}$/.test(source))){hidden.value='';input.setCustomValidity('Gunakan nominal Rupiah bulat tanpa nilai negatif atau pecahan.');return;}
  const sanitized=source.replace(/,0{1,2}$/,'').replace(/^\s*Rp\s*/i,'').replace(/[^0-9]/g,'').replace(/^0+(?=\d)/,'');
  hidden.value=sanitized;input.value=sanitized.replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  let error='';if(sanitized!==''&&(Number(sanitized)<minimum||Number(sanitized)>maximum)){error='Nominal harus antara Rp '+minimum.toLocaleString('id-ID')+' dan Rp '+maximum.toLocaleString('id-ID')+'.';}input.setCustomValidity(error);
  if(preserveCaret&&document.activeElement===input){let seen=0,position=0;while(position<input.value.length&&seen<digitsBefore){if(/[0-9]/.test(input.value[position]))seen++;position++;}input.setSelectionRange(position,position);}
 }
 format();input.defaultValue=input.value;input.addEventListener('input',()=>format(true));input.addEventListener('change',()=>format());input.form?.addEventListener('reset',()=>setTimeout(()=>{input.value=hidden.defaultValue;format();},0));enhanced.add(input);
}
function initializeMoney(){document.querySelectorAll('input[type="number"]').forEach(enhanceMoney);}
document.addEventListener('live:updated',initializeMoney);initializeMoney();
