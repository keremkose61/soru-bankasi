<?php 
ob_start(); 
$isOwnProfile = $isOwnProfile ?? true;
?>

<?php if (!empty($_SESSION['photo_error'])): ?>
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-center rounded-3 mb-4 animate-fade-in" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div><?= htmlspecialchars($_SESSION['photo_error']) ?></div>
</div>
<?php unset($_SESSION['photo_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['photo_success'])): ?>
<div class="alert alert-success border-0 shadow-sm d-flex align-items-center rounded-3 mb-4 animate-fade-in" role="alert">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <div><?= htmlspecialchars($_SESSION['photo_success']) ?></div>
</div>
<?php unset($_SESSION['photo_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['profile_error'])): ?>
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-center rounded-3 mb-4 animate-fade-in" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div><?= htmlspecialchars($_SESSION['profile_error']) ?></div>
</div>
<?php unset($_SESSION['profile_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['profile_success'])): ?>
<div class="alert alert-success border-0 shadow-sm d-flex align-items-center rounded-3 mb-4 animate-fade-in" role="alert">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <div><?= htmlspecialchars($_SESSION['profile_success']) ?></div>
</div>
<?php unset($_SESSION['profile_success']); ?>
<?php endif; ?>

<div class="row justify-content-center mb-5 animate-fade-in">
    <div class="col-lg-10">
        
        <!-- Üst Kısım: Kullanıcı Kartı -->
        <div class="glass-effect rounded-4 text-center position-relative overflow-hidden mb-5 hover-lift" style="border: 1px solid var(--glass-border-light);">
            
            <!-- Kapak Fotoğrafı -->
            <div class="position-relative w-100" style="height: 250px; background: var(--primary-gradient);">
                <?php if (!empty($user['cover_photo'])): ?>
                    <img src="<?= BASE_URL . $user['cover_photo'] ?>" alt="Cover Photo" class="w-100 h-100" style="object-fit: cover;">
                <?php endif; ?>
                
                <!-- Kapak Fotoğrafı Yükleme Butonu (Yalnızca Kendi Profili) -->
                <?php if ($isOwnProfile): ?>
                <form action="<?= BASE_URL ?>/profile/update-photo" method="POST" enctype="multipart/form-data" class="position-absolute top-0 end-0 m-3" id="coverPhotoForm">
                    <label for="cover_photo_input" class="btn btn-sm btn-light rounded-pill shadow-sm" style="cursor:pointer; background:rgba(255,255,255,0.85); backdrop-filter:blur(5px);">
                        <i class="bi bi-camera-fill me-1"></i> Kapak Değiştir
                    </label>
                    <input type="file" id="cover_photo_input" name="cover_photo" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="document.getElementById('coverPhotoForm').submit();">
                </form>
                <?php endif; ?>
            </div>
            
            <div class="position-relative p-4" style="z-index: 1; margin-top: -80px;">
                <!-- Profil Fotoğrafı ve Yükleme -->
                <div class="position-relative d-inline-block mb-3">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?= BASE_URL . $user['profile_photo'] ?>" alt="<?= __('profile_photo') ?>" class="rounded-circle shadow-lg bg-white" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid var(--bs-body-bg); transition: transform 0.3s ease;">
                    <?php else: ?>
                        <div class="bg-white text-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center mx-auto" style="width: 140px; height: 140px; font-size: 4rem; border: 4px solid var(--bs-body-bg);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($isOwnProfile): ?>
                    <form action="<?= BASE_URL ?>/profile/update-photo" method="POST" enctype="multipart/form-data" id="profilePhotoForm">
                        <label for="profile_photo_input" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; cursor: pointer; border: 3px solid var(--bs-body-bg);" title="Profil Fotoğrafını Değiştir">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" id="profile_photo_input" name="profile_photo" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="document.getElementById('profilePhotoForm').submit();">
                    </form>
                    <?php endif; ?>
                </div>

                <h2 class="fw-bold mb-1" style="color: var(--primary-color); text-shadow: 0 2px 4px rgba(0,0,0,0.05);"><?= htmlspecialchars($user['username']) ?></h2>
                
                <?php if (!empty($user['bio'])): ?>
                    <p class="text-secondary mt-2 mb-3 px-3 mx-auto" style="max-width: 600px; font-size: 0.95rem; line-height: 1.5;">
                        <i class="bi bi-quote fs-5 text-muted"></i><?= nl2br(htmlspecialchars($user['bio'])) ?><i class="bi bi-quote fs-5 text-muted"></i>
                    </p>
                <?php endif; ?>

                <?php if ($isOwnProfile): ?>
                    <p class="text-muted mb-3"><i class="bi bi-envelope-fill me-1"></i> <?= htmlspecialchars($user['email']) ?></p>
                <?php endif; ?>
                
                <div class="d-flex justify-content-center gap-3 flex-wrap mb-3">
                    <span class="badge badge-glass fs-6 px-4 py-2 rounded-pill">
                        <i class="bi bi-person-badge me-1"></i> 
                        <?= $user['role'] === 'student' ? __('student') : ($user['role'] === 'teacher' ? __('teacher') : __('admin')) ?>
                    </span>
                    <span class="badge badge-glass fs-6 px-4 py-2 rounded-pill">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> <?= (int)($user['points'] ?? 0) ?> Puan
                    </span>
                    <span class="badge badge-glass fs-6 px-4 py-2 rounded-pill">
                        <i class="bi bi-calendar3 me-1"></i> <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                    </span>
                </div>

                <!-- Başka Bir Kullanıcıyı İnceliyorsak: Mesaj Gönder Butonu -->
                <?php if (!$isOwnProfile && isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/messages/<?= $user['id'] ?>" class="btn btn-gradient rounded-pill px-4 py-2 shadow-sm text-white fw-bold">
                            <i class="bi bi-chat-dots-fill me-2"></i>Mesaj Gönder
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Kazanılan Rozetler (Özet) -->
                <?php if (!empty($userBadges)): ?>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                        <?php foreach($userBadges as $badge): ?>
                            <span class="badge bg-light border <?= $badge['color'] ?> shadow-sm px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;" title="<?= htmlspecialchars($badge['description']) ?> (Kazanıldı: <?= date('d.m.Y', strtotime($badge['awarded_at'])) ?>)">
                                <i class="bi <?= $badge['icon'] ?> me-1"></i> <?= htmlspecialchars($badge['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alt Kısım: 4 Temel Metrik Kartı -->
        <div class="row g-3 mb-5">
            <!-- Soru Sayısı -->
            <div class="col-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center h-100 hover-lift">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background: rgba(79, 70, 229, 0.1); color: var(--primary-color);">
                        <i class="bi bi-question-circle fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: var(--primary-color);"><?= count($userQuestions) ?></h3>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= __('questions_asked') ?></small>
                </div>
            </div>
            <!-- Çözüm & Başarı Oranı -->
            <div class="col-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center h-100 hover-lift">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-success"><?= $userAnswerCount ?> <span class="fs-6 text-muted fw-normal">(%<?= $successRate ?>)</span></h3>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Çözüm / Başarı</small>
                </div>
            </div>
            <!-- Beğeni Sayısı -->
            <div class="col-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center h-100 hover-lift">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="bi bi-heart-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger"><?= $totalLikes ?></h3>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= __('likes_received') ?></small>
                </div>
            </div>
            <!-- Pomodoro Odaklanma -->
            <div class="col-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center h-100 hover-lift">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="bi bi-stopwatch-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning"><?= (int)($pomodoroStats['total_minutes'] ?? 0) ?> <span class="fs-6 text-muted fw-normal">dk</span></h3>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Odaklanma Süresi</small>
                </div>
            </div>
        </div>

        <!-- Sekmeler -->
        <ul class="nav nav-pills mb-4 gap-2 flex-wrap" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold" id="stats-tab" data-bs-toggle="pill" data-bs-target="#stats" type="button" role="tab">
                    <i class="bi bi-graph-up-arrow me-1"></i> Gelişim Karnesi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="questions-tab" data-bs-toggle="pill" data-bs-target="#questions" type="button" role="tab">
                    <i class="bi bi-list-ul me-1"></i> <?= $isOwnProfile ? __('my_questions') : 'Soruları' ?> (<?= count($userQuestions) ?>)
                </button>
            </li>
            <?php if ($isOwnProfile): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="bookmarks-tab" data-bs-toggle="pill" data-bs-target="#bookmarks" type="button" role="tab">
                    <i class="bi bi-bookmark-heart-fill me-1"></i> Favorilerim (<?= count($bookmarkedQuestions) ?>)
                </button>
            </li>
            <?php endif; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="badges-tab" data-bs-toggle="pill" data-bs-target="#badges" type="button" role="tab">
                    <i class="bi bi-trophy-fill me-1"></i> Rozetler (<?= count($userBadges) ?>/<?= count($allBadges) ?>)
                </button>
            </li>
            <?php if ($isOwnProfile): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="edit-tab" data-bs-toggle="pill" data-bs-target="#edit" type="button" role="tab">
                    <i class="bi bi-gear-fill me-1"></i> Profili Düzenle
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="profileTabsContent">
            
            <!-- 1. GELİŞİM KARNESİ (Chart.js İstatistik Paneli) -->
            <div class="tab-pane fade show active" id="stats" role="tabpanel">
                <div class="row g-4">
                    <!-- Ders Dağılım Grafiği -->
                    <div class="col-lg-6">
                        <div class="glass-effect p-4 rounded-4 h-100">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-pie-chart-fill text-primary me-2"></i> Derslere Göre Dağılım
                            </h5>
                            <?php if (!empty($subjectStats)): ?>
                                <div style="position: relative; height: 280px;">
                                    <canvas id="subjectChart"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-bar-chart fs-1 opacity-50 d-block mb-2"></i>
                                    Henüz yeterli soru/cevap verisi bulunmuyor.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Aktivite ve Başarı Özeti -->
                    <div class="col-lg-6">
                        <div class="glass-effect p-4 rounded-4 h-100">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-award-fill text-warning me-2"></i> Çözüm ve Başarı Analizi
                            </h5>
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold">En İyi Çözüm Başarı Oranı</span>
                                        <span class="small fw-bold text-success">%<?= $successRate ?></span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $successRate ?>%;" aria-valuenow="<?= $successRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="p-3 rounded-3 mt-2" style="background: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.15);">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Kabul Edilen En İyi Çözümler:</span>
                                        <span class="fw-bold text-success fs-6"><i class="bi bi-check-circle-fill me-1"></i><?= $bestAnswerCount ?> adet</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Bugünkü Pomodoro Odaklanma:</span>
                                        <span class="fw-bold text-warning fs-6"><i class="bi bi-clock-history me-1"></i><?= (int)($pomodoroStats['today_minutes'] ?? 0) ?> dk (<?= (int)($pomodoroStats['today_sessions'] ?? 0) ?> seans)</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted small">Toplam Topluluk Beğenisi:</span>
                                        <span class="fw-bold text-danger fs-6"><i class="bi bi-heart-fill me-1"></i><?= $totalLikes ?> beğeni</span>
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 rounded-3 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0"></i>
                                    <div>Sorulara kaliteli ve açıklayıcı çözümler üreterek en iyi çözüm rozetlerini ve bonus puanları toplayabilirsiniz!</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. SORULAR TAB -->
            <div class="tab-pane fade" id="questions" role="tabpanel">
                <?php if (empty($userQuestions)): ?>
                    <div class="glass-effect p-5 rounded-4 text-center">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                        <h5 class="fw-bold mt-3 text-muted"><?= __('no_questions_yet') ?></h5>
                        <?php if ($isOwnProfile): ?>
                        <p class="text-muted mb-4"><?= __('start_asking_info') ?></p>
                        <a href="<?= BASE_URL ?>/question/create" class="btn btn-gradient rounded-pill px-4 py-2 fw-bold">
                            <i class="bi bi-plus-lg"></i> <?= __('ask_question_btn') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($userQuestions as $q): ?>
                            <div class="glass-effect p-4 rounded-4 hover-lift transition-all">
                                <div class="row align-items-center">
                                    <div class="col-md-8 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge badge-glass"><i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($q['subject']) ?></span>
                                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></small>
                                        </div>
                                        <h5 class="fw-bold mb-1 text-truncate">
                                            <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="text-decoration-none" style="color: var(--bs-body-color); transition: color 0.2s;">
                                                <?= htmlspecialchars($q['title']) ?>
                                            </a>
                                        </h5>
                                        <div class="d-flex gap-4 text-muted small mt-2 fw-medium">
                                            <span><i class="bi bi-chat-dots-fill text-primary"></i> <?= $q['answer_count'] ?> <?= __('comments') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end align-items-center">
                                        <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="btn btn-glass-outline btn-sm rounded-pill px-4 fw-bold"><?= __('view') ?></a>
                                        <?php if ($isOwnProfile || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                                        <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>/delete" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;" onclick="return confirm('<?= __('delete_confirm') ?>');" title="Sil">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 3. FAVORİLERİM TAB (Yalnızca Kendi Profili) -->
            <?php if ($isOwnProfile): ?>
            <div class="tab-pane fade" id="bookmarks" role="tabpanel">
                <?php if (empty($bookmarkedQuestions)): ?>
                    <div class="glass-effect p-5 rounded-4 text-center">
                        <i class="bi bi-bookmark-x text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                        <h5 class="fw-bold mt-3 text-muted">Henüz favoriye alınmış soru yok.</h5>
                        <p class="text-muted mb-4">Beğendiğiniz soruları favorilerinize ekleyerek daha sonra kolayca bulabilir ve tek tıkla Deneme Kitapçığı yapabilirsiniz.</p>
                        <a href="<?= BASE_URL ?>/" class="btn btn-gradient rounded-pill px-4 py-2 fw-bold">
                            <i class="bi bi-search"></i> Soruları Keşfet
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small"><?= count($bookmarkedQuestions) ?> favori soru kayıtlı</span>
                        <a href="<?= BASE_URL ?>/exam/booklet?source=bookmarks" target="_blank" class="btn btn-sm btn-gradient rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Favorileri Deneme Kitapçığı Yap (PDF)
                        </a>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($bookmarkedQuestions as $q): ?>
                            <div class="glass-effect p-4 rounded-4 hover-lift transition-all">
                                <div class="row align-items-center">
                                    <div class="col-md-8 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge badge-glass"><i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($q['subject']) ?></span>
                                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></small>
                                        </div>
                                        <h5 class="fw-bold mb-1 text-truncate">
                                            <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="text-decoration-none" style="color: var(--bs-body-color); transition: color 0.2s;">
                                                <?= htmlspecialchars($q['title']) ?>
                                            </a>
                                        </h5>
                                        <div class="d-flex gap-4 text-muted small mt-2 fw-medium">
                                            <span><i class="bi bi-person-fill text-primary"></i> <?= htmlspecialchars($q['username']) ?></span>
                                            <span><i class="bi bi-chat-dots-fill text-primary"></i> <?= $q['answer_count'] ?> <?= __('comments') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end align-items-center">
                                        <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="btn btn-glass-outline btn-sm rounded-pill px-4 fw-bold"><?= __('view') ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- 4. BAŞARI ROZETLERİ (Tam Vitrin) -->
            <div class="tab-pane fade" id="badges" role="tabpanel">
                <div class="row g-3">
                    <?php foreach ($allBadges as $b): ?>
                        <div class="col-md-6">
                            <div class="glass-effect p-3 rounded-4 h-100 d-flex align-items-center gap-3 transition-all <?= $b['is_unlocked'] ? 'border-primary-subtle' : 'opacity-75' ?>" style="<?= $b['is_unlocked'] ? 'box-shadow: 0 4px 15px rgba(79, 70, 229, 0.1);' : 'filter: grayscale(80%);' ?>">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 55px; height: 55px; background: <?= $b['is_unlocked'] ? 'rgba(79, 70, 229, 0.15)' : 'rgba(108, 117, 125, 0.15)' ?>; font-size: 1.6rem;">
                                    <i class="bi <?= $b['icon'] ?> <?= $b['is_unlocked'] ? $b['color'] : 'text-muted' ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0 <?= $b['is_unlocked'] ? 'text-primary' : 'text-muted' ?>">
                                            <?= htmlspecialchars($b['name']) ?>
                                        </h6>
                                        <?php if ($b['is_unlocked']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                                <i class="bi bi-check-circle-fill me-1"></i> Açıldı (<?= date('d.m.Y', strtotime($b['awarded_at'])) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill small">
                                                <i class="bi bi-lock-fill me-1"></i> Kilitli
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($b['description']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5. PROFİLİ DÜZENLE TAB (Yalnızca Kendi Profili) -->
            <?php if ($isOwnProfile): ?>
            <div class="tab-pane fade" id="edit" role="tabpanel">
                <div class="glass-effect p-4 p-md-5 rounded-4 shadow-sm">
                    <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-person-gear me-2"></i>Profil ve Hesap Bilgilerini Güncelle</h5>
                    
                    <form action="<?= BASE_URL ?>/profile/update" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kullanıcı Adı</label>
                            <input type="text" class="form-control form-control-glass" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <small class="text-muted">Kullanıcı adı sistem güvenliği nedeniyle sabittir.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">E-posta Adresi</label>
                            <input type="email" class="form-control form-control-glass" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="form-label fw-bold">Hakkımda / Biyografi</label>
                            <textarea name="bio" id="bio" rows="4" class="form-control form-control-glass" placeholder="Kendinizden, hedeflerinizden veya çalışma alanınızdan bahsedin (Örn: TYT-AYT Sayısal öğrencisi, hedef Tıp)..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            <small class="text-muted">Biyografiniz profilinizi ziyaret eden diğer öğrenci ve öğretmenler tarafından görülebilir.</small>
                        </div>

                        <hr class="my-4 opacity-25">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-shield-lock me-2"></i>Şifre Değiştir (İsteğe Bağlı)</h6>
                        <p class="text-muted small">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakabilirsiniz.</p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Mevcut Şifre</label>
                                <input type="password" name="current_password" class="form-control form-control-glass" placeholder="Mevcut şifreniz">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Yeni Şifre</label>
                                <input type="password" name="new_password" class="form-control form-control-glass" placeholder="En az 6 karakter">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="confirm_password" class="form-control form-control-glass" placeholder="Yeni şifre tekrar">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient rounded-pill px-5 py-2 fw-bold text-white shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Değişiklikleri Kaydet
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($subjectStats)): ?>
    const ctx = document.getElementById('subjectChart');
    if (ctx) {
        const labels = <?= json_encode(array_keys($subjectStats)) ?>;
        const data = <?= json_encode(array_values($subjectStats)) ?>;
        const backgroundColors = [
            '#0d6efd', '#ffc107', '#198754', '#20c997', 
            '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14'
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors.slice(0, labels.length),
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { family: 'inherit', size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
