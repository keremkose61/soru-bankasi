<?php ob_start(); ?>

<div class="row justify-content-center min-vh-75 align-items-center">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden my-5">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="d-inline-block p-3 rounded-circle bg-success bg-opacity-10 mb-3">
                        <i class="bi bi-person-plus text-success fs-2"></i>
                    </div>
                    <h3 class="fw-bold"><?= __('register_title') ?></h3>
                    <p class="text-muted small"><?= __('register_subtitle') ?></p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= htmlspecialchars((string)$error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold small"><?= __('username_label') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0" required placeholder="<?= __('username_placeholder') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold small"><?= __('email_label') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0" required placeholder="<?= __('email_placeholder') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small"><?= __('password_label') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" minlength="6" class="form-control bg-light border-start-0" required placeholder="<?= __('password_placeholder') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small mb-2"><?= __('account_type') ?></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleStudent" value="student" checked autocomplete="off">
                                <label class="btn btn-outline-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2" for="roleStudent">
                                    <i class="bi bi-mortarboard"></i> <?= __('student') ?>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleTeacher" value="teacher" autocomplete="off">
                                <label class="btn btn-outline-secondary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2" for="roleTeacher">
                                    <i class="bi bi-person-workspace"></i> <?= __('teacher') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow-sm transition-all hover-lift">
                        <?= __('register_btn') ?> <i class="bi bi-check-circle ms-2"></i>
                    </button>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0 small"><a href="<?= BASE_URL ?>/login" class="text-primary fw-bold text-decoration-none"><?= __('already_have_account') ?></a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
