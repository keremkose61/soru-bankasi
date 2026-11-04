<?php ob_start(); ?>

<style>
.chat-container { height: calc(100vh - 180px); min-height: 550px; display: flex; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(79,70,229,0.15); }
.chat-sidebar { width: 320px; min-width: 320px; display: flex; flex-direction: column; border-right: 1px solid var(--glass-border-light); background: var(--glass-bg-light); backdrop-filter: blur(15px); }
[data-bs-theme="dark"] .chat-sidebar { background: var(--glass-bg-dark); border-color: var(--glass-border-dark); }
.chat-sidebar-header { padding: 20px; border-bottom: 1px solid var(--glass-border-light); }
[data-bs-theme="dark"] .chat-sidebar-header { border-color: var(--glass-border-dark); }
.chat-sidebar-body { overflow-y: auto; flex: 1; }
.chat-contact-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; cursor: pointer; transition: background 0.2s; text-decoration: none; color: inherit; border-left: 3px solid transparent; }
.chat-contact-item:hover { background: rgba(79,70,229,0.07); color: inherit; }
.chat-contact-item.active { background: rgba(79,70,229,0.12); border-left-color: var(--primary-color); }
[data-bs-theme="dark"] .chat-contact-item:hover { background: rgba(167,139,250,0.1); }
[data-bs-theme="dark"] .chat-contact-item.active { background: rgba(167,139,250,0.15); border-left-color: var(--primary-light); }
.chat-avatar { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.chat-avatar-placeholder { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; background: var(--primary-gradient); color: white; }
.contact-meta { flex: 1; min-width: 0; }
.contact-name { font-weight: 600; font-size: 0.92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.contact-last-msg { font-size: 0.78rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.unread-badge { background: var(--primary-gradient); color: white; border-radius: 50%; min-width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chat-main { flex: 1; display: flex; flex-direction: column; background: var(--glass-bg-light); backdrop-filter: blur(12px); }
[data-bs-theme="dark"] .chat-main { background: var(--glass-bg-dark); }
.chat-main-header { padding: 16px 24px; border-bottom: 1px solid var(--glass-border-light); display: flex; align-items: center; gap: 14px; }
[data-bs-theme="dark"] .chat-main-header { border-color: var(--glass-border-dark); }
.chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.chat-bubble-wrap { display: flex; align-items: flex-end; gap: 8px; }
.chat-bubble-wrap.mine { flex-direction: row-reverse; }
.chat-bubble { max-width: 65%; padding: 10px 16px; border-radius: 18px; font-size: 0.9rem; line-height: 1.5; word-break: break-word; position: relative; }
.chat-bubble.incoming { background: var(--glass-bg-light); border: 1px solid var(--glass-border-light); border-bottom-left-radius: 4px; }
[data-bs-theme="dark"] .chat-bubble.incoming { background: rgba(30,30,60,0.6); border-color: var(--glass-border-dark); }
.chat-bubble.outgoing { background: var(--primary-gradient); color: white; border-bottom-right-radius: 4px; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
.bubble-time { font-size: 0.68rem; opacity: 0.6; margin-top: 4px; display: block; text-align: right; }
.chat-bubble.incoming .bubble-time { text-align: left; }
.chat-input-area { padding: 16px 20px; border-top: 1px solid var(--glass-border-light); display: flex; gap: 10px; align-items: flex-end; }
[data-bs-theme="dark"] .chat-input-area { border-color: var(--glass-border-dark); }
.chat-input { flex: 1; border-radius: 24px; border: 1.5px solid var(--glass-border-light); padding: 10px 18px; resize: none; font-family: 'Poppins', sans-serif; font-size: 0.9rem; background: rgba(255,255,255,0.5); transition: border 0.2s; outline: none; max-height: 120px; overflow-y: auto; }
[data-bs-theme="dark"] .chat-input { background: rgba(17,24,39,0.5); border-color: var(--glass-border-dark); color: #f3f4f6; }
.chat-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79,70,229,0.15); }
.chat-send-btn { width: 46px; height: 46px; border-radius: 50%; background: var(--primary-gradient); border: none; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 12px rgba(79,70,229,0.3); font-size: 1.1rem; }
.chat-send-btn:hover { transform: scale(1.08); box-shadow: 0 6px 18px rgba(79,70,229,0.4); }
.chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0.5; gap: 12px; }
.date-divider { text-align: center; font-size: 0.72rem; color: #9ca3af; margin: 8px 0; position: relative; }
.date-divider span { background: rgba(255,255,255,0.6); padding: 2px 14px; border-radius: 20px; }
[data-bs-theme="dark"] .date-divider span { background: rgba(17,24,39,0.6); }
@media (max-width: 768px) {
    .chat-sidebar { width: 100%; min-width: unset; display: <?= $activeUser ? 'none' : 'flex' ?>; }
    .chat-main { display: <?= $activeUser ? 'flex' : 'none' ?>; }
    .chat-container { height: calc(100vh - 140px); border-radius: 16px; }
    .chat-bubble { max-width: 85%; }
}
</style>

<div class="chat-container glass-effect border-0">

    <!-- SOL: Konuşma Listesi -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <span style="background:var(--primary-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    <i class="bi bi-chat-dots-fill"></i> <?= __('messages') ?>
                </span>
                <?php $totalUnread = array_sum(array_column($conversations, 'unread_count')); ?>
                <?php if ($totalUnread > 0): ?>
                    <span class="unread-badge"><?= $totalUnread ?></span>
                <?php endif; ?>
            </h5>
            <!-- Yeni konuşma başlat dropdown -->
            <div class="dropdown mt-2">
                <button class="btn btn-gradient btn-sm rounded-pill w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-pencil-square"></i> <?= __('new_message') ?>
                </button>
                <ul class="dropdown-menu w-100 shadow-lg" style="border-radius:16px; border:0; max-height:260px; overflow-y:auto;">
                    <?php if (empty($contacts)): ?>
                        <li><span class="dropdown-item text-muted small"><?= __('no_contacts') ?></span></li>
                    <?php else: ?>
                        <?php foreach ($contacts as $c): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>/messages/<?= $c['id'] ?>">
                                    <?php if (!empty($c['profile_photo'])): ?>
                                        <img src="<?= BASE_URL . $c['profile_photo'] ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="chat-avatar-placeholder" style="width:32px;height:32px;font-size:0.9rem;"><i class="bi bi-person-fill"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($c['username']) ?></div>
                                        <div class="text-muted" style="font-size:0.72rem;"><?= $c['role'] === 'teacher' ? __('teacher') : ($c['role'] === 'admin' ? __('admin') : __('student')) ?></div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="chat-sidebar-body">
            <?php if (empty($conversations)): ?>
                <div class="text-center text-muted p-4 small"><?= __('no_conversations') ?></div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="<?= BASE_URL ?>/messages/<?= $conv['id'] ?>"
                       class="chat-contact-item <?= ($activeUser && $activeUser['id'] == $conv['id']) ? 'active' : '' ?>">
                        <?php if (!empty($conv['profile_photo'])): ?>
                            <img src="<?= BASE_URL . $conv['profile_photo'] ?>" class="chat-avatar">
                        <?php else: ?>
                            <div class="chat-avatar-placeholder"><i class="bi bi-person-fill"></i></div>
                        <?php endif; ?>
                        <div class="contact-meta">
                            <div class="contact-name"><?= htmlspecialchars($conv['username']) ?></div>
                            <div class="contact-last-msg">
                                <?= ($conv['last_sender_id'] == $_SESSION['user_id']) ? '✓ ' : '' ?>
                                <?= htmlspecialchars(mb_substr($conv['last_message'], 0, 35)) ?>
                                <?= mb_strlen($conv['last_message']) > 35 ? '...' : '' ?>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            <small class="text-muted" style="font-size:0.68rem;"><?= date('H:i', strtotime($conv['last_at'])) ?></small>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SAĞ: Sohbet Paneli -->
    <div class="chat-main">
        <?php if ($activeUser): ?>
            <!-- Header -->
            <div class="chat-main-header">
                <a href="<?= BASE_URL ?>/messages" class="btn btn-sm btn-light rounded-circle d-md-none me-1" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <?php if (!empty($activeUser['profile_photo'])): ?>
                    <img src="<?= BASE_URL . $activeUser['profile_photo'] ?>" class="chat-avatar">
                <?php else: ?>
                    <div class="chat-avatar-placeholder"><i class="bi bi-person-fill"></i></div>
                <?php endif; ?>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($activeUser['username']) ?></div>
                    <small class="text-muted" style="font-size:0.75rem;">
                        <?= $activeUser['role'] === 'teacher' ? __('teacher') : ($activeUser['role'] === 'admin' ? __('admin') : __('student')) ?>
                        &nbsp;<span class="text-success" id="online-dot">●</span>
                    </small>
                </div>
            </div>

            <!-- Mesajlar -->
            <div class="chat-messages" id="chat-messages">
                <?php
                $lastDate = '';
                foreach ($messages as $msg):
                    $msgDate = date('d.m.Y', strtotime($msg['created_at']));
                    $isMine  = ($msg['sender_id'] == $_SESSION['user_id']);
                    if ($msgDate !== $lastDate):
                        $lastDate = $msgDate;
                ?>
                        <div class="date-divider"><span><?= $msgDate ?></span></div>
                <?php endif; ?>
                    <div class="chat-bubble-wrap <?= $isMine ? 'mine' : '' ?>" data-id="<?= $msg['id'] ?>">
                        <?php if (!$isMine && !empty($msg['sender_photo'])): ?>
                            <img src="<?= BASE_URL . $msg['sender_photo'] ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        <?php elseif (!$isMine): ?>
                            <div style="width:28px;height:28px;border-radius:50%;background:var(--primary-gradient);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.75rem;color:white;"><i class="bi bi-person-fill"></i></div>
                        <?php endif; ?>
                        <div class="chat-bubble <?= $isMine ? 'outgoing' : 'incoming' ?>">
                            <?= nl2br(htmlspecialchars($msg['content'])) ?>
                            <span class="bubble-time"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mesaj Gönder -->
            <div class="chat-input-area">
                <textarea id="msg-input" class="chat-input" rows="1"
                    placeholder="<?= __('message_placeholder') ?>"
                    maxlength="2000"></textarea>
                <button class="chat-send-btn" id="send-btn" title="<?= __('send_message') ?>">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>

            <script>
            (function() {
                const chatBox    = document.getElementById('chat-messages');
                const input      = document.getElementById('msg-input');
                const sendBtn    = document.getElementById('send-btn');
                const receiverId = <?= (int)$activeUser['id'] ?>;
                const myId       = <?= (int)$_SESSION['user_id'] ?>;
                let lastId       = <?= $lastMessageId ?>;
                const baseUrl    = <?= json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

                // Scroll to bottom
                function scrollBottom() {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
                scrollBottom();

                // Auto-resize textarea
                input.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });

                // Mesaj balonu oluştur
                function createBubble(msg) {
                    const wrap = document.createElement('div');
                    wrap.className = 'chat-bubble-wrap' + (msg.is_mine ? ' mine' : '');
                    wrap.dataset.id = msg.id;

                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble ' + (msg.is_mine ? 'outgoing' : 'incoming');
                    // Render user content as text nodes; never parse message text as HTML.
                    const messageText = String(msg.content ?? '');
                    const parts = messageText.split('\\n');
                    parts.forEach((part, index) => {
                        if (index > 0) bubble.appendChild(document.createElement('br'));
                        bubble.appendChild(document.createTextNode(part));
                    });

                    const time = document.createElement('span');
                    time.className = 'bubble-time';
                    time.textContent = String(msg.created_at ?? '');
                    bubble.appendChild(time);

                    if (!msg.is_mine) {
                        const av = document.createElement('div');
                        av.style.cssText = 'width:28px;height:28px;border-radius:50%;background:var(--primary-gradient);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.75rem;color:white;';
                        av.innerHTML = '<i class="bi bi-person-fill"></i>';
                        wrap.appendChild(av);
                    }
                    wrap.appendChild(bubble);
                    return wrap;
                }

                // Mesaj gönder
                async function sendMessage() {
                    const text = input.value.trim();
                    if (!text) return;
                    input.value = '';
                    input.style.height = 'auto';
                    sendBtn.disabled = true;

                    try {
                        const fd = new FormData();
                        fd.append('receiver_id', receiverId);
                        fd.append('content', text);

                        const res  = await fetch(baseUrl + '/messages/send', { method: 'POST', body: fd });
                        const data = await res.json();

                        if (data.success) {
                            lastId = data.id;
                            chatBox.appendChild(createBubble({ ...data, is_mine: true }));
                            scrollBottom();
                        }
                    } catch(e) {}
                    sendBtn.disabled = false;
                    input.focus();
                }

                sendBtn.addEventListener('click', sendMessage);
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
                });

                // Polling — 3 saniyede bir yeni mesaj çek
                setInterval(async function() {
                    try {
                        const res  = await fetch(baseUrl + '/messages/poll/' + receiverId + '/' + lastId);
                        const data = await res.json();

                        if (data.messages && data.messages.length > 0) {
                            data.messages.forEach(msg => {
                                if (!document.querySelector('[data-id="' + msg.id + '"]')) {
                                    chatBox.appendChild(createBubble(msg));
                                    lastId = Math.max(lastId, msg.id);
                                }
                            });
                            scrollBottom();
                        }

                        // Navbar badge güncelle
                        const badge = document.getElementById('msg-nav-badge');
                        if (badge) {
                            badge.textContent = data.unread || '';
                            badge.style.display = data.unread > 0 ? 'flex' : 'none';
                        }
                    } catch(e) {}
                }, 3000);
            })();
            </script>

        <?php else: ?>
            <!-- Konuşma seçilmedi -->
            <div class="chat-empty">
                <i class="bi bi-chat-dots" style="font-size:4rem; color:var(--primary-color);"></i>
                <h5 class="fw-bold"><?= __('select_contact') ?></h5>
                <p class="text-muted small text-center px-4"><?= __('messages_welcome') ?></p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
