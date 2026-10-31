<?php ob_start(); ?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    <?= __('moderation_queue') ?>
                </h2>
                <p class="text-muted mb-0"><?= __('moderation_queue_subtitle') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-glass-outline rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i><?= __('back_to_admin') ?>
            </a>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-5">
    <div class="col-6 col-md-3">
        <div class="card glass-effect border-0 text-center p-3" style="border-radius:18px;">
            <div class="fs-2 fw-bold text-warning"><?= count($reviewQuestions) + count($reviewAnswers) ?></div>
            <div class="text-muted small"><?= __('pending_review') ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-effect border-0 text-center p-3" style="border-radius:18px;">
            <div class="fs-2 fw-bold text-danger"><?= count($rejectedQuestions) + count($rejectedAnswers) ?></div>
            <div class="text-muted small"><?= __('rejected_content') ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-effect border-0 text-center p-3" style="border-radius:18px;">
            <div class="fs-2 fw-bold text-warning"><?= count($reviewQuestions) ?></div>
            <div class="text-muted small"><?= __('questions_under_review') ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-effect border-0 text-center p-3" style="border-radius:18px;">
            <div class="fs-2 fw-bold text-warning"><?= count($reviewAnswers) ?></div>
            <div class="text-muted small"><?= __('answers_under_review') ?></div>
        </div>
    </div>
</div>

<!-- Bootstrap Tabs -->
<ul class="nav nav-pills mb-4 gap-2" id="moderationTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="btn btn-glass-outline rounded-pill px-4 active" id="review-tab" data-bs-toggle="pill" data-bs-target="#reviewPanel" type="button" role="tab">
            <i class="bi bi-hourglass-split me-2 text-warning"></i>
            <?= __('pending_review') ?>
            <?php $totalReview = count($reviewQuestions) + count($reviewAnswers); if ($totalReview > 0): ?>
                <span class="badge bg-warning text-dark ms-2"><?= $totalReview ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="btn btn-glass-outline rounded-pill px-4" id="rejected-tab" data-bs-toggle="pill" data-bs-target="#rejectedPanel" type="button" role="tab">
            <i class="bi bi-x-octagon me-2 text-danger"></i>
            <?= __('rejected_content') ?>
            <?php $totalRejected = count($rejectedQuestions) + count($rejectedAnswers); if ($totalRejected > 0): ?>
                <span class="badge bg-danger ms-2"><?= $totalRejected ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content" id="moderationTabContent">

    <!-- REVIEW TAB -->
    <div class="tab-pane fade show active" id="reviewPanel" role="tabpanel">

        <?php if (empty($reviewQuestions) && empty($reviewAnswers)): ?>
            <div class="text-center py-5 glass-effect" style="border-radius:20px;">
                <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                <p class="mt-3 fs-5 fw-semibold"><?= __('no_items_in_queue') ?></p>
            </div>
        <?php endif; ?>

        <!-- Review Questions -->
        <?php if (!empty($reviewQuestions)): ?>
        <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-2 text-primary"></i><?= __('questions_under_review') ?> (<?= count($reviewQuestions) ?>)</h5>
        <?php foreach ($reviewQuestions as $q): ?>
            <?php
                $log = null;
                // Fetch latest moderation log for this question
                try {
                    require_once __DIR__ . '/../models/Database.php';
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("SELECT * FROM moderation_log WHERE content_type='question' AND content_id=? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$q['id']]);
                    $log = $stmt->fetch();
                } catch(Exception $e) {}
            ?>
            <div class="card glass-effect border-0 mb-3 hover-lift" style="border-radius:18px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge" style="background: rgba(245,158,11,0.15); color:#d97706; border:1px solid rgba(245,158,11,0.3);">
                                    <i class="bi bi-hourglass-split me-1"></i><?= __('under_review') ?>
                                </span>
                                <span class="badge bg-secondary"><?= __('question') ?></span>
                                <?php if ($log): ?>
                                    <span class="badge" style="background:rgba(79,70,229,0.1);color:#4F46E5;">
                                        <?= __('moderation_confidence') ?>: <?= $log['confidence'] ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($q['title']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($q['username']) ?> &bull;
                                <i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?>
                            </p>
                            <p class="mb-2 small" style="white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($q['description'] ?? '', 0, 200)) ?><?= mb_strlen($q['description'] ?? '') > 200 ? '...' : '' ?></p>
                            <?php if ($log && !empty($log['reason'])): ?>
                                <div class="alert py-2 px-3 small mb-0" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:10px;">
                                    <i class="bi bi-robot me-1"></i><strong><?= __('moderation_reason') ?>:</strong> <?= htmlspecialchars($log['reason']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-column gap-2" style="min-width:140px;">
                            <a href="<?= BASE_URL ?>/question/<?= $q['id'] ?>" class="btn btn-sm btn-glass-outline rounded-pill" target="_blank">
                                <i class="bi bi-eye me-1"></i><?= __('view') ?>
                            </a>
                            <form action="<?= BASE_URL ?>/admin/moderation/approve/question/<?= $q['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_approve') ?>');"><button type="submit" class="btn btn-sm rounded-pill text-white fw-semibold" style="background:linear-gradient(135deg,#10b981,#059669);">
                                <i class="bi bi-check-lg me-1"></i><?= __('moderation_approve') ?>
                            </button></form>
                            <form action="<?= BASE_URL ?>/admin/moderation/reject/question/<?= $q['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_reject') ?>');"><button type="submit" class="btn btn-sm btn-danger rounded-pill fw-semibold">
                                <i class="bi bi-x-lg me-1"></i><?= __('moderation_reject') ?>
                            </button></form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Review Answers -->
        <?php if (!empty($reviewAnswers)): ?>
        <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-chat-dots me-2 text-info"></i><?= __('answers_under_review') ?> (<?= count($reviewAnswers) ?>)</h5>
        <?php foreach ($reviewAnswers as $a): ?>
            <?php
                $log = null;
                try {
                    $stmt = $db->prepare("SELECT * FROM moderation_log WHERE content_type='answer' AND content_id=? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$a['id']]);
                    $log = $stmt->fetch();
                } catch(Exception $e) {}
            ?>
            <div class="card glass-effect border-0 mb-3 hover-lift" style="border-radius:18px; border-left: 4px solid #06b6d4 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge" style="background:rgba(245,158,11,0.15);color:#d97706;border:1px solid rgba(245,158,11,0.3);">
                                    <i class="bi bi-hourglass-split me-1"></i><?= __('under_review') ?>
                                </span>
                                <span class="badge bg-info text-dark"><?= __('answer') ?></span>
                                <?php if ($log): ?>
                                    <span class="badge" style="background:rgba(79,70,229,0.1);color:#4F46E5;">
                                        <?= __('moderation_confidence') ?>: <?= $log['confidence'] ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-link-45deg me-1"></i><?= __('question') ?>: 
                                <a href="<?= BASE_URL ?>/question/<?= $a['question_id'] ?>" target="_blank" class="text-primary">
                                    <?= htmlspecialchars(mb_substr($a['question_title'], 0, 60)) ?>
                                </a>
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($a['username']) ?> &bull;
                                <i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($a['created_at'])) ?>
                            </p>
                            <p class="mb-2 small" style="white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($a['content'], 0, 250)) ?><?= mb_strlen($a['content']) > 250 ? '...' : '' ?></p>
                            <?php if ($log && !empty($log['reason'])): ?>
                                <div class="alert py-2 px-3 small mb-0" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:10px;">
                                    <i class="bi bi-robot me-1"></i><strong><?= __('moderation_reason') ?>:</strong> <?= htmlspecialchars($log['reason']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-column gap-2" style="min-width:140px;">
                            <a href="<?= BASE_URL ?>/question/<?= $a['question_id'] ?>" class="btn btn-sm btn-glass-outline rounded-pill" target="_blank">
                                <i class="bi bi-eye me-1"></i><?= __('view') ?>
                            </a>
                            <form action="<?= BASE_URL ?>/admin/moderation/approve/answer/<?= $a['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_approve') ?>');"><button type="submit" class="btn btn-sm rounded-pill text-white fw-semibold" style="background:linear-gradient(135deg,#10b981,#059669);">
                                <i class="bi bi-check-lg me-1"></i><?= __('moderation_approve') ?>
                            </button></form>
                            <form action="<?= BASE_URL ?>/admin/moderation/reject/answer/<?= $a['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_reject') ?>');"><button type="submit" class="btn btn-sm btn-danger rounded-pill fw-semibold">
                                <i class="bi bi-x-lg me-1"></i><?= __('moderation_reject') ?>
                            </button></form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div><!-- /review tab -->

    <!-- REJECTED TAB -->
    <div class="tab-pane fade" id="rejectedPanel" role="tabpanel">
        <?php if (empty($rejectedQuestions) && empty($rejectedAnswers)): ?>
            <div class="text-center py-5 glass-effect" style="border-radius:20px;">
                <i class="bi bi-emoji-smile text-success" style="font-size:3rem;"></i>
                <p class="mt-3 fs-5 fw-semibold"><?= __('no_rejected_content') ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($rejectedQuestions)): ?>
        <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-2 text-danger"></i><?= __('rejected_questions') ?> (<?= count($rejectedQuestions) ?>)</h5>
        <?php foreach ($rejectedQuestions as $q): ?>
            <div class="card border-0 mb-3" style="border-radius:18px; background:rgba(239,68,68,0.06); border-left:4px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <span class="badge bg-danger mb-2"><i class="bi bi-x-octagon me-1"></i><?= __('rejected') ?></span>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($q['title']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($q['username']) ?> &bull;
                                <?= date('d.m.Y H:i', strtotime($q['created_at'])) ?>
                            </p>
                            <p class="small mb-0 text-muted" style="white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($q['description'] ?? '', 0, 150)) ?></p>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <form action="<?= BASE_URL ?>/admin/moderation/approve/question/<?= $q['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_approve') ?>');"><button type="submit" class="btn btn-sm rounded-pill text-white fw-semibold" style="background:linear-gradient(135deg,#10b981,#059669);">
                                <i class="bi bi-check-lg me-1"></i><?= __('moderation_approve') ?>
                            </button></form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($rejectedAnswers)): ?>
        <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-chat-dots me-2 text-danger"></i><?= __('rejected_answers') ?> (<?= count($rejectedAnswers) ?>)</h5>
        <?php foreach ($rejectedAnswers as $a): ?>
            <div class="card border-0 mb-3" style="border-radius:18px; background:rgba(239,68,68,0.06); border-left:4px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <span class="badge bg-danger mb-2"><i class="bi bi-x-octagon me-1"></i><?= __('rejected') ?></span>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-link-45deg me-1"></i><?= __('question') ?>:
                                <a href="<?= BASE_URL ?>/question/<?= $a['question_id'] ?>" target="_blank" class="text-primary">
                                    <?= htmlspecialchars(mb_substr($a['question_title'], 0, 60)) ?>
                                </a>
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($a['username']) ?> &bull;
                                <?= date('d.m.Y H:i', strtotime($a['created_at'])) ?>
                            </p>
                            <p class="small mb-0 text-muted" style="white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($a['content'], 0, 200)) ?></p>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <form action="<?= BASE_URL ?>/admin/moderation/approve/answer/<?= $a['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= __('confirm_approve') ?>');"><button type="submit" class="btn btn-sm rounded-pill text-white fw-semibold" style="background:linear-gradient(135deg,#10b981,#059669);">
                                <i class="bi bi-check-lg me-1"></i><?= __('moderation_approve') ?>
                            </button></form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div><!-- /rejected tab -->

</div><!-- /tab-content -->

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
