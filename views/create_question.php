<?php ob_start(); ?>

<?php
// Show moderation notices
if (isset($_SESSION['mod_error'])) {
    $modError = $_SESSION['mod_error'];
    unset($_SESSION['mod_error']);
}
if (isset($_SESSION['mod_notice'])) {
    $modNotice = $_SESSION['mod_notice'];
    unset($_SESSION['mod_notice']);
}
?>

<?php if (isset($modError)): ?>
<div class="alert d-flex align-items-start gap-3 mb-4" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:16px;" role="alert">
    <i class="bi bi-shield-x fs-4 text-danger mt-1 flex-shrink-0"></i>
    <div>
        <div class="fw-bold text-danger mb-1"><?= __('moderation_rejected_title') ?></div>
        <div class="text-muted small"><?= htmlspecialchars($modError) ?></div>
        <div class="mt-1 small"><?= __('moderation_rejected_help') ?></div>
    </div>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card glass-effect border-0 mt-5 mb-5 shadow-lg" style="border-radius: 25px;">
            <div class="card-body p-5">
                <h2 class="fw-bolder mb-5" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="bi bi-pencil-square text-primary me-2"></i><?= __('create_q_title') ?></h2>

                <form method="POST" action="<?= BASE_URL ?>/question/create" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary"><?= __('q_title_label') ?></label>
                        <input type="text" name="title" class="form-control form-control-glass" required placeholder="<?= __('q_title_placeholder') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary"><?= __('q_subject_label') ?></label>
                        <select name="subject" class="form-select form-control-glass" required>
                            <option value=""><?= __('choose_subject') ?></option>
                            <option value="TYT Matematik">TYT Matematik</option>
                            <option value="AYT Matematik">AYT Matematik</option>
                            <option value="TYT Türkçe">TYT Türkçe</option>
                            <option value="Fizik">Fizik</option>
                            <option value="Kimya">Kimya</option>
                            <option value="Biyoloji">Biyoloji</option>
                            <option value="Tarih / Coğrafya">Tarih / Coğrafya</option>
                            <option value="Diğer"><?= __('subject_other') ?></option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary"><?= __('q_desc_label') ?></label>
                        <!-- Quill Editor -->
                        <div id="editor-container" class="form-control-glass" style="height: 200px; border-radius: 0 0 15px 15px; border-top: none;"></div>
                        <input type="hidden" name="description" id="description">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary"><i class="bi bi-tags"></i> Etiketler (İsteğe bağlı)</label>
                        <input type="text" name="tags" class="form-control form-control-glass" placeholder="Örn: matematik, türev, zor soru (virgülle ayırın)">
                        <small class="text-muted">Sorunuzu daha kolay bulunabilir yapmak için etiket ekleyebilirsiniz.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold text-primary"><i class="bi bi-image"></i> <?= __('upload_image') ?></label>
                            <input type="file" name="image" class="form-control form-control-glass" accept="image/jpeg, image/png">
                            <small class="text-muted"><?= __('image_support') ?></small>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold text-primary"><i class="bi bi-file-earmark-pdf"></i> <?= __('upload_pdf') ?></label>
                            <input type="file" name="pdf" class="form-control form-control-glass" accept="application/pdf">
                            <small class="text-muted"><?= __('pdf_support') ?></small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="<?= BASE_URL ?>/" class="btn btn-glass-outline me-3 px-4 rounded-pill"><?= __('q_cancel') ?></a>
                        <button type="submit" class="btn btn-gradient px-5 rounded-pill shadow-sm"><i class="bi bi-send me-2"></i><?= __('q_submit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Initialize Quill editor -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'align': [] }],
        ['clean']                                         // remove formatting button
    ];

    var quill = new Quill('#editor-container', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: '<?= __('q_desc_placeholder') ?>',
        theme: 'snow'
    });
    
    // Style the toolbar to match glass effect
    document.querySelector('.ql-toolbar').classList.add('form-control-glass');
    document.querySelector('.ql-toolbar').style.borderRadius = '15px 15px 0 0';
    document.querySelector('.ql-toolbar').style.borderBottom = '1px solid rgba(255,255,255,0.2)';

    // Update hidden input before submit
    var form = document.querySelector('form');
    form.onsubmit = function() {
        var description = document.querySelector('input[name=description]');
        description.value = quill.root.innerHTML;
    };
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
