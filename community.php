


<?php
 $pageTitle = 'Communauté';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();

// Traitement de l'ajout d'ami
if (isset($_GET['add'])) {
    $friendId = (int)$_GET['add'];
    if ($friendId != $currentUser['id']) {
        $check = $db->prepare("SELECT id FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
        $check->execute([$currentUser['id'], $friendId, $friendId, $currentUser['id']]);
        
        if (!$check->fetch()) {
            $stmt = $db->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$currentUser['id'], $friendId]);
        }
    }
    header('Location: community.php');
    exit;
}

// Mise à jour du profil école si POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['school'])) {
    $school = trim($_POST['school']);
    $db->prepare("UPDATE users SET school = ? WHERE id = ?")->execute([$school, $currentUser['id']]);
    header('Location: community.php');
    exit;
}

// Recherche et Filtres
 $search = trim($_GET['search'] ?? '');
 $schoolFilter = trim($_GET['school'] ?? '');

 $sql = "SELECT id, first_name, last_name, city, school, role FROM users WHERE id != ? AND is_active = 1";
 $params = [$currentUser['id']];

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR city LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($schoolFilter) {
    $sql .= " AND school LIKE ?";
    $params[] = "%$schoolFilter%";
}

 $sql .= " ORDER BY created_at DESC LIMIT 20";

 $stmt = $db->prepare($sql);
 $stmt->execute($params);
 $users = $stmt->fetchAll();

// Récupérer les IDs des amis
 $myFriends = [];
 $stmt = $db->prepare("SELECT friend_id, user_id, status FROM friends WHERE user_id = ? OR friend_id = ?");
 $stmt->execute([$currentUser['id'], $currentUser['id']]);
foreach ($stmt->fetchAll() as $f) {
    $key = $f['user_id'] == $currentUser['id'] ? $f['friend_id'] : $f['user_id'];
    $myFriends[$key] = $f['status'];
}

// Compter les camarades (VERSION CORRIGEE COMPATIBLE)
 $mateCount = 0;
if (!empty($currentUser['school'])) {
    foreach ($users as $u) {
        if (stripos($u['school'], $currentUser['school']) !== false) {
            $mateCount++;
        }
    }
}

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h1>Communauté & Étudiants</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="friends.php" class="btn btn-secondary">Mes Amis</a>
            </div>
        </div>

        <!-- Carte "Mon Etablissement" -->
        <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--bg-card), var(--bg-elevated));">
            <h3>🎓 Mon Établissement (Tunisie)</h3>
            <?php if (empty($currentUser['school'])): ?>
            <p class="text-muted">Indiquez votre école ou université pour retrouver vos camarades de promotion !</p>
            <form method="POST" style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <input type="text" name="school" class="form-input" placeholder="Ex: ESPRIT, IHEC, FST, Sup'Com..." required style="flex:1;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
            
            <div style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <small class="text-muted">Suggestions :</small>
                <form method="POST"><button type="submit" name="school" value="ESPRIT" class="btn btn-ghost btn-sm" style="padding: 4px 8px; font-size: 0.8rem;">ESPRIT</button></form>
                <form method="POST"><button type="submit" name="school" value="Université de Tunis" class="btn btn-ghost btn-sm" style="padding: 4px 8px; font-size: 0.8rem;">Univ. Tunis</button></form>
                <form method="POST"><button type="submit" name="school" value="IHEC Carthage" class="btn btn-ghost btn-sm" style="padding: 4px 8px; font-size: 0.8rem;">IHEC</button></form>
            </div>

            <?php else: ?>
            <p>Vous êtes inscrit à : <strong style="color: var(--accent)"><?= htmlspecialchars($currentUser['school']) ?></strong></p>
            <a href="?school=<?= urlencode($currentUser['school']) ?>" class="btn btn-ghost btn-sm" style="margin-top: 0.5rem;">
                Voir mes camarades (<?= $mateCount ?>)
            </a>
            <?php endif; ?>
        </div>

        <!-- Recherche -->
        <form method="GET" class="filters-bar">
            <input type="text" name="search" class="form-input" placeholder="Rechercher un prénom, une ville..." value="<?= htmlspecialchars($search) ?>">
            <input type="text" name="school" class="form-input" placeholder="Filtrer par école..." value="<?= htmlspecialchars($schoolFilter) ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <!-- Liste des utilisateurs -->
        <div class="missions-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
            <?php foreach ($users as $u): ?>
            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto 1rem;">
                    <?= strtoupper(substr($u['first_name'], 0, 1)) ?>
                </div>
                <h3 style="font-size: 1.1rem;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></h3>
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                    <?= ucfirst($u['role']) ?>
                    <?php if ($u['school']): ?>
                    <br>🎓 <?= htmlspecialchars($u['school']) ?>
                    <?php endif; ?>
                </p>
                <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 1rem;">
                    📍 <?= htmlspecialchars($u['city'] ?? 'Non renseigné') ?>
                </p>
                
                <div style="display: flex; justify-content: center; gap: 0.5rem;">
                    <?php if (isset($myFriends[$u['id']])): ?>
                        <?php if ($myFriends[$u['id']] === 'accepted'): ?>
                            <span class="badge status-ouverte">Ami ✓</span>
                        <?php else: ?>
                            <span class="badge status-en_attente">Demande envoyée</span>
                        <?php endif; ?>
                    <?php else: ?>
                    <a href="?add=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Ajouter</a>
                    <?php endif; ?>
                    <a href="messages.php?to=<?= $u['id'] ?>" class="btn btn-sm btn-ghost">Message</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>