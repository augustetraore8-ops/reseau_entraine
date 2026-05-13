


<?php
 $pageTitle = 'Missions disponibles';
require_once 'includes/header.php';

 $db = getDB();

// Filters
 $search = trim($_GET['search'] ?? '');
 $category = $_GET['category'] ?? '';
 $city = trim($_GET['city'] ?? '');
 $urgency = $_GET['urgency'] ?? '';

 $query = "
    SELECT m.*, u.first_name, u.last_name, u.city as user_city,
           (SELECT COUNT(*) FROM candidatures WHERE mission_id = m.id AND status = 'acceptee') as accepted_count
    FROM missions m
    JOIN users u ON m.user_id = u.id
    WHERE m.status = 'ouverte'
";

 $params = [];

if ($search) {
    $query .= " AND (m.title LIKE ? OR m.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND m.category = ?";
    $params[] = $category;
}

if ($city) {
    $query .= " AND m.city LIKE ?";
    $params[] = "%$city%";
}

if ($urgency) {
    $query .= " AND m.urgency = ?";
    $params[] = $urgency;
}

 $query .= " ORDER BY m.created_at DESC";

 $stmt = $db->prepare($query);
 $stmt->execute($params);
 $missions = $stmt->fetchAll();

 $categories = ['compagnie', 'courses', 'jardinage', 'bricolage', 'demenagement', 'soutien_scolaire', 'administratif', 'autre'];
?>

<main>
    <div class="container">
        <div style="margin-bottom: 2rem;">
            <h1>Missions disponibles</h1>
            <p class="text-muted">Trouvez une mission qui vous correspond</p>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="filters-bar">
            <div class="filter-group" style="flex: 2;">
                <label class="filter-label">Rechercher</label>
                <input type="text" name="search" class="form-input" value="<?= htmlspecialchars($search) ?>" placeholder="Titre ou description...">
            </div>
            <div class="filter-group">
                <label class="filter-label">Categorie</label>
                <select name="category" class="form-select">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $cat)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Ville</label>
                <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($city) ?>" placeholder="Ville...">
            </div>
            <div class="filter-group">
                <label class="filter-label">Urgence</label>
                <select name="urgency" class="form-select">
                    <option value="">Toutes</option>
                    <option value="faible" <?= $urgency === 'faible' ? 'selected' : '' ?>>Faible</option>
                    <option value="moyenne" <?= $urgency === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                    <option value="elevee" <?= $urgency === 'elevee' ? 'selected' : '' ?>>Elevee</option>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="missions.php" class="btn btn-ghost">Reinitialiser</a>
            </div>
        </form>
        
        <?php if (empty($missions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <h3 class="empty-state-title">Aucune mission trouvee</h3>
            <p class="empty-state-text">Essayez de modifier vos criteres de recherche</p>
        </div>
        <?php else: ?>
        <p class="text-muted mb-3"><?= count($missions) ?> mission(s) trouvee(s)</p>
        
        <div class="missions-grid">
            <?php foreach ($missions as $mission): ?>
            <a href="mission-details.php?id=<?= $mission['id'] ?>" class="card mission-card">
                
                <!-- Affichage Catégorie et Rémunération -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span class="mission-category"><?= str_replace('_', ' ', $mission['category']) ?></span>
                    
                    <?php if (!empty($mission['remuneration'])): ?>
                    <span class="badge" style="background: rgba(45, 198, 83, 0.15); color: var(--success); border: 1px solid var(--success); font-size: 0.8rem;">
                        💰 <?= htmlspecialchars($mission['remuneration']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <h3 class="mission-title"><?= htmlspecialchars($mission['title']) ?></h3>
                <p class="text-muted"><?= substr(htmlspecialchars($mission['description']), 0, 150) ?>...</p>
                
                <div class="mission-meta">
                    <div class="mission-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= htmlspecialchars($mission['city']) ?>
                    </div>
                    <div class="mission-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?= date('d/m/Y', strtotime($mission['date_mission'])) ?>
                    </div>
                    <div class="mission-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <?= $mission['time_start'] ?>
                    </div>
                </div>
                
                <div class="card-footer">
                    <span class="urgency-badge urgency-<?= $mission['urgency'] ?>">
                        <?= ucfirst($mission['urgency']) ?>
                    </span>
                    <span class="text-muted" style="margin-left: auto;">
                        <?= $mission['accepted_count'] ?>/<?= $mission['max_benevoles'] ?> benevole(s)
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>