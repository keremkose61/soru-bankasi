<?php ob_start(); ?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2">KİŞİSEL RAPOR</span>
        <h1 class="fw-bold mb-2">Başarı Analizi</h1>
        <p class="text-muted mb-0">Merhaba <?= htmlspecialchars($user['username'] ?? ''); ?>! Öğrenme topluluğundaki katkılarını burada takip edebilirsin.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/pomodoro" class="btn btn-gradient rounded-pill px-4"><i class="bi bi-stopwatch me-2"></i>Odaklanmaya Başla</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body">
            <div class="text-muted small fw-semibold mb-2">Toplam Cevap</div>
            <div class="fs-2 fw-bold"><?= $totalAnswers ?></div>
            <small class="text-muted">Paylaştığın çözümler</small>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body">
            <div class="text-muted small fw-semibold mb-2">En İyi Cevap</div>
            <div class="fs-2 fw-bold text-success"><?= $bestAnswers ?></div>
            <small class="text-muted">En iyi seçilen çözümlerin</small>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body">
            <div class="text-muted small fw-semibold mb-2">Toplam Beğeni</div>
            <div class="fs-2 fw-bold text-danger"><?= $totalLikes ?></div>
            <small class="text-muted">Cevaplarına gelen beğeniler</small>
        </div></div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body">
            <div class="text-muted small fw-semibold mb-2">Başarı Puanı</div>
            <div class="fs-2 fw-bold text-primary"><?= (int)($user['points'] ?? 0) ?></div>
            <small class="text-muted">Toplam puanın</small>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body p-4">
            <h5 class="fw-bold mb-1">Son 7 Günlük Etkinlik</h5>
            <p class="text-muted small mb-4">Günlük paylaştığın cevap sayısı</p>
            <div class="d-flex align-items-end justify-content-between gap-2" style="height:190px;">
                <?php $activityMax = max(1, max($activityValues)); ?>
                <?php foreach ($activityValues as $i => $value): ?>
                    <div class="d-flex flex-column align-items-center justify-content-end flex-fill h-100">
                        <small class="text-muted mb-2"><?= (int)$value ?></small>
                        <div class="w-100 rounded-top-3" title="<?= (int)$value ?> cevap" style="height:<?= max(5, (int)round(($value / $activityMax) * 125)) ?>px;max-width:46px;background:linear-gradient(180deg,#818cf8,#4f46e5);"></div>
                        <small class="text-muted mt-2"><?= htmlspecialchars($activityLabels[$i]) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="small text-muted mt-3">Bu hafta toplam <strong><?= $activityTotal ?></strong> cevap paylaştın.</div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm glass-effect"><div class="card-body p-4">
            <h5 class="fw-bold mb-1">Derslere Göre Katkı</h5>
            <p class="text-muted small mb-4">Cevaplarının ders dağılımı</p>
            <?php if (empty($subjectStats) || $subjectTotal === 0): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-journal-plus fs-2 d-block mb-2"></i>
                    Henüz ders bazında cevap etkinliğin bulunmuyor.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($subjectStats as $subject): ?>
                        <?php $count = (int)$subject['count']; $percent = (int)round(($count / $subjectTotal) * 100); ?>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold"><?= htmlspecialchars($subject['subject'] ?? 'Diğer') ?></span>
                                <span class="text-muted"><?= $count ?> cevap · %<?= $percent ?></span>
                            </div>
                            <div class="progress" style="height:8px;background:var(--bs-tertiary-bg);">
                                <div class="progress-bar rounded-pill" role="progressbar" style="width:<?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<div class="alert alert-info border-0 rounded-4">
    <i class="bi bi-info-circle me-2"></i>
    Bu rapor, BrainBoost'taki soru-cevap katkılarını gösterir. Henüz sınav denemelerinin doğru-yanlış sonuçları kaydedilmediği için net ve başarı yüzdesi hesaplanmaz.
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
