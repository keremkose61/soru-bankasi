<?php ob_start(); ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card glass-effect mb-4 border-0 shadow-lg" style="border-radius: 25px;">
            <div class="card-body p-5">
                <h1 class="card-title fw-bolder mb-4 text-center" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="bi bi-trophy-fill text-warning"></i> Liderlik Tablosu
                </h1>
                <p class="text-center text-muted mb-5">Soru sorarak, cevap yazarak ve en iyi cevap seçilerek puan toplayın!</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 10%;">#</th>
                                <th style="width: 50%;">Kullanıcı</th>
                                <th class="text-center" style="width: 20%;">Rol</th>
                                <th class="text-center" style="width: 20%;">Puan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach($topUsers as $u): ?>
                                <tr class="<?= $rank <= 3 ? 'fw-bold' : '' ?>">
                                    <td class="text-center">
                                        <?php if($rank == 1): ?>
                                            <span class="badge bg-warning fs-5 p-2 rounded-circle shadow-sm"><i class="bi bi-trophy-fill"></i></span>
                                        <?php elseif($rank == 2): ?>
                                            <span class="badge bg-secondary fs-5 p-2 rounded-circle shadow-sm"><i class="bi bi-award-fill"></i></span>
                                        <?php elseif($rank == 3): ?>
                                            <span class="badge fs-5 p-2 rounded-circle shadow-sm text-white" style="background-color:#cd7f32;"><i class="bi bi-award-fill"></i></span>
                                        <?php else: ?>
                                            <span class="fs-5 text-muted"><?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/profile/<?= $u['id'] ?>" class="d-flex align-items-center text-decoration-none" style="color: inherit;" title="Profili Görüntüle">
                                            <?php if(!empty($u['profile_photo'])): ?>
                                                <img src="<?= BASE_URL . htmlspecialchars($u['profile_photo']) ?>" class="rounded-circle me-3 object-fit-cover shadow-sm" width="45" height="45">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 shadow-sm text-primary fw-bold" style="width: 45px; height: 45px;">
                                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="fs-5 fw-bold text-primary"><?= htmlspecialchars($u['username']) ?></span>
                                                <small class="d-block text-muted" style="font-size: 0.75rem;">Profili Gör & Mesaj At &rarr;</small>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $u['role'] === 'teacher' ? 'bg-info text-white' : 'bg-secondary' ?>"><?= $u['role'] === 'teacher' ? 'Öğretmen' : 'Öğrenci' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-5 text-success fw-bolder"><?= $u['points'] ?> Pts</span>
                                    </td>
                                </tr>
                            <?php $rank++; endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
