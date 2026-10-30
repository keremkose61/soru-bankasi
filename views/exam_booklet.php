<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — BrainBoost Soru Bankası</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/logo.png">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #4f46e5;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .booklet-container {
            max-width: 950px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-radius: 12px;
        }
        .header-box {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 25px;
        }
        .question-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px 30px;
        }
        .single-column {
            grid-template-columns: 1fr !important;
        }
        .question-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            background: #ffffff;
            break-inside: avoid;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .q-number {
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 2px 10px;
            border-radius: 6px;
            margin-right: 8px;
        }
        .q-subject {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
        }
        .q-title {
            font-weight: 700;
            font-size: 1rem;
            margin-top: 8px;
            margin-bottom: 8px;
            color: #0f172a;
        }
        .q-desc {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #334155;
            margin-bottom: 12px;
        }
        .q-image {
            max-width: 100%;
            max-height: 220px;
            object-fit: contain;
            border-radius: 6px;
            margin: 8px auto;
            display: block;
            border: 1px solid #e2e8f0;
        }
        .scratch-area {
            border-top: 1px dashed #cbd5e1;
            margin-top: auto;
            padding-top: 8px;
            min-height: 50px;
            font-size: 0.75rem;
            color: #94a3b8;
            font-style: italic;
        }
        .optical-sheet {
            border: 1px solid #0f172a;
            border-radius: 8px;
            padding: 15px;
            margin-top: 30px;
            break-inside: avoid;
        }
        .opt-bubble {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #475569;
            border-radius: 50%;
            text-align: center;
            line-height: 16px;
            font-size: 0.7rem;
            margin: 0 2px;
            color: #475569;
        }
        .answer-key-section {
            page-break-before: always;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px dashed #0f172a;
        }

        /* ── YAZDIRMA (PRINT) ÖZEL STİLLERİ ───────────────────────── */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .booklet-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .header-box {
                border-color: #000000 !important;
            }
            .question-item {
                border: 1px solid #94a3b8 !important;
            }
            .answer-key-section {
                page-break-before: always;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
        }
    </style>
</head>
<body>

<!-- Kontrol Çubuğu (Yazdırma esnasında gizlenir) -->
<div class="no-print bg-dark text-white py-2 px-3 sticky-top shadow-sm">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-light rounded-pill">
                <i class="bi bi-arrow-left"></i> Geri Dön
            </a>
            <span class="fw-bold"><i class="bi bi-file-earmark-pdf me-1"></i> <?= htmlspecialchars($title) ?> (<?= count($questions) ?> Soru)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="toggleColumnsBtn" class="btn btn-sm btn-outline-light rounded-pill">
                <i class="bi bi-layout-split me-1"></i> <span id="colBtnText">Tek Sütun Yap</span>
            </button>
            <button type="button" id="toggleKeyBtn" class="btn btn-sm btn-outline-warning rounded-pill">
                <i class="bi bi-key-fill me-1"></i> <span id="keyBtnText">Cevap Anahtarını Gizle</span>
            </button>
            <button onclick="window.print()" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Yazdır / PDF İndir
            </button>
        </div>
    </div>
</div>

<div class="booklet-container">

    <!-- Sınav Başlık ve Öğrenci Bilgi Alanı -->
    <div class="header-box">
        <div class="row align-items-center">
            <div class="col-8">
                <div class="d-flex align-items-center mb-1">
                    <img src="<?= BASE_URL ?>/public/images/logo.png" alt="Logo" width="30" height="30" class="me-2 rounded">
                    <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">BRAINBOOST DENEME VE TARAMA TESTİ</h5>
                </div>
                <div class="text-muted small fw-bold"><?= htmlspecialchars($title) ?> &bull; Soru Sayısı: <?= count($questions) ?> &bull; Süre: <?= count($questions) * 2 ?> Dakika</div>
            </div>
            <div class="col-4 text-end">
                <div class="small fw-bold">Tarih: ____ / ____ / 202_</div>
            </div>
        </div>
        <hr class="my-2 border-dark">
        <div class="row g-2 small">
            <div class="col-6"><strong>Adı Soyadı:</strong> ................................................................</div>
            <div class="col-3"><strong>Sınıf / Şube:</strong> .............</div>
            <div class="col-3"><strong>Numara:</strong> .............</div>
        </div>
        <div class="row mt-2 pt-1 border-top border-secondary-subtle small text-center text-muted">
            <div class="col-3"><strong>Doğru:</strong> _____</div>
            <div class="col-3"><strong>Yanlış:</strong> _____</div>
            <div class="col-3"><strong>Boş:</strong> _____</div>
            <div class="col-3"><strong>NET:</strong> _____</div>
        </div>
    </div>

    <!-- Sorular Listesi -->
    <?php if (empty($questions)): ?>
        <div class="text-center py-5 text-muted">
            <h5>Kitapçığa eklenecek soru bulunamadı.</h5>
            <p>Lütfen farklı filtrelerle tekrar deneyin.</p>
        </div>
    <?php else: ?>
        <div class="question-columns" id="questionGrid">
            <?php $i = 1; foreach ($questions as $q): ?>
                <div class="question-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <span class="q-number"><?= $i ?></span>
                            <span class="q-subject"><?= htmlspecialchars($q['subject']) ?></span>
                        </div>
                        <?php if (isset($q['username'])): ?>
                            <small class="text-muted" style="font-size:0.7rem;"><i class="bi bi-person"></i> <?= htmlspecialchars($q['username']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="q-title"><?= htmlspecialchars($q['title']) ?></div>

                    <div class="q-desc">
                        <?= strip_tags($q['description'], '<b><i><u><strong><em><p><br><ul><li><ol><code><span>') ?>
                    </div>

                    <?php if (!empty($q['image_path'])): ?>
                        <img src="<?= BASE_URL . $q['image_path'] ?>" class="q-image" alt="Soru Görseli <?= $i ?>">
                    <?php endif; ?>

                    <div class="scratch-area">
                        <span>Karalama & Çözüm Alanı</span>
                    </div>
                </div>
            <?php $i++; endforeach; ?>
        </div>

        <!-- Optik Form Şablonu -->
        <div class="optical-sheet">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small text-uppercase"><i class="bi bi-card-checklist me-1"></i> CEVAP KODLAMA ALANI</span>
                <span class="text-muted" style="font-size:0.75rem;">Kurşun kalem ile taşırmadan doldurunuz.</span>
            </div>
            <div class="row row-cols-2 row-cols-sm-4 row-cols-md-5 g-2">
                <?php for($k = 1; $k <= count($questions); $k++): ?>
                    <div class="col d-flex align-items-center">
                        <span class="fw-bold small me-1" style="min-width: 22px;"><?= $k ?>.</span>
                        <span class="opt-bubble">A</span>
                        <span class="opt-bubble">B</span>
                        <span class="opt-bubble">C</span>
                        <span class="opt-bubble">D</span>
                        <span class="opt-bubble">E</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- CEVAP ANAHTARI & ÇÖZÜM ÖZETİ SAYFASI (Yazdırıldığında ayrı sayfada çıkar) -->
        <div class="answer-key-section" id="answerKeyBox">
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1 text-uppercase"><i class="bi bi-key-fill me-2 text-warning"></i> CEVAP ANAHTARI VE ÇÖZÜM ÖZETİ</h4>
                <div class="text-muted small"><?= htmlspecialchars($title) ?> &bull; Soru ve Çözüm Detayları</div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 8%;">Soru #</th>
                            <th style="width: 22%;">Ders / Konu</th>
                            <th style="width: 40%;">Soru Başlığı</th>
                            <th style="width: 30%;">Onaylanan Çözüm & İpucu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n = 1; foreach ($questions as $q): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $n ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($q['subject']) ?></span></td>
                                <td class="small fw-semibold"><?= htmlspecialchars($q['title']) ?></td>
                                <td class="small text-muted">
                                    <?php if (!empty($q['best_answer'])): ?>
                                        <span class="text-success fw-bold d-block"><i class="bi bi-check-circle-fill me-1"></i>Çözüm Var (<?= htmlspecialchars($q['best_answer']['username']) ?>):</span>
                                        <?= mb_substr(strip_tags($q['best_answer']['content']), 0, 100) ?>...
                                    <?php else: ?>
                                        <span class="text-secondary fst-italic">Henüz kabul edilmiş çözüm yok.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php $n++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

    <div class="text-center text-muted small mt-4 pt-3 border-top">
        BrainBoost Soru ve Deneme Paylaşım Ağı &bull; Sınavınızda Başarılar Dileriz!
    </div>

</div>

<!-- Sütun & Cevap Anahtarı Kontrol Scripti -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleColumnsBtn');
    const colText = document.getElementById('colBtnText');
    const grid = document.getElementById('questionGrid');

    if (toggleBtn && grid) {
        toggleBtn.addEventListener('click', function() {
            grid.classList.toggle('single-column');
            if (grid.classList.contains('single-column')) {
                colText.textContent = '2 Sütun Yap';
            } else {
                colText.textContent = 'Tek Sütun Yap';
            }
        });
    }

    const toggleKeyBtn = document.getElementById('toggleKeyBtn');
    const keyBox = document.getElementById('answerKeyBox');
    const keyText = document.getElementById('keyBtnText');

    if (toggleKeyBtn && keyBox) {
        toggleKeyBtn.addEventListener('click', function() {
            if (keyBox.style.display === 'none') {
                keyBox.style.display = 'block';
                keyText.textContent = 'Cevap Anahtarını Gizle';
            } else {
                keyBox.style.display = 'none';
                keyText.textContent = 'Cevap Anahtarını Göster';
            }
        });
    }
});
</script>

<!-- MathJax Formül Desteği -->
<script>
window.MathJax = {
  tex: {
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    displayMath: [['$$', '$$'], ['\\[', '\\]']]
  },
  svg: {
    fontCache: 'global'
  }
};
</script>
<script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>

</body>
</html>
