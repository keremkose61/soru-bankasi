<?php ob_start(); ?>

<!-- Hero Alanı -->
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-effect p-5 text-center position-relative overflow-hidden" style="border-radius: 30px;">
            <h1 class="display-4 fw-bold mb-3" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <?= __('hero_title') ?>
            </h1>
            <p class="lead mb-4 text-muted"><?= __('hero_subtitle') ?></p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/register" class="btn btn-gradient btn-lg px-5 rounded-pill shadow"><?= __('join_now') ?></a>
            <?php else: ?>
                <?php if($_SESSION['role'] === 'teacher'): ?>
                    <a href="#questions-section" class="btn btn-gradient btn-lg px-5 rounded-pill shadow pulse-on-hover"><i class="bi bi-chat-dots me-2"></i><?= __('answer_btn') ?></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/question/create" class="btn btn-gradient btn-lg px-5 rounded-pill shadow"><i class="bi bi-plus-circle me-2"></i><?= __('ask_question_btn') ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($dashboardStats)): ?>
<!-- Personal Learning Dashboard -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Sorularım</span>
                    <i class="bi bi-question-circle text-primary fs-5"></i>
                </div>
                <div class="fs-2 fw-bold"><?= (int)$dashboardStats['questions_count'] ?></div>
                <span class="text-muted small">Paylaştığın soru</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Cevaplarım</span>
                    <i class="bi bi-chat-square-text text-success fs-5"></i>
                </div>
                <div class="fs-2 fw-bold"><?= (int)$dashboardStats['answers_count'] ?></div>
                <span class="text-muted small">Verdiğin cevap</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Puanlarım</span>
                    <i class="bi bi-lightning-charge text-warning fs-5"></i>
                </div>
                <div class="fs-2 fw-bold"><?= (int)$dashboardStats['points'] ?></div>
                <span class="text-muted small">Toplam başarı puanı</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Bugünkü Odak</span>
                    <i class="bi bi-stopwatch text-info fs-5"></i>
                </div>
                <div class="fs-2 fw-bold"><?= (int)$dashboardStats['today_minutes'] ?><small class="fs-6 text-muted ms-1">dk</small></div>
                <span class="text-muted small"><?= (int)$dashboardStats['today_sessions'] ?> tamamlanan seans</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Son Odak Çalışmalarım</h5>
                        <p class="text-muted small mb-0">En son tamamladığın Pomodoro seansları</p>
                    </div>
                    <a href="<?= BASE_URL ?>/pomodoro" class="btn btn-sm btn-glass-outline rounded-pill">Pomodoro'ya Git</a>
                </div>
                <?php if (empty($recentPomodoroSessions)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-hourglass-split fs-3 d-block mb-2"></i>
                        Henüz tamamlanmış bir odak seansın yok. İlk seansını başlat!
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($recentPomodoroSessions as $session): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background: var(--bs-tertiary-bg);">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;background:rgba(79,70,229,.12);">
                                        <i class="bi bi-clock-history text-primary"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold"><?= (int)$session['duration_minutes'] ?> dakikalık çalışma</div>
                                        <small class="text-muted"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($session['created_at']))) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">Tamamlandı</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions section removed as requested -->

<!-- YKS / Sınav Geri Sayım Sayacı (Countdown Widget) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-effect p-4 rounded-4 shadow-sm position-relative overflow-hidden" style="border: 1px solid var(--glass-border-light);">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center text-lg-start mb-3 mb-lg-0">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold mb-2">
                        <i class="bi bi-calendar-event me-1"></i> Hedefe Kalan Zaman
                    </span>
                    <h4 class="fw-bold mb-1" style="color: var(--primary-color);">YKS (TYT - AYT) Sayacı</h4>
                    <p class="text-muted small mb-0">Her çözdüğün soru seni hayalindeki üniversiteye bir adım daha yaklaştırır!</p>
                </div>
                <div class="col-lg-8">
                    <div class="d-flex justify-content-center justify-content-lg-end gap-3 flex-wrap" id="examCountdown">
                        <div class="text-center p-2 rounded-3" style="background: rgba(79, 70, 229, 0.08); min-width: 75px;">
                            <div class="fw-bolder fs-3 text-primary mb-0" id="cd-days">--</div>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Gün</small>
                        </div>
                        <div class="text-center p-2 rounded-3" style="background: rgba(79, 70, 229, 0.08); min-width: 75px;">
                            <div class="fw-bolder fs-3 text-primary mb-0" id="cd-hours">--</div>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Saat</small>
                        </div>
                        <div class="text-center p-2 rounded-3" style="background: rgba(79, 70, 229, 0.08); min-width: 75px;">
                            <div class="fw-bolder fs-3 text-primary mb-0" id="cd-minutes">--</div>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Dakika</small>
                        </div>
                        <div class="text-center p-2 rounded-3" style="background: rgba(79, 70, 229, 0.08); min-width: 75px;">
                            <div class="fw-bolder fs-3 text-primary mb-0" id="cd-seconds">--</div>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Saniye</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 align-items-center" id="questions-section" style="scroll-margin-top: 100px;">
    <div class="col-lg-3 mb-2 mb-lg-0">
        <h3 class="fw-bold mb-0"><i class="bi bi-compass me-2 text-primary"></i><?= __('discover') ?></h3>
    </div>
    <div class="col-lg-9 text-lg-end d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
        
        <!-- Filtre ve Arama Formu -->
        <form method="GET" action="<?= BASE_URL ?>/" class="d-inline-flex align-items-center gap-2 flex-wrap" id="filterForm">
            <?php if(isset($_GET['sort'])): ?>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
            <?php endif; ?>

            <!-- Arama Kutusu -->
            <div class="input-group input-group-sm" style="max-width: 250px;">
                <input type="text" name="q" class="form-control form-control-glass py-1 px-3" placeholder="Soru veya etiket ara..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="border-radius: 15px 0 0 15px; font-size: 0.85rem;">
                <button class="btn btn-gradient text-white py-1 px-3 shadow-none" type="submit" style="border-radius: 0 15px 15px 0;" title="Ara">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            
            <select name="status" class="form-select form-select-sm form-control-glass py-1 px-2" style="width: auto; border-radius: 15px; font-size: 0.85rem;" onchange="document.getElementById('filterForm').submit();">
                <option value="">Tüm Durumlar</option>
                <option value="unsolved" <?= (isset($_GET['status']) && $_GET['status'] == 'unsolved') ? 'selected' : '' ?>>Sadece Çözülemeyenler</option>
                <option value="solved" <?= (isset($_GET['status']) && $_GET['status'] == 'solved') ? 'selected' : '' ?>>Çözüldü</option>
            </select>

            <select name="subject" class="form-select form-select-sm form-control-glass py-1 px-2" style="width: auto; border-radius: 15px; font-size: 0.85rem;" onchange="document.getElementById('filterForm').submit();">
                <option value=""><?= __('all_subjects') ?></option>
                <?php if(!empty($subjects)): ?>
                    <?php foreach($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= (isset($_GET['subject']) && $_GET['subject'] == $s) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <?php if(!empty($_GET['q']) || !empty($_GET['subject']) || !empty($_GET['status'])): ?>
                <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-sm" title="Filtreleri Temizle" style="font-size: 0.8rem;">
                    <i class="bi bi-x-circle me-1"></i> Temizle
                </a>
            <?php endif; ?>
        </form>

        <!-- Sıralama Butonları -->
        <?php 
            $sortParam = (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'newest' : 'most_commented';
            $urlParams = [];
            if(isset($_GET['subject']) && $_GET['subject'] !== '') $urlParams['subject'] = $_GET['subject'];
            if(isset($_GET['q']) && $_GET['q'] !== '') $urlParams['q'] = $_GET['q'];
            if(isset($_GET['status']) && $_GET['status'] !== '') $urlParams['status'] = $_GET['status'];
            $queryString = !empty($urlParams) ? '&' . http_build_query($urlParams) : '';
        ?>
        <a href="<?= BASE_URL ?>/?sort=newest<?= $queryString ?>" class="btn btn-sm px-3 py-1 rounded-pill <?= $sortParam == 'newest' ? 'btn-gradient' : 'btn-glass-outline' ?>" style="font-size: 0.85rem;"><?= __('sort_newest') ?></a>
        <a href="<?= BASE_URL ?>/?sort=most_commented<?= $queryString ?>" class="btn btn-sm px-3 py-1 rounded-pill <?= $sortParam == 'most_commented' ? 'btn-gradient' : 'btn-glass-outline' ?>" style="font-size: 0.85rem;"><?= __('sort_most_commented') ?></a>
        <a href="<?= BASE_URL ?>/exam/booklet<?= !empty($queryString) ? '?' . ltrim($queryString, '&') : '' ?>" target="_blank" class="btn btn-sm btn-outline-primary px-3 py-1 rounded-pill shadow-sm" style="font-size: 0.85rem;" title="Mevcut soruları yazdırılabilir deneme kitapçığına dönüştür">
            <i class="bi bi-file-earmark-pdf me-1"></i> Deneme Kitapçığı (PDF)
        </a>
    </div>
</div>

<div class="row">
    <?php if (empty($questions)): ?>
        <div class="col-12 text-center py-5">
            <div class="glass-effect p-5 rounded-4 d-inline-block">
                <i class="bi bi-search text-muted fs-1 mb-3 d-block"></i>
                <h5 class="text-muted mb-2"><?= __('no_questions') ?></h5>
                <p class="text-muted small mb-3">Arama kriterlerinizi değiştirerek veya yeni bir soru sorarak topluluğa katılabilirsiniz.</p>
                <?php if(!empty($_GET['q']) || !empty($_GET['subject']) || !empty($_GET['status'])): ?>
                    <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-gradient rounded-pill px-4">Tüm Soruları Göster</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($questions as $q): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 question-card glass-effect hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge badge-glass"><?= htmlspecialchars($q['subject']) ?></span>
                                <?php if(isset($q['is_solved']) && $q['is_solved'] > 0): ?>
                                    <span class="badge bg-success ms-1"><i class="bi bi-check-circle"></i> Çözüldü</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></small>
                        </div>
                        <h5 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($q['title']) ?>">
                            <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="text-decoration-none" style="color: inherit;">
                                <?= htmlspecialchars($q['title']) ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted mb-3 description-truncate">
                            <?= strip_tags($q['description']) ?>
                        </p>
                        
                        <?php if(!empty($q['tags_list'])): ?>
                            <div class="mb-3">
                                <?php foreach(explode(',', $q['tags_list']) as $tag): 
                                    $trimmedTag = trim($tag);
                                    if(empty($trimmedTag)) continue;
                                ?>
                                    <a href="<?= BASE_URL ?>/?q=<?= urlencode('#' . $trimmedTag) ?>" class="badge bg-light text-primary border border-primary-subtle me-1 rounded-pill fw-normal text-decoration-none" title="#<?= htmlspecialchars($trimmedTag) ?> etiketli sorular">
                                        #<?= htmlspecialchars($trimmedTag) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <a href="<?= BASE_URL ?>/profile/<?= $q['user_id'] ?>" class="text-primary fw-bold text-decoration-none small" title="Kullanıcı Profilini Gör">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($q['username']) ?>
                            </a>
                            <span class="text-muted small"><i class="bi bi-chat-dots"></i> <?= $q['answer_count'] ?> <?= __('comments_count') ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 d-flex gap-2 pb-3 px-3 align-items-center">
                        <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="btn btn-glass-outline btn-sm flex-grow-1 text-truncate"><?= __('view_and_solve') ?></a>
                        
                        <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $q['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'))): ?>
                            <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>/delete" class="btn btn-outline-danger btn-sm" onclick="return confirm('<?= __('delete_confirm') ?>');" title="<?= __('delete') ?>"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// YKS Geri Sayım Sayacı
document.addEventListener('DOMContentLoaded', function() {
    // Hedef Sınav Tarihi (Örn: 2027 Haziran 3. Haftasonu, 19 Haziran 10:15)
    let currentYear = new Date().getFullYear();
    let examDate = new Date(currentYear, 5, 20, 10, 15, 0); // 20 Haziran
    if (new Date() > examDate) {
        examDate = new Date(currentYear + 1, 5, 20, 10, 15, 0);
    }

    const daysEl = document.getElementById('cd-days');
    const hoursEl = document.getElementById('cd-hours');
    const minsEl = document.getElementById('cd-minutes');
    const secsEl = document.getElementById('cd-seconds');

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = examDate.getTime() - now;

        if (distance < 0) {
            if (daysEl) daysEl.textContent = '0';
            if (hoursEl) hoursEl.textContent = '0';
            if (minsEl) minsEl.textContent = '0';
            if (secsEl) secsEl.textContent = '0';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (daysEl) daysEl.textContent = days;
        if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, '0');
        if (minsEl) minsEl.textContent = minutes.toString().padStart(2, '0');
        if (secsEl) secsEl.textContent = seconds.toString().padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
