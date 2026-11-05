<?php ob_start(); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
 <div><span class="badge bg-primary-subtle text-primary mb-2">BRAINBOOST SINAV MERKEZİ</span><h1 class="fw-bold mb-1">Online Deneme Sınavı</h1><p class="text-muted mb-0">Süreli sınavlara katıl, sonuçlarını ve netlerini takip et.</p></div>
 <?php if($canManage): ?><a class="btn btn-outline-primary rounded-pill" href="<?= BASE_URL ?>/mock-exams/question/create"><i class="bi bi-plus-circle me-1"></i>Soru Ekle</a><?php endif; ?>
</div>
<?php if(isset($_GET['added'])): ?><div class="alert alert-success">Soru başarıyla eklendi.</div><?php endif; ?>
<?php if(isset($_GET['empty'])): ?><div class="alert alert-warning">Bu ders için henüz yeterli soru bulunmuyor.</div><?php endif; ?>
<div class="row g-4">
 <div class="col-lg-5"><div class="card border-0 shadow-sm glass-effect h-100"><div class="card-body p-4">
  <h5 class="fw-bold mb-3"><i class="bi bi-play-circle text-primary me-2"></i>Yeni Deneme Başlat</h5>
  <p class="text-muted small">Sistemde şu anda <?= (int)$questionCount ?> aktif sınav sorusu var.</p>
  <form method="POST" action="<?= BASE_URL ?>/mock-exams/start">
   <label class="form-label fw-semibold">Ders</label><select class="form-select mb-3" name="subject"><option value="">Tüm dersler</option><?php foreach($subjects as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?></select>
   <label class="form-label fw-semibold">Soru sayısı</label><select class="form-select mb-3" name="question_count"><option value="10">10 soru</option><option value="20" selected>20 soru</option><option value="30">30 soru</option><option value="40">40 soru</option><option value="50">50 soru</option></select>
   <label class="form-label fw-semibold">Süre</label><select class="form-select mb-4" name="duration"><option value="10">10 dakika</option><option value="20" selected>20 dakika</option><option value="30">30 dakika</option><option value="45">45 dakika</option><option value="60">60 dakika</option><option value="90">90 dakika</option></select>
   <button class="btn btn-gradient text-white w-100" type="submit" <?= $questionCount<1?'disabled':'' ?>>Sınava Başla <i class="bi bi-arrow-right ms-1"></i></button>
  </form>
 </div></div></div>
 <div class="col-lg-7"><div class="card border-0 shadow-sm glass-effect h-100"><div class="card-body p-4">
  <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Son Denemelerim</h5>
  <?php if(empty($history)): ?><div class="text-center text-muted py-5"><i class="bi bi-journal-check fs-1 d-block mb-2"></i>Henüz tamamlanmış denemen yok.</div>
  <?php else: ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Deneme</th><th>Doğru</th><th>Yanlış</th><th>Net</th><th></th></tr></thead><tbody><?php foreach($history as $h): ?><tr><td><strong><?= htmlspecialchars($h['title']) ?></strong><div class="small text-muted"><?= date('d.m.Y H:i',strtotime($h['submitted_at'])) ?></div></td><td class="text-success fw-bold"><?= (int)$h['correct_count'] ?></td><td class="text-danger fw-bold"><?= (int)$h['wrong_count'] ?></td><td class="fw-bold"><?= number_format((float)$h['net_score'],2,',','.') ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/mock-exams/result/<?= (int)$h['id'] ?>">Sonuç</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
 </div></div></div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/layout.php';