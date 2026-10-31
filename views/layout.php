<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/images/logo.png">
    <script>
        // Prevent layout shift/flash on reload
        const storedTheme = localStorage.getItem('theme') || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
        if (storedTheme) document.documentElement.setAttribute('data-bs-theme', storedTheme);
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soru Bankası</title>

    <!-- PWA Manifest & App Metas -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="BrainBoost">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= BASE_URL ?>/public/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/public/images/icons/icon-192x192.png">
    <meta name="msapplication-TileColor" content="#4f46e5">
    <meta name="msapplication-TileImage" content="<?= BASE_URL ?>/public/images/icons/icon-144x144.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/style.css?v=<?= filemtime(__DIR__ . '/../public/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass mt-3 mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/public/images/logo.png" alt="Soru Bankası Logo" width="32" height="32" class="me-2 rounded">
            Soru Bankası
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <div class="lang-switcher-wrapper">
                        <a href="?lang=tr" class="lang-link <?= $_SESSION['lang'] == 'tr' ? 'active' : '' ?>">TR</a>
                        <a href="?lang=en" class="lang-link <?= $_SESSION['lang'] == 'en' ? 'active' : '' ?>">EN</a>
                    </div>
                </li>
                <li class="nav-item me-2">
                    <button class="btn btn-link nav-link px-2 d-flex align-items-center" id="theme-toggle" type="button" aria-label="<?= __('theme_toggle') ?>" title="<?= __('theme_toggle') ?>">
                        <i class="bi bi-moon-fill" id="theme-icon" style="font-size: 1.1rem; transition: transform 0.3s ease;"></i>
                    </button>
                </li>
                <li class="nav-item me-2" id="pwa-install-nav-item" style="display: none;">
                    <button class="btn btn-sm btn-gradient rounded-pill px-3 py-1 shadow-sm fw-bold text-white d-flex align-items-center" id="pwa-install-btn" title="Uygulamayı Cihazınıza Yükleyin">
                        <i class="bi bi-download me-1"></i> <span>Uygulamayı Yükle</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/"><?= __('home_link') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/leaderboard"><i class="bi bi-trophy"></i> <?= __('leaderboard') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pomodoro"><i class="bi bi-stopwatch"></i> Pomodoro</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] !== 'teacher'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/question/create"><i class="bi bi-plus-circle"></i> <?= __('ask_question_btn') ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold ms-2" href="<?= BASE_URL ?>/admin"><?= __('admin_panel') ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold ms-1 position-relative" href="<?= BASE_URL ?>/admin/moderation" title="<?= __('moderation_queue') ?>">
                                <i class="bi bi-shield-check" style="font-size:1.1rem; color:var(--primary-color);"></i>
                                <?php
                                    // Show badge count for items in review queue
                                    try {
                                        require_once __DIR__ . '/../models/Question.php';
                                        require_once __DIR__ . '/../models/Answer.php';
                                        $qm = new Question(); $am = new Answer();
                                        $rc = count($qm->getForModeration('review')) + count($am->getForModeration('review'));
                                        if ($rc > 0): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem; min-width:18px;"><?= $rc ?></span>
                                        <?php endif;
                                    } catch(Exception $e) {}
                                ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                        // Mesaj badge sayısı
                        $msgUnread = 0;
                        try {
                            if (!class_exists('Message')) {
                                require_once __DIR__ . '/../models/Message.php';
                            }
                            $msgModel  = new Message();
                            $msgUnread = $msgModel->getUnreadCount($_SESSION['user_id']);
                        } catch(Exception $e) {}
                    ?>
                    <li class="nav-item me-1">
                        <a class="nav-link position-relative px-2" href="<?= BASE_URL ?>/messages" title="<?= __('messages') ?>">
                            <i class="bi bi-chat-dots-fill" style="font-size:1.2rem;color:var(--primary-color);"></i>
                            <span id="msg-nav-badge"
                                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  style="font-size:.6rem;min-width:18px;<?= $msgUnread == 0 ? 'display:none;' : '' ?>">
                                <?= $msgUnread ?: '' ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative px-2 dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Bildirimler">
                            <i class="bi bi-bell-fill" style="font-size:1.2rem;color:var(--primary-color);"></i>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;min-width:18px;display:none;"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notif-list">
                            <li><span class="dropdown-item text-muted text-center py-3">Bildirim yok</span></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php if(!empty($_SESSION['profile_photo'])): ?>
                                <img src="<?= BASE_URL . $_SESSION['profile_photo'] ?>" alt="" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover; vertical-align: middle;">
                            <?php else: ?>
                                <i class="bi bi-person-circle"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile"><i class="bi bi-person"></i> <?= __('my_profile') ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right"></i> <?= __('logout') ?></a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn btn-glass-outline btn-sm ms-2 fw-bold" href="<?= BASE_URL ?>/login"><?= __('login_btn') ?></a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gradient btn-sm ms-2 fw-bold text-white" href="<?= BASE_URL ?>/register"><?= __('register_btn') ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5 animate-fade-in">
    <?= $content ?>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    const updateThemeIcon = (theme) => {
        if (theme === 'dark') {
            themeIcon.classList.remove('bi-sun-fill', 'text-warning');
            themeIcon.classList.add('bi-moon-fill', 'text-light');
            themeIcon.style.transform = 'rotate(180deg)';
        } else {
            themeIcon.classList.remove('bi-moon-fill', 'text-light');
            themeIcon.classList.add('bi-sun-fill', 'text-warning');
            themeIcon.style.transform = 'rotate(0deg)';
        }
    };

    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    if (themeIcon) updateThemeIcon(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-bs-theme');
            let newTheme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }

    // --- Bildirim Sistemi ---
    <?php if(isset($_SESSION['user_id'])): ?>
    const notifBadge = document.getElementById('notif-badge');
    const notifList = document.getElementById('notif-list');
    const notifDropdown = document.getElementById('notifDropdown');

    function checkNotifications() {
        fetch('<?= BASE_URL ?>/notifications/unread')
            .then(res => res.json())
            .then(data => {
                if(data.success && data.notifications.length > 0) {
                    notifBadge.textContent = data.notifications.length;
                    notifBadge.style.display = 'inline-block';
                    
                    let html = '';
                    data.notifications.forEach(n => {
                        let icon = 'bi-info-circle';
                        if(n.type === 'answer') icon = 'bi-chat-dots text-primary';
                        if(n.type === 'comment') icon = 'bi-chat-left-text-fill text-info';
                        if(n.type === 'like') icon = 'bi-heart-fill text-danger';
                        if(n.type === 'best_answer') icon = 'bi-check-circle-fill text-success';
                        
                        html += `<li><a class="dropdown-item py-2 border-bottom" href="<?= BASE_URL ?>/question/${n.content_id}">
                            <div class="d-flex align-items-center">
                                <i class="bi ${icon} me-2 fs-5"></i>
                                <span style="white-space:normal; font-size: 0.9rem;">${n.message}</span>
                            </div>
                        </a></li>`;
                    });
                    notifList.innerHTML = html;
                } else {
                    notifBadge.style.display = 'none';
                    notifList.innerHTML = '<li><span class="dropdown-item text-muted text-center py-3">Bildirim yok</span></li>';
                }
            }).catch(err => console.error(err));
    }

    notifDropdown.addEventListener('click', function() {
        if(notifBadge.style.display !== 'none') {
            fetch('<?= BASE_URL ?>/notifications/read', { method: 'POST' }).then(() => {
                notifBadge.style.display = 'none';
            });
        }
    });

    checkNotifications();
    setInterval(checkNotifications, 30000); // 30 saniyede bir kontrol et
    <?php endif; ?>
});
</script>

<!-- MathJax Setup -->
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

<!-- PWA Mobil Yükleme Bildirimi (Banner) -->
<div id="pwa-install-banner" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-3 p-3 glass-effect rounded-4 shadow-lg border border-primary-subtle z-3" style="max-width: 92%; width: 440px; backdrop-filter: blur(20px); z-index: 9999;">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <img src="<?= BASE_URL ?>/public/images/icons/icon-96x96.png" width="46" height="46" class="rounded-3 shadow-sm flex-shrink-0" alt="BrainBoost App">
        <div class="flex-grow-1 text-start">
            <div class="fw-bold small text-primary mb-0">BrainBoost Uygulamasını Yükleyin</div>
            <small class="text-muted" style="font-size: 0.75rem;">Çevrimdışı çalışma ve hızlı erişim için ana ekranınıza ekleyin.</small>
        </div>
        <button type="button" id="pwa-banner-install-btn" class="btn btn-gradient btn-sm rounded-pill px-3 fw-bold flex-shrink-0">Yükle</button>
        <button type="button" class="btn-close btn-sm" id="pwa-banner-close" aria-label="Kapat"></button>
    </div>
</div>

<!-- PWA Service Worker & Install Handler -->
<script>
// 1. Service Worker Kaydı
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
            .then((registration) => {
                console.log('BrainBoost PWA ServiceWorker başarıyla devrede:', registration.scope);
            })
            .catch((error) => {
                console.warn('BrainBoost ServiceWorker kaydı başarısız:', error);
            });
    });
}

// 2. PWA Yükleme (Install) İstek Yönetimi
let deferredPwaPrompt = null;
const pwaNavBtn = document.getElementById('pwa-install-btn');
const pwaNavItem = document.getElementById('pwa-install-nav-item');
const pwaBanner = document.getElementById('pwa-install-banner');
const pwaBannerBtn = document.getElementById('pwa-banner-install-btn');
const pwaBannerClose = document.getElementById('pwa-banner-close');

// Kullanıcı daha önce banner'ı kapattıysa bu oturumda tekrar rahatsız etme
const bannerDismissed = sessionStorage.getItem('pwa_banner_dismissed');

window.addEventListener('beforeinstallprompt', (e) => {
    // Tarayıcının varsayılan mini bilgi çubuğunu engelle
    e.preventDefault();
    deferredPwaPrompt = e;

    // Menüdeki yükleme butonunu görünür yap
    if (pwaNavItem) {
        pwaNavItem.style.display = 'block';
    }

    // Mobil banner'ı göster (eğer kapatılmadıysa)
    if (pwaBanner && !bannerDismissed) {
        pwaBanner.classList.remove('d-none');
    }
});

function handleInstallClick() {
    if (!deferredPwaPrompt) return;

    deferredPwaPrompt.prompt();
    deferredPwaPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
            console.log('Kullanıcı BrainBoost uygulamasını yükledi.');
        } else {
            console.log('Kullanıcı yüklemeyi erteledi.');
        }
        deferredPwaPrompt = null;
        if (pwaNavItem) pwaNavItem.style.display = 'none';
        if (pwaBanner) pwaBanner.classList.add('d-none');
    });
}

if (pwaNavBtn) {
    pwaNavBtn.addEventListener('click', handleInstallClick);
}

if (pwaBannerBtn) {
    pwaBannerBtn.addEventListener('click', handleInstallClick);
}

if (pwaBannerClose) {
    pwaBannerClose.addEventListener('click', () => {
        if (pwaBanner) pwaBanner.classList.add('d-none');
        sessionStorage.setItem('pwa_banner_dismissed', '1');
    });
}

// Uygulama yüklendiğinde butonları gizle
window.addEventListener('appinstalled', () => {
    console.log('BrainBoost PWA başarıyla kuruldu.');
    if (pwaNavItem) pwaNavItem.style.display = 'none';
    if (pwaBanner) pwaBanner.classList.add('d-none');
    deferredPwaPrompt = null;
});

// Eğer uygulama zaten bağımsız (standalone) PWA penceresinde çalışıyorsa butonları gizle
if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
    if (pwaNavItem) pwaNavItem.style.display = 'none';
    if (pwaBanner) pwaBanner.classList.add('d-none');
}
</script>

</body>
</html>
