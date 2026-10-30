<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mt-5 glass-effect" style="border-radius: 20px;">
            <div class="card-body p-4">
                <h3 class="text-center fw-bold mb-4">Şifremi Unuttum</h3>
                <p class="text-center text-muted mb-4">Lütfen kayıtlı olduğunuz e-posta adresinizi girin. Size bir sıfırlama bağlantısı oluşturacağız.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="border-radius: 15px;"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="border-radius: 15px;"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>/forgot-password">
                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold">E-Posta Adresiniz</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required placeholder="ornek@mail.com">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-gradient w-100 py-2 fw-bold rounded-pill">Sıfırlama Bağlantısı Gönder</button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="<?= BASE_URL ?>/login" class="text-decoration-none text-muted fw-bold"><i class="bi bi-arrow-left me-1"></i> Giriş Sayfasına Dön</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
