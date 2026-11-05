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

<nav class="navbar navbar-glass mt-3 mb-4 bb-topbar">
    <div class="container-fluid px-3 px-lg-4 bb-topbar-inner">
        <button class="btn bb-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#bbSidebar" aria-controls="bbSidebar" aria-label="Menüyü aç">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand fw-bold d-flex align-items-center bb-centered-brand" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/public/images/logo.png" alt="BrainBoost Logo" width="32" height="32" class="me-2 rounded">
            BrainBoost
        </a>
        <div class="bb-topbar-actions d-flex align-items-center gap-2">
            <div class="lang-switcher-wrapper">
                <a href="?lang=tr" class="lang-link <?= ($_SESSION['lang'] ?? 'tr') == 'tr' ? 'active' : '' ?>" aria-label="Türkçe">TR</a>
                <a href="?lang=en" class="lang-link <?= ($_SESSION['lang'] ?? 'tr') == 'en' ? 'active' : '' ?>" aria-label="English">EN</a>
            </div>
            <button class="btn btn-sm bb-sidebar-icon-btn" id="theme-toggle" type="button" aria-label="<?= __('theme_toggle') ?>" title="<?= __('theme_toggle') ?>">
                <i class="bi bi-moon-fill" id="theme-icon"></i>
            </button>
        </div>
    </div>
</nav>

<?php
$msgUnread = 0;
if (isset($_SESSION['user_id'])) {
    try {
        if (!class_exists('Message')) require_once __DIR__ . '/../models/Message.php';
        $msgModel = new Message();
        $msgUnread = $msgModel->getUnreadCount($_SESSION['user_id']);
    } catch (Exception $e) {}
}
?>
<div class="offcanvas offcanvas-start bb-sidebar" tabindex="-1" id="bbSidebar" aria-labelledby="bbSidebarLabel">
    <div class="offcanvas-header bb-sidebar-header">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/public/images/logo.png" alt="" width="34" height="34" class="me-2 rounded">
            <span id="bbSidebarLabel">BrainBoost</span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="bb-sidebar-label mb-2">MENÜ</div>
        <ul class="navbar-nav bb-sidebar-nav">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/"><i class="bi bi-house-door"></i><span><?= __('home_link') ?></span></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/leaderboard"><i class="bi bi-trophy"></i><span><?= __('leaderboard') ?></span></a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/ai-tutor"><i class="bi bi-robot"></i><span>Yapay Zekâ Öğretmeni</span></a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mock-exams"><i class="bi bi-journal-check"></i><span>Online Deneme</span></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/performance"><i class="bi bi-graph-up-arrow"></i><span>Başarı Analizi</span></a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pomodoro"><i class="bi bi-stopwatch"></i><span>Pomodoro</span></a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(($_SESSION['role'] ?? '') === 'teacher'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/#questions-section"><i class="bi bi-chat-dots"></i><span>Soruları İncele</span></a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/question/create"><i class="bi bi-plus-circle"></i><span><?= __('ask_question_btn') ?></span></a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/messages"><i class="bi bi-chat-dots-fill"></i><span><?= __('messages') ?></span><span id="msg-nav-badge" class="badge rounded-pill bg-danger ms-auto" style="font-size:.65rem;<?= $msgUnread > 0 ? '' : 'display:none;' ?>"><?= $msgUnread ?: '' ?></span></a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-bell-fill"></i><span>Bildirimler</span><span id="notif-badge" class="badge rounded-pill bg-danger ms-auto" style="display:none;"></span></a>
                    <ul class="dropdown-menu bb-notif-menu shadow-sm" aria-labelledby="notifDropdown" id="notif-list"><li><span class="dropdown-item text-muted text-center py-3">Bildirim yok</span></li></ul>
                </li>
                <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-shield-lock"></i><span><?= __('admin_panel') ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/moderation"><i class="bi bi-shield-check"></i><span><?= __('moderation_queue') ?></span></a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div class="bb-sidebar-footer mt-auto pt-3">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="bb-sidebar-user mb-2">
                    <?php if(!empty($_SESSION['profile_photo'])): ?>
                        <img src="<?= BASE_URL . $_SESSION['profile_photo'] ?>" alt="" class="rounded-circle">
                    <?php else: ?>
                        <span class="bb-user-fallback"><i class="bi bi-person-circle"></i></span>
                    <?php endif; ?>
                    <div class="min-w-0"><strong><?= htmlspecialchars($_SESSION['username']) ?></strong><small>Hesabım</small></div>
                </div>
                <a class="nav-link" href="<?= BASE_URL ?>/profile"><i class="bi bi-person"></i><span><?= __('my_profile') ?></span></a>
                <form action="<?= BASE_URL ?>/logout" method="POST" class="m-0"><button type="submit" class="nav-link w-100 text-start text-danger"><i class="bi bi-box-arrow-right"></i><span><?= __('logout') ?></span></button></form>
            <?php else: ?>
                <a class="btn btn-glass-outline w-100 mb-2" href="<?= BASE_URL ?>/login"><?= __('login_btn') ?></a>
                <a class="btn btn-gradient text-white w-100" href="<?= BASE_URL ?>/register"><?= __('register_btn') ?></a>
            <?php endif; ?>
            <div id="pwa-install-nav-item" class="mt-3" style="display:none;"><button class="btn btn-gradient text-white w-100" id="pwa-install-btn"><i class="bi bi-download me-2"></i>Uygulamayı Yükle</button></div>
        </div>
    </div>
</div>

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
