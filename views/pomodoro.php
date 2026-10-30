<?php ob_start(); ?>

<div class="row justify-content-center animate-fade-in">
    <div class="col-lg-8 text-center">

        <!-- Başlık -->
        <div class="mb-4">
            <h1 class="display-5 fw-bold mb-2" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="bi bi-stopwatch me-2"></i> Pomodoro Odaklanma Odası
            </h1>
            <p class="text-muted">Dikkatini topla, zamanı yönet ve her tamamlanan seans ile puan & rozetler topla.</p>
        </div>

        <!-- Ana Sayaç Kartı -->
        <div class="glass-effect p-4 p-md-5 rounded-4 mb-4 position-relative shadow-lg" style="border: 1px solid var(--glass-border-light);">
            
            <!-- Mod Seçiciler -->
            <div class="d-inline-flex p-1 rounded-pill mb-4 flex-wrap justify-content-center" style="background: rgba(79, 70, 229, 0.08); border: 1px solid rgba(79, 70, 229, 0.15);">
                <button type="button" class="btn btn-sm rounded-pill px-4 fw-bold mode-btn active" data-mode="pomodoro" data-time="25">
                    🍅 Pomodoro (25 dk)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-4 fw-bold mode-btn" data-mode="shortBreak" data-time="5">
                    ☕ Kısa Mola (5 dk)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-4 fw-bold mode-btn" data-mode="longBreak" data-time="15">
                    🌴 Uzun Mola (15 dk)
                </button>
            </div>

            <!-- Dairesel Sayaç -->
            <div class="position-relative d-inline-flex align-items-center justify-content-center my-3" style="width: 280px; height: 280px;">
                <svg width="280" height="280" viewBox="0 0 280 280" style="transform: rotate(-90deg);">
                    <circle cx="140" cy="140" r="120" stroke="rgba(79, 70, 229, 0.1)" stroke-width="12" fill="transparent"/>
                    <circle id="progressRing" cx="140" cy="140" r="120" stroke="var(--primary-color)" stroke-width="12" fill="transparent"
                            stroke-dasharray="753.98" stroke-dashoffset="0" stroke-linecap="round" style="transition: stroke-dashoffset 0.8s ease;"/>
                </svg>
                <div class="position-absolute text-center">
                    <div id="timerDisplay" class="fw-bolder" style="font-size: 3.5rem; letter-spacing: 2px; color: var(--primary-color);">25:00</div>
                    <span id="modeStatusText" class="badge rounded-pill bg-light text-primary border border-primary-subtle px-3 py-1">Odaklanma Zamanı</span>
                </div>
            </div>

            <!-- Kontrol Butonları -->
            <div class="d-flex justify-content-center gap-3 align-items-center mt-4">
                <button id="startBtn" class="btn btn-gradient btn-lg px-5 rounded-pill shadow-sm fw-bold">
                    <i class="bi bi-play-fill fs-4 align-middle"></i> Başlat
                </button>
                <button id="pauseBtn" class="btn btn-glass-outline btn-lg px-4 rounded-pill shadow-sm fw-bold d-none">
                    <i class="bi bi-pause-fill fs-4 align-middle"></i> Duraklat
                </button>
                <button id="resetBtn" class="btn btn-outline-secondary btn-lg px-3 rounded-circle shadow-sm" title="Sıfırla" style="width: 50px; height: 50px;">
                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                </button>
                <button id="soundToggleBtn" class="btn btn-outline-primary btn-lg px-3 rounded-circle shadow-sm" title="Zil Sesini Aç / Kapat" style="width: 50px; height: 50px;">
                    <i class="bi bi-bell-fill fs-5" id="soundIcon"></i>
                </button>
            </div>

            <!-- Odaklanma Arka Plan Sesleri (Web Audio Sentetik) -->
            <hr class="my-4 opacity-25">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                <span class="text-muted small fw-semibold me-2"><i class="bi bi-headphones me-1"></i> Odaklanma Sesi:</span>
                <button type="button" class="btn btn-sm btn-glass-outline rounded-pill px-3 sound-btn active" data-sound="none">
                    Sessiz
                </button>
                <button type="button" class="btn btn-sm btn-glass-outline rounded-pill px-3 sound-btn" data-sound="rain">
                    🌧️ Yağmur
                </button>
                <button type="button" class="btn btn-sm btn-glass-outline rounded-pill px-3 sound-btn" data-sound="cafe">
                    ☕ Kütüphane / Kafe
                </button>
                <button type="button" class="btn btn-sm btn-glass-outline rounded-pill px-3 sound-btn" data-sound="tone">
                    🧘 432Hz Odak Dalgaları
                </button>
            </div>

        </div>

        <!-- İstatistik Kutucukları -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center">
                    <span class="text-muted small text-uppercase">Bugün Seans</span>
                    <h3 id="statTodaySessions" class="fw-bold mb-0 text-primary"><?= (int)($stats['today_sessions'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center">
                    <span class="text-muted small text-uppercase">Bugün Süre</span>
                    <h3 id="statTodayMinutes" class="fw-bold mb-0 text-success"><?= (int)($stats['today_minutes'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">dk</span></h3>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center">
                    <span class="text-muted small text-uppercase">Toplam Seans</span>
                    <h3 id="statTotalSessions" class="fw-bold mb-0 text-warning"><?= (int)($stats['total_sessions'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="glass-effect p-3 rounded-4 text-center">
                    <span class="text-muted small text-uppercase">Toplam Odaklanma</span>
                    <h3 id="statTotalMinutes" class="fw-bold mb-0 text-info"><?= (int)($stats['total_minutes'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">dk</span></h3>
                </div>
            </div>
        </div>

        <!-- Son 7 Günlük Odaklanma Grafiği -->
        <div class="glass-effect p-4 rounded-4 text-start shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-bar-chart-line-fill me-2"></i>Son 7 Günün Odaklanma Grafiği
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                    Haftalık Toplam: <?= (int)($weeklyStats['total_week_minutes'] ?? 0) ?> dakika
                </span>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div style="height: 220px; position: relative;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-lock-fill fs-3 d-block mb-2 text-secondary"></i>
                    Haftalık çalışma grafiğinizi ve istatistiklerinizi kaydetmek için lütfen <a href="<?= BASE_URL ?>/login" class="fw-bold text-primary">giriş yapın</a>.
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let mode = 'pomodoro'; // 'pomodoro', 'shortBreak', 'longBreak'
    let totalSeconds = 25 * 60;
    let remainingSeconds = totalSeconds;
    let timerInterval = null;
    let isRunning = false;
    let soundEnabled = true;
    const ringRadius = 120;
    const circumference = 2 * Math.PI * ringRadius; // ~753.98

    const timerDisplay = document.getElementById('timerDisplay');
    const progressRing = document.getElementById('progressRing');
    const startBtn = document.getElementById('startBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    const resetBtn = document.getElementById('resetBtn');
    const soundToggleBtn = document.getElementById('soundToggleBtn');
    const soundIcon = document.getElementById('soundIcon');
    const modeStatusText = document.getElementById('modeStatusText');
    const modeButtons = document.querySelectorAll('.mode-btn');

    // Ring initial setup
    progressRing.style.strokeDasharray = `${circumference} ${circumference}`;
    progressRing.style.strokeDashoffset = '0';

    function updateDisplay() {
        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        const text = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        timerDisplay.textContent = text;
        document.title = `${text} - Pomodoro | Soru Bankası`;

        // Update ring
        const progress = (totalSeconds - remainingSeconds) / totalSeconds;
        const offset = circumference - (progress * circumference);
        progressRing.style.strokeDashoffset = offset;
    }

    function switchMode(newMode, minutes) {
        pauseTimer();
        mode = newMode;
        totalSeconds = minutes * 60;
        remainingSeconds = totalSeconds;

        modeButtons.forEach(btn => {
            if (btn.dataset.mode === newMode) {
                btn.classList.add('btn-gradient', 'active');
                btn.classList.remove('btn-glass-outline');
            } else {
                btn.classList.remove('btn-gradient', 'active');
                btn.classList.add('btn-glass-outline');
            }
        });

        if (newMode === 'pomodoro') {
            modeStatusText.textContent = 'Odaklanma Zamanı';
            progressRing.style.stroke = 'var(--primary-color)';
        } else if (newMode === 'shortBreak') {
            modeStatusText.textContent = 'Kısa Mola (Dinlenin)';
            progressRing.style.stroke = '#10b981';
        } else {
            modeStatusText.textContent = 'Uzun Mola (Tazelenin)';
            progressRing.style.stroke = '#0ea5e9';
        }

        updateDisplay();
    }

    modeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            switchMode(this.dataset.mode, parseInt(this.dataset.time));
        });
    });

    function startTimer() {
        if (isRunning) return;
        isRunning = true;
        startBtn.classList.add('d-none');
        pauseBtn.classList.remove('d-none');

        timerInterval = setInterval(() => {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                updateDisplay();
            } else {
                onTimerComplete();
            }
        }, 1000);
    }

    function pauseTimer() {
        isRunning = false;
        clearInterval(timerInterval);
        pauseBtn.classList.add('d-none');
        startBtn.classList.remove('d-none');
    }

    function resetTimer() {
        pauseTimer();
        remainingSeconds = totalSeconds;
        updateDisplay();
    }

    startBtn.addEventListener('click', startTimer);
    pauseBtn.addEventListener('click', pauseTimer);
    resetBtn.addEventListener('click', resetTimer);

    // Zil Sesi Aç/Kapat
    if (soundToggleBtn) {
        soundToggleBtn.addEventListener('click', function() {
            soundEnabled = !soundEnabled;
            if (soundEnabled) {
                soundIcon.className = 'bi bi-bell-fill fs-5 text-primary';
                playChime();
            } else {
                soundIcon.className = 'bi bi-bell-slash-fill fs-5 text-muted';
            }
        });
    }

    // Audio chime using Web Audio API (Sentetik Melodik Zil)
    function playChime() {
        if (!soundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            
            // C5 (523.25Hz) -> E5 (659.25Hz) -> G5 (783.99Hz) -> C6 (1046.50Hz)
            const notes = [523.25, 659.25, 783.99, 1046.50];
            notes.forEach((freq, index) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const noteTime = now + (index * 0.15);

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, noteTime);

                gain.gain.setValueAtTime(0.4, noteTime);
                gain.gain.exponentialRampToValueAtTime(0.001, noteTime + 0.8);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(noteTime);
                osc.stop(noteTime + 0.8);
            });
        } catch(e) {}
    }

    function onTimerComplete() {
        pauseTimer();
        playChime();

        if (mode === 'pomodoro') {
            const durationMins = Math.round(totalSeconds / 60);

            // AJAX kaydet
            fetch('<?= BASE_URL ?>/pomodoro/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    duration_minutes: durationMins,
                    mode: 'pomodoro'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.stats) {
                    document.getElementById('statTodaySessions').textContent = data.stats.today_sessions;
                    document.getElementById('statTodayMinutes').innerHTML = `${data.stats.today_minutes} <span class="fs-6 fw-normal text-muted">dk</span>`;
                    document.getElementById('statTotalSessions').textContent = data.stats.total_sessions;
                    document.getElementById('statTotalMinutes').innerHTML = `${data.stats.total_minutes} <span class="fs-6 fw-normal text-muted">dk</span>`;
                    alert('🎉 Harika iş! 25 dakikalık odaklanma tamamlandı ve +5 puan kazandınız! Şimdi 5 dakikalık kısa mola zamanı.');
                } else {
                    alert('🎉 Tebrikler! Seansı başarıyla tamamladınız.');
                }
                switchMode('shortBreak', 5);
            })
            .catch(() => {
                alert('🎉 Tebrikler! Seansı tamamladınız.');
                switchMode('shortBreak', 5);
            });

        } else {
            alert('Molayı tamamladınız. Yeniden odaklanmaya hazır mısınız?');
            switchMode('pomodoro', 25);
        }
    }

    // ── Web Audio Sentetik Arka Plan Sesleri ─────────────────────────────────
    let audioCtx = null;
    let activeNoiseNode = null;
    let currentSound = 'none';

    function stopAmbientSound() {
        if (activeNoiseNode) {
            try {
                activeNoiseNode.stop();
                activeNoiseNode.disconnect();
            } catch(e) {}
            activeNoiseNode = null;
        }
    }

    function startAmbientSound(type) {
        stopAmbientSound();
        if (type === 'none') return;

        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        if (type === 'tone') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(432, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.05, audioCtx.currentTime);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            activeNoiseNode = osc;
        } else {
            const bufferSize = audioCtx.sampleRate * 2;
            const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            const output = buffer.getChannelData(0);

            if (type === 'rain') {
                let b0 = 0, b1 = 0, b2 = 0, b3 = 0, b4 = 0, b5 = 0, b6 = 0;
                for (let i = 0; i < bufferSize; i++) {
                    const white = Math.random() * 2 - 1;
                    b0 = 0.99886 * b0 + white * 0.0555179;
                    b1 = 0.99332 * b1 + white * 0.0750759;
                    b2 = 0.96900 * b2 + white * 0.1538520;
                    b3 = 0.86650 * b3 + white * 0.3104856;
                    b4 = 0.55000 * b4 + white * 0.5329522;
                    b5 = -0.7616 * b5 - white * 0.0168980;
                    output[i] = (b0 + b1 + b2 + b3 + b4 + b5 + b6 + white * 0.5362) * 0.04;
                    b6 = white * 0.115926;
                }
            } else {
                let lastOut = 0.0;
                for (let i = 0; i < bufferSize; i++) {
                    const white = Math.random() * 2 - 1;
                    output[i] = (lastOut + (0.02 * white)) / 1.02;
                    lastOut = output[i];
                    output[i] *= 0.15;
                }
            }

            const noise = audioCtx.createBufferSource();
            noise.buffer = buffer;
            noise.loop = true;
            const gain = audioCtx.createGain();
            gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
            noise.connect(gain);
            gain.connect(audioCtx.destination);
            noise.start();
            activeNoiseNode = noise;
        }
    }

    document.querySelectorAll('.sound-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sound-btn').forEach(b => b.classList.remove('btn-primary', 'active'));
            this.classList.add('btn-primary', 'active');
            currentSound = this.dataset.sound;
            startAmbientSound(currentSound);
        });
    });

    window.addEventListener('beforeunload', stopAmbientSound);

    // ── Haftalık Odaklanma Chart.js Grafiği ──────────────────────────────────
    <?php if(isset($_SESSION['user_id'])): ?>
    const weeklyCanvas = document.getElementById('weeklyChart');
    if (weeklyCanvas) {
        const weeklyLabels = <?= json_encode($weeklyStats['labels'] ?? []) ?>;
        const weeklyData = <?= json_encode($weeklyStats['data'] ?? []) ?>;

        new Chart(weeklyCanvas, {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Odaklanma Süresi (Dakika)',
                    data: weeklyData,
                    backgroundColor: 'rgba(79, 70, 229, 0.75)',
                    hoverBackgroundColor: 'rgba(79, 70, 229, 0.95)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' dakika odaklanıldı';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: function(value) { return value + ' dk'; }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php'; 
?>
