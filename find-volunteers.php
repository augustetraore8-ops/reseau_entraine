


<?php
 $pageTitle = 'Trouver des Bénévoles';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $db = getDB();
 $search = trim($_GET['search'] ?? '');

// Recherche de bénévoles
 $sql = "SELECT id, first_name, last_name, city, school FROM users WHERE role = 'benevole' AND is_active = 1";
 $params = [];

if ($search) {
    $sql .= " AND (city LIKE ? OR school LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

 $stmt = $db->prepare($sql);
 $stmt->execute($params);
 $volunteers = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <h1 style="margin-bottom: 0.5rem;">Trouver des Bénévoles</h1>
        <p class="text-muted" style="margin-bottom: 2rem;">Recherchez des étudiants disponibles par ville ou école.</p>

        <form method="GET" class="filters-bar" style="margin-bottom: 2rem;">
            <input type="text" name="search" class="form-input" placeholder="Rechercher par ville ou école..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <div class="missions-grid">
            <?php foreach ($volunteers as $v): ?>
            <div class="card">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div class="user-avatar"><?= strtoupper(substr($v['first_name'], 0, 1)) ?></div>
                    <div>
                        <h3 style="font-size: 1rem; margin: 0;"><?= htmlspecialchars($v['first_name']) ?></h3>
                        <p class="text-muted" style="font-size: 0.85rem; margin: 0;">Bénévole</p>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.9rem;">
                    📍 <?= htmlspecialchars($v['city'] ?? 'Non renseigné') ?><br>
                    🎓 <?= htmlspecialchars($v['school'] ?? 'Non renseigné') ?>
                </p>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                    <a href="messages.php?to=<?= $v['id'] ?>" class="btn btn-primary btn-sm btn-block">Contacter</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>