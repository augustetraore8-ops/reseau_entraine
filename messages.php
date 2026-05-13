




<?php
 $pageTitle = 'Messages';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();

// Identifiants pour nouvelle conversation
 $toUserId = (int)($_GET['to'] ?? 0);
 $missionId = (int)($_GET['mission'] ?? 0);

// Si on veut démarrer une nouvelle conversation
if ($toUserId && $toUserId != $currentUser['id']) {
    // Vérifier si conversation existe déjà
    $stmt = $db->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([$currentUser['id'], $toUserId, $toUserId, $currentUser['id']]);
    $existingConv = $stmt->fetch();

    if ($existingConv) {
        $conversationId = $existingConv['id'];
    } else {
        // Créer la nouvelle conversation
        $stmt = $db->prepare("INSERT INTO conversations (user1_id, user2_id, mission_id) VALUES (?, ?, ?)");
        $stmt->execute([$currentUser['id'], $toUserId, $missionId ?: null]);
        $conversationId = $db->lastInsertId();
    }
    header("Location: messages.php?id=$conversationId");
    exit;
}

// Récupérer ID conversation active
 $conversationId = (int)($_GET['id'] ?? 0);

// Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conversationId) {
    $msg = trim($_POST['content'] ?? '');
    if ($msg) {
        $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$conversationId, $currentUser['id'], $msg]);
        header("Location: messages.php?id=$conversationId");
        exit;
    }
}

// Liste des conversations
 $stmt = $db->prepare("
    SELECT c.id, 
           CASE WHEN c.user1_id = ? THEN c.user2_id ELSE c.user1_id END as other_id
    FROM conversations c
    WHERE c.user1_id = ? OR c.user2_id = ?
    ORDER BY c.created_at DESC
");
 $stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
 $conversations = $stmt->fetchAll();

// Messages de la conversation active
 $messages = [];
 $otherUser = null;
if ($conversationId) {
    // Vérifier que l'utilisateur fait partie de la conversation
    $stmt = $db->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
    $stmt->execute([$conversationId, $currentUser['id'], $currentUser['id']]);
    $conv = $stmt->fetch();
    
    if ($conv) {
        $otherId = ($conv['user1_id'] == $currentUser['id']) ? $conv['user2_id'] : $conv['user1_id'];
        $stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$otherId]);
        $otherUser = $stmt->fetch();

        // Récupérer messages
        $stmt = $db->prepare("
            SELECT m.*, u.first_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.conversation_id = ? 
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll();

        // Marquer comme lus
        $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?")->execute([$conversationId, $currentUser['id']]);
    }
}

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 style="margin-bottom: 1.5rem;">Messagerie</h1>
        
        <div class="messages-container">
            <!-- Liste conversations -->
            <aside class="conversations-list">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border); font-weight: 600;">Discussions</div>
                <?php if (empty($conversations)): ?>
                    <div style="padding: 1rem; text-align: center; color: var(--fg-muted);">Aucune discussion.</div>
                <?php else: ?>
                    <?php foreach ($conversations as $c): 
                        $stmt = $db->prepare("SELECT id, first_name FROM users WHERE id = ?");
                        $stmt->execute([$c['other_id']]);
                        $u = $stmt->fetch();
                    ?>
                    <a href="messages.php?id=<?= $c['id'] ?>" class="conversation-item <?= $conversationId == $c['id'] ? 'active' : '' ?>">
                        <div class="user-avatar"><?= strtoupper(substr($u['first_name'],0,1)) ?></div>
                        <div style="flex:1;"><?= htmlspecialchars($u['first_name']) ?></div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </aside>

            <!-- Zone de chat -->
            <div class="chat-area">
                <?php if ($conversationId && $otherUser): ?>
                    <!-- En-tête -->
                    <div class="chat-header" style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                        <div class="user-avatar"><?= strtoupper(substr($otherUser['first_name'],0,1)) ?></div>
                        <div>
                            <strong><?= htmlspecialchars($otherUser['first_name']) ?></strong>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="chat-messages" id="chatBox">
                        <?php foreach($messages as $m): ?>
                        <div class="message <?= $m['sender_id'] == $currentUser['id'] ? 'sent' : 'received' ?>">
                            <div><?= htmlspecialchars($m['content']) ?></div>
                            <small style="opacity: 0.7; font-size: 0.75rem; display: block; margin-top: 4px;">
                                <?= date('H:i', strtotime($m['created_at'])) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulaire -->
                    <form class="chat-input" method="POST">
                        <input type="text" name="content" class="form-input" placeholder="Écrivez votre message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>

                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--fg-muted); text-align: center;">
                        <div>
                            <div style="font-size: 3rem; opacity: 0.5;">💬</div>
                            <p>Sélectionnez une conversation<br>ou contactez un utilisateur depuis une mission.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    // Scroll automatique vers le bas
    const chatBox = document.getElementById('chatBox');
    if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>