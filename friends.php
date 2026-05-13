






<?php
 $pageTitle = 'Mes Amis';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();

// Accepter une demande
if (isset($_GET['accept'])) {
    $id = (int)$_GET['accept'];
    $db->prepare("UPDATE friends SET status = 'accepted' WHERE id = ? AND friend_id = ?")->execute([$id, $currentUser['id']]);
    header('Location: friends.php');
    exit;
}

// Récupérer les demandes reçues (en attente)
 $stmt = $db->prepare("
    SELECT f.id as request_id, u.id as user_id, u.first_name, u.last_name, u.school 
    FROM friends f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.friend_id = ? AND f.status = 'pending'
");
 $stmt->execute([$currentUser['id']]);
 $requests = $stmt->fetchAll();

// Récupérer la liste d'amis
 $stmt = $db->prepare("
    SELECT u.id, u.first_name, u.last_name, u.city, u.school 
    FROM friends f 
    JOIN users u ON (u.id = f.user_id OR u.id = f.friend_id)
    WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted' AND u.id != ?
");
 $stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
 $friends = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main>
    <div class="container" style="max-width: 800px;">
        <h1 style="margin-bottom: 2rem;">Mon Réseau</h1>

        <!-- Demandes en attente -->
        <?php if (!empty($requests)): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">📩 Nouvelles demandes</h3>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($requests as $r): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-elevated); padding: 1rem; border-radius: 8px;">
                    <div>
                        <strong><?= htmlspecialchars($r['first_name']) ?></strong>
                        <p class="text-muted" style="font-size: 0.8rem; margin:0;">
                            <?= htmlspecialchars($r['school'] ?? 'École non renseignée') ?>
                        </p>
                    </div>
                    <a href="?accept=<?= $r['request_id'] ?>" class="btn btn-success btn-sm">Accepter</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste d'amis -->
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Mes Amis (<?= count($friends) ?>)</h3>
            <?php if (empty($friends)): ?>
                <p class="text-muted">Vous n'avez pas encore d'amis. <a href="community.php">Trouvez des camarades</a> !</p>
            <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($friends as $f): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <strong><?= htmlspecialchars($f['first_name'] . ' ' . $f['last_name']) ?></strong><br>
                        <small class="text-muted">📍 <?= htmlspecialchars($f['city'] ?? 'N/A') ?> | 🎓 <?= htmlspecialchars($f['school'] ?? 'N/A') ?></small>
                    </div>
                    <a href="messages.php?to=<?= $f['id'] ?>" class="btn btn-sm btn-secondary">Discuter</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>