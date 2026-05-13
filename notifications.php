



<?php
 $pageTitle = 'Notifications';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();

// Marquer toutes les notifications comme lues
if (isset($_GET['mark_read'])) {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$currentUser['id']]);
    header('Location: notifications.php');
    exit;
}

// Récupérer les notifications
 $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
 $stmt->execute([$currentUser['id']]);
 $notifications = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main>
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Notifications</h1>
            <?php if (!empty($notifications)): ?>
            <a href="?mark_read=1" class="btn btn-ghost btn-sm">Tout marquer comme lu</a>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔔</div>
            <h3>Aucune notification</h3>
            <p class="text-muted">Vous êtes à jour !</p>
        </div>
        <?php else: ?>
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($notifications as $n): ?>
            <div class="card" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; border-left: 4px solid <?= $n['is_read'] ? 'var(--border)' : 'var(--accent)' ?>; opacity: <?= $n['is_read'] ? '0.7' : '1' ?>;">
                
                <!-- Icône selon le type -->
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <?php 
                        $icons = [
                            'candidature' => '📝',
                            'friend'      => '👤',
                            'message'     => '💬',
                            'default'     => '🔔'
                        ];
                        echo $icons[$n['type']] ?? $icons['default'];
                    ?>
                </div>

                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 0.25rem;"><?= htmlspecialchars($n['title']) ?></strong>
                    <p class="text-muted" style="font-size: 0.9rem; margin: 0;">
                        <?= htmlspecialchars($n['content']) ?>
                    </p>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <?= date('d/m/Y à H:i', strtotime($n['created_at'])) ?>
                    </small>
                </div>

                <?php if ($n['link']): ?>
                <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-secondary">Voir</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php']; ?>