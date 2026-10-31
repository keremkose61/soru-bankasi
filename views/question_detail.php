<?php ob_start(); ?>

<?php
// Show moderation notices from session
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
        <div class="fw-bold text-danger mb-1"><?= __('moderation_answer_rejected_title') ?></div>
        <div class="text-muted small"><?= htmlspecialchars($modError) ?></div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($modNotice)): ?>
<div class="alert d-flex align-items-start gap-3 mb-4" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:16px;" role="alert">
    <i class="bi bi-hourglass-split fs-4 text-warning mt-1 flex-shrink-0"></i>
    <div>
        <div class="fw-bold" style="color:#d97706;" mb-1><?= __('moderation_review_title') ?></div>
        <div class="text-muted small"><?= __('moderation_review_body') ?></div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Soru Detayı -->
    <div class="col-lg-8 mx-auto">
        <div class="card glass-effect mb-4 position-relative border-0 shadow-lg" style="border-radius: 25px;">
            <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $question['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'))): ?>
                <form action="<?= BASE_URL ?>/question/<?= (int)$question['id'] ?>/delete" method="POST" class="position-absolute top-0 end-0 m-3 z-3" onsubmit="return confirm('<?= __('delete_confirm') ?>');"><button type="submit" class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash"></i> <?= __('delete') ?></button></form>
            <?php endif; ?>
            <div class="card-body p-5">
                <?php
                $isSolved = false;
                foreach($answers as $a) {
                    if (isset($a['is_best_answer']) && $a['is_best_answer']) {
                        $isSolved = true; break;
                    }
                }
                ?>
                <h1 class="card-title fw-bolder mb-4" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?php if($isSolved): ?>
                        <span class="badge bg-success me-2 fs-6 align-middle"><i class="bi bi-check-circle"></i> Çözüldü</span>
                    <?php endif; ?>
                    <?= htmlspecialchars($question['title']) ?>
                </h1>
                
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge badge-glass px-3 py-2 fs-6"><i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($question['subject']) ?></span>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-glass-outline btn-sm rounded-circle shadow-sm ms-auto" id="bookmarkBtn" data-id="<?= $question['id'] ?>" style="width: 40px; height: 40px;" title="<?= $isBookmarked ? 'Favorilerden Çıkar' : 'Favorilere Ekle' ?>">
                            <i class="bi <?= $isBookmarked ? 'bi-bookmark-fill text-primary' : 'bi-bookmark' ?> fs-5"></i>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex align-items-center mb-4 flex-wrap gap-2 text-muted">
                    <a href="<?= BASE_URL ?>/profile/<?= $question['user_id'] ?>" class="text-decoration-none fw-bold text-primary d-inline-flex align-items-center me-3" title="Kullanıcı Profilini Görüntüle">
                        <i class="bi bi-person-circle me-1 fs-5"></i> <?= htmlspecialchars($question['username']) ?>
                    </a>
                    <span class="me-3"><i class="bi bi-calendar3"></i> <?= date('d.m.Y H:i', strtotime($question['created_at'])) ?></span>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $question['user_id'] && $_SESSION['role'] !== 'admin'): ?>
                        <a href="<?= BASE_URL ?>/messages/<?= $question['user_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" title="Soruyu sorana özel mesaj gönder">
                            <i class="bi bi-chat-dots-fill me-1"></i> Mesaj Gönder
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($tags)): ?>
                    <div class="mb-4">
                        <?php foreach($tags as $tag): ?>
                            <a href="<?= BASE_URL ?>/?q=<?= urlencode('#' . $tag['name']) ?>" class="badge bg-light text-primary border border-primary-subtle me-1 rounded-pill px-3 py-2 fw-normal text-decoration-none" style="font-size: 0.9rem;">#<?= htmlspecialchars($tag['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="card-text fs-5 mb-4" style="line-height: 1.6;">
                    <?= strip_tags($question['description'], '<b><i><u><s><strong><em><p><br><ul><li><ol><h1><h2><h3><h4><h5><h6><span><pre><code><blockquote>') ?>
                </div>

                <?php if (!empty($question['image_path'])): ?>
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL . $question['image_path'] ?>" class="img-fluid rounded shadow-sm border" alt="<?= __('question_image') ?>">
                    </div>
                <?php endif; ?>

                <?php if (!empty($question['pdf_path'])): ?>
                    <div class="mt-4">
                        <a href="<?= BASE_URL . $question['pdf_path'] ?>" target="_blank" class="btn btn-glass-outline rounded-pill px-4"><i class="bi bi-file-earmark-pdf me-2"></i><?= __('view_pdf_attachment') ?></a>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $question['user_id']): ?>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#reportModal" data-item-type="question" data-item-id="<?= $question['id'] ?>">
                            <i class="bi bi-flag"></i> Bildir
                        </button>
                    </div>
                <?php endif; ?>


            </div>
        </div>

        <!-- Çözümler ve Yorumlar -->
        <h4 class="fw-bold mb-4 pb-3" style="border-bottom: 2px solid rgba(79, 70, 229, 0.2);"><?= __('solutions_and_comments') ?> (<?= count($answers) ?>)</h4>

        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="glass-effect p-4 mb-5 transition-all">
                <form method="POST" action="<?= BASE_URL ?>/question/<?= $question['id'] ?>/answer" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary"><?= __('your_solution_answer') ?></label>
                        <!-- Quill Editor -->
                        <div id="editor-container" class="form-control-glass" style="height: 150px; border-radius: 0 0 15px 15px; border-top: none;"></div>
                        <input type="hidden" name="content" id="content">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <label class="form-label fw-semibold text-muted mb-0" style="font-size:0.9rem;">
                                <i class="bi bi-image me-1"></i> <?= __('attach_solution_image') ?> <span class="text-muted fw-normal">(<?= __('optional') ?>)</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#whiteboardModal">
                                <i class="bi bi-brush me-1"></i> Çizimli Tahta (Whiteboard) Aç
                            </button>
                        </div>
                        <div class="position-relative">
                            <input type="file" name="answer_image" id="answerImageInput" class="form-control form-control-glass" accept="image/jpeg,image/png,image/webp,image/gif" style="border-radius:14px;">
                        </div>
                        
                        <!-- Standart Dosya Önizleme -->
                        <div id="answerImagePreview" class="mt-3 text-center d-none">
                            <img id="answerImagePreviewImg" src="" alt="" class="img-fluid rounded shadow-sm" style="max-height:250px; border:2px solid rgba(79,70,229,0.2); border-radius:14px;">
                            <div class="mt-2">
                                <button type="button" id="removeAnswerImage" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-x-circle me-1"></i><?= __('remove_image') ?>
                                </button>
                            </div>
                        </div>

                        <!-- Canvas Tuval Çizimi Önizleme & Gizli Input -->
                        <input type="hidden" name="canvas_drawing" id="canvasDrawingInput">
                        <div id="canvasPreviewBox" class="mt-3 p-3 rounded-4 glass-effect border border-primary-subtle text-center d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary rounded-pill px-3 py-1"><i class="bi bi-palette-fill me-1"></i> Çizimli Çözümünüz Eklendi</span>
                                <button type="button" id="removeCanvasDrawingBtn" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                                    <i class="bi bi-trash me-1"></i> Çizimi Kaldır
                                </button>
                            </div>
                            <div>
                                <img id="canvasPreviewImg" src="" class="img-fluid rounded shadow-sm" style="max-height:250px; border:2px solid rgba(79,70,229,0.2); border-radius:14px;">
                            </div>
                        </div>

                        <div class="form-text mt-1"><i class="bi bi-info-circle me-1"></i><?= __('answer_image_info') ?> veya soru üzerine çizim yapmak için Çizimli Tahta'yı kullanabilirsiniz.</div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-gradient px-5 rounded-pill shadow-sm"><i class="bi bi-send me-2"></i><?= __('submit') ?></button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning"><?= __('login_to_write_solution') ?> <a href="<?= BASE_URL ?>/login" class="alert-link"><?= __('login') ?></a>.</div>
        <?php endif; ?>

        <!-- Çözüm Listesi -->
        <?php foreach($answers as $ans): ?>
            <?php $isLiked = in_array($ans['id'], $likedAnswers ?? []); ?>
            <?php $isBest = isset($ans['is_best_answer']) && $ans['is_best_answer']; ?>
            
            <div class="card mb-4 answer-card hover-lift position-relative <?= $isBest ? 'border-success bg-success bg-opacity-10' : 'glass-effect' ?>" style="<?= $isBest ? 'border-width: 2px;' : '' ?>">
                <?php if($isBest): ?>
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success px-3 py-2 shadow-sm z-3 fs-6">
                        <i class="bi bi-check-circle me-1"></i> En İyi Cevap
                    </div>
                <?php endif; ?>
                
                <div class="card-body p-4 <?= $isBest ? 'pt-5' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= BASE_URL ?>/profile/<?= $ans['user_id'] ?>" class="text-decoration-none fw-bold d-flex align-items-center" style="color: <?= $isBest ? '#198754' : '#4F46E5' ?>; font-size: 1.05rem;" title="Profili Görüntüle">
                                <?php if(!empty($ans['profile_photo'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($ans['profile_photo']) ?>" class="rounded-circle me-2 object-fit-cover shadow-sm" width="30" height="30">
                                <?php else: ?>
                                    <i class="bi bi-person-circle me-1 fs-5"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($ans['username']) ?>
                            </a>
                            <?php if(!empty($ans['role']) && $ans['role'] === 'teacher'): ?>
                                <span class="badge bg-info text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-mortarboard-fill me-1"></i>Öğretmen</span>
                            <?php endif; ?>
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ans['user_id'] && $_SESSION['role'] !== 'admin'): ?>
                                <a href="<?= BASE_URL ?>/messages/<?= $ans['user_id'] ?>" class="btn btn-sm btn-link text-decoration-none py-0 px-1 text-muted" title="Özel Mesaj Gönder">
                                    <i class="bi bi-chat-dots"></i>
                                </button></form>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="badge badge-glass text-muted me-2"><i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($ans['created_at'])) ?></span>
                            
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $question['user_id'] && !$isBest && !$isSolved): ?>
                                <form action="<?= BASE_URL ?>/question/<?= (int)$question['id'] ?>/best-answer/<?= (int)$ans['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Bu cevabı en iyi cevap olarak işaretlemek istediğinize emin misiniz? (Geri alınamaz)');"><button type="submit" class="btn btn-sm btn-outline-success rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> En İyi Seç
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3 fs-6" style="line-height: 1.7;">
                        <?= strip_tags($ans['content'], '<b><i><u><s><strong><em><p><br><ul><li><ol><h1><h2><h3><h4><h5><h6><span><pre><code><blockquote>') ?>
                    </div>
                    
                    <?php if (!empty($ans['image_path'])): ?>
                    <div class="mb-4 text-center">
                        <a href="<?= BASE_URL . $ans['image_path'] ?>" target="_blank">
                            <img src="<?= BASE_URL . $ans['image_path'] ?>" 
                                 alt="<?= __('solution_image') ?>" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 350px; border: 2px solid rgba(79,70,229,0.15); border-radius: 16px; cursor: zoom-in;">
                        </a>
                        <div class="text-muted small mt-1"><i class="bi bi-zoom-in me-1"></i><?= __('click_to_enlarge') ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle">
                        <div>
                            <button type="button" class="btn btn-sm like-btn <?= $isLiked ? 'text-danger' : 'text-muted' ?>" data-answer-id="<?= $ans['id'] ?>" style="background:none; border:none; padding-left:0;">
                                <i class="bi <?= $isLiked ? 'bi-heart-fill' : 'bi-heart' ?> me-1 fs-6"></i>
                                <span class="like-count fw-bold"><?= (int)($ans['like_count'] ?? 0) ?></span> Beğeni
                            </button>
                        </div>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ans['user_id']): ?>
                            <div>
                                <button type="button" class="btn btn-sm text-danger" style="background:none; border:none;" data-bs-toggle="modal" data-bs-target="#reportModal" data-item-type="answer" data-item-id="<?= $ans['id'] ?>" title="Bu cevabı bildir">
                                    <i class="bi bi-flag"></i> Bildir
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Alt Yorumlar & Tartışma Bölümü -->
                    <div class="mt-3 pt-3 border-top" style="border-color: rgba(79, 70, 229, 0.1) !important;" id="answer-<?= $ans['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">
                                <i class="bi bi-chat-left-text me-1"></i> Yorumlar & Sorular (<?= count($ans['comments'] ?? []) ?>)
                            </span>
                        </div>

                        <!-- Mevcut Yorumlar -->
                        <?php if (!empty($ans['comments'])): ?>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <?php foreach ($ans['comments'] as $c): ?>
                                    <div class="p-2 px-3 rounded-3" style="background: rgba(79, 70, 229, 0.04); border-left: 3px solid var(--primary-color);">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-1">
                                                <a href="<?= BASE_URL ?>/profile/<?= $c['user_id'] ?>" class="fw-bold small text-decoration-none text-primary">
                                                    <?= htmlspecialchars($c['username']) ?>
                                                </a>
                                                <?php if (!empty($c['role']) && $c['role'] === 'teacher'): ?>
                                                    <span class="badge bg-info text-white rounded-pill" style="font-size: 0.65rem;">Öğretmen</span>
                                                <?php endif; ?>
                                                <small class="text-muted ms-1" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></small>
                                            </div>
                                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $c['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'))): ?>
                                                <form action="<?= BASE_URL ?>/question/<?= (int)$question['id'] ?>/comment/<?= (int)$c['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bu yorumu silmek istediğinize emin misiniz?');"><button type="submit" class="btn btn-link text-danger small p-0" title="Sil">
                                                    <i class="bi bi-trash"></i>
                                                </button></form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-secondary" style="line-height: 1.4;">
                                            <?= nl2br(htmlspecialchars($c['content'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Yorum Ekleme Formu -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="<?= BASE_URL ?>/question/<?= $question['id'] ?>/answer/<?= $ans['id'] ?>/comment" method="POST" class="d-flex gap-2">
                                <input type="text" name="comment_content" class="form-control form-control-sm form-control-glass rounded-pill px-3" placeholder="Bu çözüme kısa bir yorum veya soru yazın..." required style="font-size: 0.85rem;">
                                <button type="submit" class="btn btn-sm btn-gradient rounded-pill px-3 text-white fw-semibold" style="font-size: 0.85rem; white-space: nowrap;">
                                    <i class="bi bi-send-fill me-1"></i> Yorum Yap
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>


                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Floating AI Assistant Button -->
<div id="aiFloatingContainer" style="position: fixed; bottom: 30px; right: 30px; z-index: 1050; display: flex; flex-direction: column; align-items: flex-end;">
    
    <!-- AI Popup Window -->
    <div id="aiHintBox" class="bg-white shadow-lg mb-3 d-none d-flex flex-column" style="width: 380px; max-width: calc(100vw - 40px); height: 500px; max-height: calc(100vh - 120px); border-radius: 20px; overflow: hidden; transform-origin: bottom right; transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.1);">
        <!-- Header -->
        <div class="p-3 d-flex justify-content-between align-items-center" style="background: var(--primary-gradient); color: white;">
            <div class="fw-bold"><i class="bi bi-robot me-2"></i>Yapay Zeka Eğitmen</div>
            <button type="button" id="aiHintCloseBtn" class="btn-close btn-close-white shadow-none" aria-label="Close"></button>
        </div>
        
        <!-- Chat History -->
        <div id="aiChatHistory" class="p-3 flex-grow-1" style="overflow-y: auto; background-color: #f8f9fa;">
            <!-- Welcome message -->
            <div class="d-flex mb-3">
                <div class="bg-primary bg-opacity-10 text-dark p-3 rounded-4 rounded-top-0 shadow-sm" style="max-width: 85%;">
                    <i class="bi bi-magic text-primary mb-2 fs-5 d-block"></i>
                    <p class="mb-0 small">Merhaba! Bu soruda takıldığın bir yer mi var? Bana ne sormak istediğini yazabilirsin, cevabı söylemeden düşünme adımlarında sana rehberlik edeceğim.</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-top">
            <div class="input-group">
                <input type="text" id="aiUserInput" class="form-control rounded-pill rounded-end-0 border-end-0 shadow-none" placeholder="Sorunuzu yazın..." style="border-color: rgba(0,0,0,0.1);">
                <button type="button" id="aiHintFetchBtn" class="btn btn-outline-primary border-start-0 rounded-pill rounded-start-0 shadow-none" style="border-color: rgba(0,0,0,0.1);">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button type="button" id="aiHintBtn" class="btn btn-gradient rounded-circle shadow-lg" style="width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; transition: transform 0.2s;">
        <i class="bi bi-robot"></i>
    </button>
</div>

<!-- Şikayet Modalı -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="reportModalLabel"><i class="bi bi-flag-fill text-danger me-2"></i> İçeriği Bildir</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL ?>/report/create" method="POST">
          <div class="modal-body p-4">
            <input type="hidden" name="item_type" id="reportItemType">
            <input type="hidden" name="item_id" id="reportItemId">
            <div class="mb-3">
                <label for="reportReason" class="form-label text-muted">Şikayet Sebebiniz</label>
                <textarea class="form-control" name="reason" id="reportReason" rows="3" required placeholder="Lütfen neden bildirdiğinizi açıklayın..."></textarea>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">İptal</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4">Gönder</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Çizimli Çözüm Tahtası (Whiteboard) Modalı -->
<div class="modal fade" id="whiteboardModal" tabindex="-1" aria-labelledby="whiteboardModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content glass-effect border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
      <div class="modal-header border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-primary mb-0" id="whiteboardModalLabel">
          <i class="bi bi-brush me-2"></i> Çizimli Çözüm Tahtası (Whiteboard)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3 p-md-4">
        <!-- Araç Çubuğu -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 p-2 rounded-3 bg-light border">
          <!-- Araçlar & Renkler -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" id="wbToolPen" class="btn btn-primary active"><i class="bi bi-pencil-fill me-1"></i> Kalem</button>
              <button type="button" id="wbToolEraser" class="btn btn-outline-secondary"><i class="bi bi-eraser-fill me-1"></i> Silgi</button>
            </div>

            <!-- Renk Paleti -->
            <div class="d-flex align-items-center gap-1 ms-2">
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#0f172a" style="width:24px; height:24px; background:#0f172a; cursor:pointer;" title="Siyah"></button>
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#dc2626" style="width:24px; height:24px; background:#dc2626; cursor:pointer;" title="Kırmızı"></button>
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#2563eb" style="width:24px; height:24px; background:#2563eb; cursor:pointer;" title="Mavi"></button>
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#16a34a" style="width:24px; height:24px; background:#16a34a; cursor:pointer;" title="Yeşil"></button>
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#d97706" style="width:24px; height:24px; background:#d97706; cursor:pointer;" title="Sarı/Turuncu"></button>
              <button type="button" class="wb-color-btn rounded-circle border" data-color="#ffffff" style="width:24px; height:24px; background:#ffffff; cursor:pointer;" title="Beyaz"></button>
            </div>

            <!-- Çizgi Kalınlığı -->
            <div class="d-flex align-items-center ms-2 gap-1">
              <small class="text-muted">Kalınlık:</small>
              <input type="range" id="wbLineWidth" min="1" max="25" value="3" style="width: 80px;" class="form-range">
            </div>
          </div>

          <!-- Zemin Seçenekleri & Geri Al -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if (!empty($question['image_path'])): ?>
              <button type="button" id="wbLoadQuestionImg" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="bi bi-image me-1"></i> Sorunun Resmini Zemin Yap
              </button>
            <?php endif; ?>
            <button type="button" id="wbBgWhite" class="btn btn-sm btn-outline-secondary rounded-pill">⬜ Beyaz Tahta</button>
            <button type="button" id="wbBgGrid" class="btn btn-sm btn-outline-secondary rounded-pill">📐 Kareli Defter</button>
            <button type="button" id="wbUndo" class="btn btn-sm btn-outline-dark rounded-pill" title="Geri Al"><i class="bi bi-arrow-counterclockwise"></i> Geri Al</button>
            <button type="button" id="wbClear" class="btn btn-sm btn-outline-danger rounded-pill" title="Tümünü Temizle"><i class="bi bi-trash"></i> Temizle</button>
          </div>
        </div>

        <!-- Canvas Alanı -->
        <div class="d-flex justify-content-center bg-white rounded-3 shadow-inner p-1 border overflow-hidden" style="position:relative; min-height: 420px;">
          <canvas id="whiteboardCanvas" width="900" height="520" style="max-width: 100%; height: auto; cursor: crosshair; touch-action: none; background: #ffffff;"></canvas>
        </div>
      </div>

      <div class="modal-footer border-top py-3 px-4">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" id="saveCanvasToAnswerBtn" class="btn btn-gradient rounded-pill px-4 fw-bold">
          <i class="bi bi-check2-circle me-1"></i> Çözüm Görseli Olarak Ekle
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Cevap Resmi Önizleme ────────────────────────────────────────────────
    const answerImageInput   = document.getElementById('answerImageInput');
    const answerImagePreview = document.getElementById('answerImagePreview');
    const answerImagePreviewImg = document.getElementById('answerImagePreviewImg');
    const removeAnswerImage  = document.getElementById('removeAnswerImage');

    if (answerImageInput) {
        answerImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    answerImagePreviewImg.src = e.target.result;
                    answerImagePreview.classList.remove('d-none');
                    // Canvas çizimini temizle eğer normal resim seçildiyse
                    const canvasInput = document.getElementById('canvasDrawingInput');
                    const canvasBox = document.getElementById('canvasPreviewBox');
                    if (canvasInput) canvasInput.value = '';
                    if (canvasBox) canvasBox.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeAnswerImage) {
        removeAnswerImage.addEventListener('click', function() {
            answerImageInput.value = '';
            answerImagePreviewImg.src = '';
            answerImagePreview.classList.add('d-none');
        });
    }

    // ── Canvas Çizimini Kaldır Butonu ───────────────────────────────────────
    const removeCanvasDrawingBtn = document.getElementById('removeCanvasDrawingBtn');
    if (removeCanvasDrawingBtn) {
        removeCanvasDrawingBtn.addEventListener('click', function() {
            document.getElementById('canvasDrawingInput').value = '';
            document.getElementById('canvasPreviewBox').classList.add('d-none');
            document.getElementById('canvasPreviewImg').src = '';
        });
    }

    // ── Çizimli Tahta (Whiteboard) Motoru ───────────────────────────────────
    const wbModal = document.getElementById('whiteboardModal');
    const canvas = document.getElementById('whiteboardCanvas');
    if (wbModal && canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let currentColor = '#0f172a';
        let currentLineWidth = 3;
        let currentTool = 'pen'; // 'pen', 'eraser'
        const historyStack = [];

        function saveState() {
            if (historyStack.length >= 15) historyStack.shift();
            historyStack.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
        }

        function clearCanvas(fillColor = '#ffffff') {
            ctx.fillStyle = fillColor;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        function drawGrid() {
            clearCanvas('#ffffff');
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1;
            const gridSize = 25;
            for (let x = 0; x < canvas.width; x += gridSize) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, canvas.height);
                ctx.stroke();
            }
            for (let y = 0; y < canvas.height; y += gridSize) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(canvas.width, y);
                ctx.stroke();
            }
        }

        function loadQuestionImageToCanvas() {
            <?php if (!empty($question['image_path'])): ?>
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                saveState();
                clearCanvas('#ffffff');
                // Fit image proportionally
                const scale = Math.min((canvas.width - 40) / img.width, (canvas.height - 40) / img.height);
                const w = img.width * scale;
                const h = img.height * scale;
                const x = (canvas.width - w) / 2;
                const y = (canvas.height - h) / 2;
                ctx.drawImage(img, x, y, w, h);
                saveState();
            };
            img.src = '<?= BASE_URL . $question['image_path'] ?>';
            <?php endif; ?>
        }

        // İlk açılışta zemin hazırla
        wbModal.addEventListener('shown.bs.modal', function () {
            if (historyStack.length === 0) {
                <?php if (!empty($question['image_path'])): ?>
                    loadQuestionImageToCanvas();
                <?php else: ?>
                    clearCanvas('#ffffff');
                    saveState();
                <?php endif; ?>
            }
        });

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            saveState();
            const pos = getCoordinates(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = currentTool === 'eraser' ? '#ffffff' : currentColor;
            ctx.lineWidth = currentTool === 'eraser' ? currentLineWidth * 4 : currentLineWidth;
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getCoordinates(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            if (!isDrawing) return;
            isDrawing = false;
            ctx.closePath();
        }

        // Mouse Events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Touch Events
        canvas.addEventListener('touchstart', function(e) { e.preventDefault(); startDrawing(e); }, { passive: false });
        canvas.addEventListener('touchmove', function(e) { e.preventDefault(); draw(e); }, { passive: false });
        canvas.addEventListener('touchend', function(e) { e.preventDefault(); stopDrawing(); }, { passive: false });

        // Tool buttons
        const penBtn = document.getElementById('wbToolPen');
        const eraserBtn = document.getElementById('wbToolEraser');
        if (penBtn && eraserBtn) {
            penBtn.addEventListener('click', () => {
                currentTool = 'pen';
                penBtn.classList.add('btn-primary', 'active');
                penBtn.classList.remove('btn-outline-secondary');
                eraserBtn.classList.remove('btn-primary', 'active');
                eraserBtn.classList.add('btn-outline-secondary');
            });
            eraserBtn.addEventListener('click', () => {
                currentTool = 'eraser';
                eraserBtn.classList.add('btn-primary', 'active');
                eraserBtn.classList.remove('btn-outline-secondary');
                penBtn.classList.remove('btn-primary', 'active');
                penBtn.classList.add('btn-outline-secondary');
            });
        }

        // Color buttons
        document.querySelectorAll('.wb-color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentColor = this.dataset.color;
                if (currentTool === 'eraser' && penBtn) penBtn.click();
                document.querySelectorAll('.wb-color-btn').forEach(b => b.style.outline = 'none');
                this.style.outline = '3px solid #4f46e5';
            });
        });

        // Line width
        const widthSlider = document.getElementById('wbLineWidth');
        if (widthSlider) {
            widthSlider.addEventListener('input', function() {
                currentLineWidth = parseInt(this.value);
            });
        }

        // Background buttons
        const btnQImg = document.getElementById('wbLoadQuestionImg');
        const btnBgWhite = document.getElementById('wbBgWhite');
        const btnBgGrid = document.getElementById('wbBgGrid');
        const btnUndo = document.getElementById('wbUndo');
        const btnClear = document.getElementById('wbClear');

        if (btnQImg) btnQImg.addEventListener('click', loadQuestionImageToCanvas);
        if (btnBgWhite) btnBgWhite.addEventListener('click', () => { saveState(); clearCanvas('#ffffff'); });
        if (btnBgGrid) btnBgGrid.addEventListener('click', () => { saveState(); drawGrid(); });
        if (btnClear) btnClear.addEventListener('click', () => { saveState(); clearCanvas('#ffffff'); });
        if (btnUndo) {
            btnUndo.addEventListener('click', () => {
                if (historyStack.length > 0) {
                    const lastState = historyStack.pop();
                    ctx.putImageData(lastState, 0, 0);
                }
            });
        }

        // Çizimi Çözüme Ekle Butonu
        const saveBtn = document.getElementById('saveCanvasToAnswerBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const dataUrl = canvas.toDataURL('image/png');
                document.getElementById('canvasDrawingInput').value = dataUrl;
                document.getElementById('canvasPreviewImg').src = dataUrl;
                document.getElementById('canvasPreviewBox').classList.remove('d-none');

                // Dosya alanını temizle
                if (answerImageInput) answerImageInput.value = '';
                if (answerImagePreview) answerImagePreview.classList.add('d-none');

                // Modalı kapat
                const modalInstance = bootstrap.Modal.getInstance(wbModal);
                if (modalInstance) modalInstance.hide();
            });
        }
    }

    const reportModal = document.getElementById('reportModal');
    if (reportModal) {
        reportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemType = button.getAttribute('data-item-type');
            const itemId = button.getAttribute('data-item-id');
            
            document.getElementById('reportItemType').value = itemType;
            document.getElementById('reportItemId').value = itemId;
            document.getElementById('reportReason').value = ''; // Modal açıldığında textarea'yı temizle
        });
    }

    // Bookmark Toggle Logic
    const bookmarkBtn = document.getElementById('bookmarkBtn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            const questionId = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            fetch(`<?= BASE_URL ?>/question/${questionId}/bookmark`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    if(data.added) {
                        icon.classList.remove('bi-bookmark');
                        icon.classList.add('bi-bookmark-fill', 'text-primary');
                        this.title = 'Favorilerden Çıkar';
                    } else {
                        icon.classList.remove('bi-bookmark-fill', 'text-primary');
                        icon.classList.add('bi-bookmark');
                        this.title = 'Favorilere Ekle';
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error(err));
        });
    }

    // Like Toggle Logic
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = '<?= BASE_URL ?>/login';
                return;
            <?php endif; ?>

            const answerId = this.getAttribute('data-answer-id');
            const icon = this.querySelector('i');
            const countSpan = this.querySelector('.like-count');

            fetch(`<?= BASE_URL ?>/answer/${answerId}/like`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let currentCount = parseInt(countSpan.textContent) || 0;
                    if (data.action === 'liked') {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        this.classList.remove('text-muted');
                        this.classList.add('text-danger');
                        countSpan.textContent = currentCount + 1;
                    } else if (data.action === 'unliked') {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        this.classList.remove('text-danger');
                        this.classList.add('text-muted');
                        countSpan.textContent = Math.max(0, currentCount - 1);
                    }
                } else {
                    alert(data.message || 'Bir işlem hatası oluştu');
                }
            })
            .catch(err => console.error(err));
        });
    });

    // AI Hint Floating Logic
    const aiFloatingContainer = document.getElementById('aiFloatingContainer');
    if (aiFloatingContainer) {
        document.body.appendChild(aiFloatingContainer); // Move outside container to avoid transform/z-index issues
    }

    const aiHintBtn = document.getElementById('aiHintBtn');
    const aiHintBox = document.getElementById('aiHintBox');
    const aiHintCloseBtn = document.getElementById('aiHintCloseBtn');
    const aiHintFetchBtn = document.getElementById('aiHintFetchBtn');
    
    if (aiHintBtn && aiHintBox) {
        // Toggle Popup
        aiHintBtn.addEventListener('click', function() {
            if (aiHintBox.classList.contains('d-none')) {
                aiHintBox.classList.remove('d-none');
                aiHintBtn.style.transform = 'scale(0.8)';
            } else {
                aiHintBox.classList.add('d-none');
                aiHintBtn.style.transform = 'scale(1)';
            }
        });

        // Close Button
        aiHintCloseBtn.addEventListener('click', function() {
            aiHintBox.classList.add('d-none');
            aiHintBtn.style.transform = 'scale(1)';
        });

        // Fetch Hint
        if (aiHintFetchBtn) {
            const sendPrompt = () => {
                <?php if (!isset($_SESSION['user_id'])): ?>
                    alert("Yapay zeka ile sohbet etmek için giriş yapmalısınız.");
                    return;
                <?php endif; ?>

                const questionId = <?= $question['id'] ?>;
                const userInputEl = document.getElementById('aiUserInput');
                const chatHistory = document.getElementById('aiChatHistory');
                
                const promptText = userInputEl.value.trim();
                if (!promptText) return;

                // Escape HTML logic manually
                const escapeHtml = (text) => text.replace(/[&<>'"]/g, char => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
                }[char]));

                // 1. Add user message to chat history
                chatHistory.innerHTML += `
                    <div class="d-flex justify-content-end mb-3">
                        <div class="bg-primary text-white p-2 px-3 rounded-4 rounded-top-0 shadow-sm" style="max-width: 85%;">
                            <p class="mb-0 small">${escapeHtml(promptText)}</p>
                        </div>
                    </div>
                `;
                userInputEl.value = '';
                chatHistory.scrollTop = chatHistory.scrollHeight;

                // 2. Add loading message
                const loadingId = 'ai-loading-' + Date.now();
                chatHistory.innerHTML += `
                    <div id="${loadingId}" class="d-flex mb-3">
                        <div class="bg-white border p-3 rounded-4 rounded-top-0 shadow-sm text-center" style="max-width: 85%;">
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            <div class="spinner-grow spinner-grow-sm text-primary mx-1" role="status"></div>
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                        </div>
                    </div>
                `;
                chatHistory.scrollTop = chatHistory.scrollHeight;
                
                // Disable input during request
                userInputEl.disabled = true;
                aiHintFetchBtn.disabled = true;

                const formData = new FormData();
                formData.append('prompt', promptText);

                fetch(`<?= BASE_URL ?>/question/${questionId}/hint`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const loadingEl = document.getElementById(loadingId);
                    if(loadingEl) loadingEl.remove();

                    let responseHtml = '';
                    if (data.success) {
                        responseHtml = data.hint;
                    } else {
                        responseHtml = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</span>`;
                    }

                    chatHistory.innerHTML += `
                        <div class="d-flex mb-3">
                            <div class="bg-white border text-dark p-3 rounded-4 rounded-top-0 shadow-sm" style="max-width: 90%;">
                                <div class="mb-0 small" style="line-height: 1.5;">${responseHtml}</div>
                            </div>
                        </div>
                    `;
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                    
                    userInputEl.disabled = false;
                    aiHintFetchBtn.disabled = false;
                    userInputEl.focus();
                })
                .catch(err => {
                    console.error(err);
                    const loadingEl = document.getElementById(loadingId);
                    if(loadingEl) loadingEl.remove();

                    chatHistory.innerHTML += `
                        <div class="d-flex mb-3">
                            <div class="bg-white border text-danger p-2 px-3 rounded-4 rounded-top-0 shadow-sm" style="max-width: 85%;">
                                <p class="mb-0 small"><i class="bi bi-x-circle"></i> Bir bağlantı hatası oluştu.</p>
                            </div>
                        </div>
                    `;
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                    userInputEl.disabled = false;
                    aiHintFetchBtn.disabled = false;
                });
            };

            aiHintFetchBtn.addEventListener('click', sendPrompt);
            document.getElementById('aiUserInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendPrompt();
            });
        }
    }
});
</script>

<!-- Include stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Initialize Quill editor -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editorContainer = document.getElementById('editor-container');
    if(editorContainer) {
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            ['clean']
        ];

        var quill = new Quill('#editor-container', {
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: '<?= __('share_your_solution') ?>',
            theme: 'snow'
        });
        
        // Style the toolbar to match glass effect
        document.querySelector('.ql-toolbar').classList.add('form-control-glass');
        document.querySelector('.ql-toolbar').style.borderRadius = '15px 15px 0 0';
        document.querySelector('.ql-toolbar').style.borderBottom = '1px solid rgba(255,255,255,0.2)';

        // Update hidden input before submit
        var form = document.querySelector('form[action*="/answer"]');
        if (form) {
            form.onsubmit = function() {
                var content = document.querySelector('input[name=content]');
                content.value = quill.root.innerHTML;
            };
        }
    }
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
