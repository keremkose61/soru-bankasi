<?php ob_start(); ?>
<div class="row justify-content-center"><div class="col-lg-9"><div class="card border-0 shadow-sm glass-effect"><div class="card-body p-4 p-lg-5">
<a href="<?= BASE_URL ?>/mock-exams" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Sınav merkezine dön</a><h1 class="h3 fw-bold mt-3 mb-4">Çoktan Seçmeli Soru Ekle</h1>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
<div class="row g-3"><div class="col-md-6"><label class="form-label">Ders *</label><input name="subject" class="form-control" required></div><div class="col-md-6"><label class="form-label">Konu</label><input name="topic" class="form-control"></div>
<div class="col-12"><label class="form-label">Soru metni *</label><textarea name="question_text" class="form-control" rows="3" required></textarea></div>
<?php foreach(['a'=>'A','b'=>'B','c'=>'C','d'=>'D','e'=>'E (isteğe bağlı)'] as $key=>$label): ?><div class="col-md-6"><label class="form-label">Seçenek <?= $label ?> <?= $key!=='e'?'*':'' ?></label><textarea name="option_<?= $key ?>" class="form-control" rows="2" <?= $key!=='e'?'required':'' ?>></textarea></div><?php endforeach; ?>
<div class="col-md-4"><label class="form-label">Doğru cevap</label><select name="correct_option" class="form-select"><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option></select></div>
<div class="col-md-4"><label class="form-label">Zorluk</label><select name="difficulty" class="form-select"><option value="easy">Kolay</option><option value="medium" selected>Orta</option><option value="hard">Zor</option></select></div>
<div class="col-12"><label class="form-label">Çözüm / açıklama</label><textarea name="explanation" class="form-control" rows="3"></textarea></div></div>
<button class="btn btn-gradient text-white mt-4" type="submit">Soruyu Kaydet</button></form>
</div></div></div></div>
<?php $content=ob_get_clean(); require __DIR__.'/layout.php';