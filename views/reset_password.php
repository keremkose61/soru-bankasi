<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mt-5 glass-effect" style="border-radius: 20px;">
            <div class="card-body p-4">
                <h3 class="text-center fw-bold mb-4">Yeni Şifre Belirleme</h3>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="border-radius: 15px;"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string)$error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="border-radius: 15px;"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string)$success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                <?php else: ?>
                    <p class="text-center text-muted mb-4">Lütfen yeni şifrenizi girin. Şifrenizin en az 6 karakter olmasına dikkat edin.</p>
                    <form method="POST" action="<?= BASE_URL ?>/reset-password?token=<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Yeni Şifre</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required placeholder="••••••••" minlength="6">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold">Yeni Şifre (Tekrar)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password_confirm" class="form-control" required placeholder="••••••••" minlength="6">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-gradient w-100 py-2 fw-bold rounded-pill">Şifreyi Güncelle</button>
                    </form>
                <?php endif; ?>
                
                <?php if (isset($error) && strpos($error, 'Geçersiz') !== false): ?>
                <div class="text-center mt-4">
                    <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none text-muted fw-bold">Yeni Link İste</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
