<?php ob_start(); ?>
<div class="row justify-content-center"><div class="col-xl-9">
 <div class="mb-4"><span class="badge bg-primary-subtle text-primary mb-2">BRAINBOOST AI</span><h1 class="fw-bold mb-2">Yapay Zekâ Öğretmeni</h1><p class="text-muted mb-0">Bir konuyu sor, adım adım öğren, örneklerle pekiştir.</p></div>
 <div class="card border-0 shadow-sm glass-effect overflow-hidden">
  <div class="p-3 p-md-4 text-white" style="background:var(--primary-gradient)"><div class="d-flex align-items-center gap-3"><div class="rounded-circle bg-white bg-opacity-25 p-3"><i class="bi bi-robot fs-3"></i></div><div><div class="fw-bold fs-5">BrainBoost Öğretmen</div><div class="small opacity-75">Ders desteği · Konu anlatımı · Örnek soru</div></div></div></div>
  <div id="tutor-chat" class="p-3 p-md-4" style="min-height:340px;max-height:55vh;overflow-y:auto">
   <div class="d-flex gap-2 mb-3"><div class="rounded-circle bg-primary-subtle text-primary p-2"><i class="bi bi-robot"></i></div><div class="p-3 rounded-4 bg-body-tertiary" style="max-width:85%"><strong>Merhaba!</strong><p class="mb-0 mt-1">Hangi dersi veya konuyu öğrenmek istiyorsun? Örneğin: “12. sınıf fotosentezi basitçe anlat” veya “İkinci dereceden denklemleri örnekle açıkla.”</p></div></div>
  </div>
  <div class="border-top p-3 p-md-4">
   <form id="tutor-form"><label for="tutor-prompt" class="form-label fw-semibold">Sorunu yaz</label><textarea id="tutor-prompt" class="form-control mb-2" rows="3" maxlength="2000" placeholder="Öğrenmek istediğin konuyu veya soruyu yaz..." required></textarea><div class="d-flex justify-content-between align-items-center gap-2"><small class="text-muted">En fazla 2000 karakter</small><button class="btn btn-gradient text-white px-4" id="tutor-send" type="submit"><i class="bi bi-send me-1"></i> Gönder</button></div></form>
   <div class="small text-muted mt-3"><i class="bi bi-info-circle me-1"></i>Yapay zekâ yanıtlarını kontrol et; önemli sınav bilgilerini ders kaynaklarınla doğrula.</div>
  </div>
 </div>
</div></div>
<script>
(()=>{
 const form=document.getElementById('tutor-form'),input=document.getElementById('tutor-prompt'),chat=document.getElementById('tutor-chat'),button=document.getElementById('tutor-send');
 const add=(text,who,isError=false)=>{const wrap=document.createElement('div');wrap.className='d-flex gap-2 mb-3 '+(who==='user'?'justify-content-end':'');const bubble=document.createElement('div');bubble.className='p-3 rounded-4 '+(who==='user'?'bg-primary text-white':'bg-body-tertiary')+(isError?' border border-danger':'');bubble.style.maxWidth='88%';bubble.style.whiteSpace='pre-wrap';bubble.textContent=text;wrap.appendChild(bubble);chat.appendChild(wrap);chat.scrollTop=chat.scrollHeight;};
 form.addEventListener('submit',async e=>{e.preventDefault();const prompt=input.value.trim();if(!prompt)return;add(prompt,'user');input.value='';button.disabled=true;button.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Yanıtlanıyor';try{const data=new FormData();data.append('prompt',prompt);const response=await fetch('<?= BASE_URL ?>/ai-tutor/ask',{method:'POST',body:data});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Yanıt alınamadı.');add(result.answer||'Yanıt boş döndü.','assistant');}catch(err){add(err.message+' API anahtarının secrets.php dosyasında tanımlı olduğundan emin ol.','assistant',true);}finally{button.disabled=false;button.innerHTML='<i class="bi bi-send me-1"></i> Gönder';input.focus();}});
})();
</script>
<?php $content=ob_get_clean(); require __DIR__.'/layout.php';