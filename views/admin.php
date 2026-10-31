<?php ob_start(); ?>

<div class="row">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="fw-bold mb-0"><i class="bi bi-shield-lock text-warning"></i> <?= __('admin_dashboard') ?></h2>
        <a href="<?= BASE_URL ?>/admin/moderation" class="btn btn-outline-primary rounded-pill px-4 shadow-sm position-relative">
            <i class="bi bi-shield-check me-1"></i> <?= __('moderation_queue') ?>
            <?php if (!empty($reviewCount) && $reviewCount > 0): ?>
                <span class="badge bg-danger ms-2 rounded-pill"><?= $reviewCount ?></span>
            <?php endif; ?>
        </button></form>
    </div>

    <!-- Kullanıcılar -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-bottom-0 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill me-2 text-primary"></i><?= __('registered_users') ?> (<?= count($users) ?>)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('id') ?></th>
                                <th><?= __('username_label') ?></th>
                                <th><?= __('role') ?></th>
                                <th class="text-end"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/profile/<?= $u['id'] ?>" class="text-decoration-none fw-semibold text-primary" title="Kullanıcı Profilini Görüntüle">
                                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($u['username']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill me-1"></i><?= __('admin') ?></span>
                                    <?php elseif ($u['role'] === 'teacher'): ?>
                                        <span class="badge bg-info text-white"><i class="bi bi-mortarboard-fill me-1"></i><?= __('teacher') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-person-fill me-1"></i><?= __('student') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($u['role'] !== 'admin'): ?>
                                        <!-- Rol Değiştirme Butonu -->
                                        <?php if ($u['role'] === 'student'): ?>
                                            <form action="<?= BASE_URL ?>/admin/toggle_role/<?= $u['id'] ?>/teacher" method="POST" class="d-inline" onsubmit="return confirm('Bu kullanıcıya Öğretmen rolü atansın mı?');"><button type="submit" class="btn btn-sm btn-outline-info" title="Öğretmen Yetkisi Ver">
                                                <i class="bi bi-mortarboard"></i>
                                            </button></form>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>/admin/toggle_role/<?= $u['id'] ?>/student" method="POST" class="d-inline" onsubmit="return confirm('Bu kullanıcı Öğrenci rolüne çekilsin mi?');"><button type="submit" class="btn btn-sm btn-outline-secondary" title="Öğrenciye Çevir">
                                                <i class="bi bi-person"></i>
                                            </button></form>
                                        <?php endif; ?>

                                        <?php if(isset($u['is_banned']) && $u['is_banned']): ?>
                                            <form action="<?= BASE_URL ?>/admin/toggle_ban/<?= $u['id'] ?>/0" method="POST" class="d-inline"><button type="submit" class="btn btn-sm btn-success" title="Engeli Kaldır"><i class="bi bi-unlock"></i></button></form>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>/admin/toggle_ban/<?= $u['id'] ?>/1" method="POST" class="d-inline" onsubmit="return confirm('Kullanıcı askıya alınsın mı?');"><button type="submit" class="btn btn-sm btn-warning" title="Kullanıcıyı Banla"><i class="bi bi-ban"></i></button></form>
                                        <?php endif; ?>
                                    <form action="<?= BASE_URL ?>/admin/delete_user/<?= (int)$u['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('delete_user_confirm') ?>');"><button type="submit" class="btn btn-sm btn-danger" title="Kullanıcıyı Sil"><i class="bi bi-trash"></i></button></form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sorular -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-bottom-0 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-question-diamond-fill me-2 text-primary"></i><?= __('all_questions') ?> (<?= count($questions) ?>)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('id') ?></th>
                                <th><?= __('title') ?></th>
                                <th><?= __('asker') ?></th>
                                <th class="text-end"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($questions as $q): ?>
                            <tr>
                                <td><?= $q['id'] ?></td>
                                <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($q['title']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/profile/<?= $q['user_id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($q['username']) ?>
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="btn btn-sm btn-info text-white" title="Görüntüle"><i class="bi bi-eye"></i></a>
                                    <form action="<?= BASE_URL ?>/admin/delete_question/<?= (int)$q['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('delete_confirm') ?>');"><button type="submit" class="btn btn-sm btn-danger" title="Sil"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="reports">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom-0 fw-bold bg-danger text-white">
                <i class="bi bi-flag-fill me-2"></i> Gelen Şikayetler (<?= count($reports ?? []) ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Bildiren</th>
                                <th>Tip</th>
                                <th>İçerik ID</th>
                                <th>Sebep</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($reports)): foreach($reports as $r): ?>
                            <tr class="<?= $r['status'] === 'pending' ? 'table-warning' : '' ?>">
                                <td><?= $r['id'] ?></td>
                                <td><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['username']) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst($r['item_type']) ?></span></td>
                                <td>
                                    <?php if($r['item_type'] === 'question'): ?>
                                        <a href="<?= BASE_URL ?>/question/<?= $r['item_id'] ?>" target="_blank">Soru #<?= $r['item_id'] ?></a>
                                    <?php elseif($r['item_type'] === 'answer'): ?>
                                        Cevap #<?= $r['item_id'] ?>
                                    <?php else: ?>
                                        Mesaj #<?= $r['item_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($r['reason']) ?></td>
                                <td>
                                    <?php if($r['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Bekliyor</span>
                                    <?php elseif($r['status'] === 'resolved'): ?>
                                        <span class="badge bg-success">Çözüldü</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark">Reddedildi</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                                <td class="text-end">
                                    <?php if($r['status'] === 'pending'): ?>
                                        <form action="<?= BASE_URL ?>/admin/report/<?= $r['id'] ?>/resolved" method="POST" class="d-inline"><button type="submit" class="btn btn-sm btn-success" title="Çözüldü İşaretle"><i class="bi bi-check-circle"></i></button></form>
                                        <form action="<?= BASE_URL ?>/admin/report/<?= $r['id'] ?>/dismissed" method="POST" class="d-inline"><button type="submit" class="btn btn-sm btn-secondary" title="Reddet"><i class="bi bi-x-circle"></i></button></form>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="bi bi-check2-all"></i> İşlem Yapıldı</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Şikayet bulunmuyor.</td>
                            </tr>
                            <?php endif; ?>
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
