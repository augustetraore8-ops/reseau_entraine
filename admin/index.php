

<?php
 $pageTitle = 'Accueil';
require_once 'includes/auth.php';
require_once 'config/database.php';

 $db = getDB();

// Récupération des statistiques
 $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'benevole'");
 $benevolesCount = $stmt->fetchColumn();

 $stmt = $db->query("SELECT COUNT(*) FROM missions WHERE status = 'ouverte'");
 $missionsCount = $stmt->fetchColumn();

// Récupération des missions récentes
 $stmt = $db->query("
    SELECT m.*, u.first_name, u.city 
    FROM missions m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.status = 'ouverte' 
    ORDER BY m.created_at DESC 
    LIMIT 6
");
 $recentMissions = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main>
    <!-- Section Héro -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Créez du lien,<br>Changez des vies</h1>
            <p class="hero-subtitle">
                Connectez-vous avec des bénévoles et des personnes ayant besoin d'aide dans votre quartier.
            </p>
            <div class="hero-actions">
                <a href="register.php" class="btn btn-primary btn-lg">Rejoindre la communauté</a>
                <a href="missions.php" class="btn btn-secondary btn-lg">Voir les missions</a>
            </div>
            
            <!-- Statistiques -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= $benevolesCount ?></div>
                    <div class="text-muted">Bénévoles actifs</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= $missionsCount ?></div>
                    <div class="text-muted">Missions disponibles</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Missions Récentes -->
    <section class="container" style="padding: 4rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Missions récentes</h2>
            <a href="missions.php" class="btn btn-ghost">Voir tout &rarr;</a>
        </div>

        <?php if (empty($recentMissions)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <p class="text-muted">Aucune mission disponible pour le moment.</p>
            <p class="text-muted" style="font-size: 0.9rem; margin-top: 0.5rem;">Soyez le premier à proposer de l'aide !</p>
        </div>
        <?php else: ?>
        <div class="missions-grid">
            <?php foreach ($recentMissions as $m): ?>
            <a href="mission-details.php?id=<?= $m['id'] ?>" class="card mission-card">
                <span class="mission-category"><?= htmlspecialchars($m['category']) ?></span>
                <h3 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($m['title']) ?></h3>
                <p class="text-muted" style="font-size: 0.9rem;">
                    <?= substr(htmlspecialchars($m['description']), 0, 100) ?>...
                </p>
                <div class="mission-meta">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?= htmlspecialchars($m['city']) ?>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?= date('d/m/Y', strtotime($m['date_mission'])) ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- Section Présentation -->
    <section style="background: var(--bg-secondary); padding: 4rem 0; margin-top: 2rem;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; text-align: center;">
                <div>
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🤝</div>
                    <h3>Entraide Locale</h3>
                    <p class="text-muted">Une plateforme dédiée au lien social entre étudiants et personnes dans le besoin.</p>
                </div>
                <div>
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⚡</div>
                    <h3>Réactivité</h3>
                    <p class="text-muted">Trouvez de l'aide rapidement grâce à notre système de mise en relation simplifié.</p>
                </div>
                <div>
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🔒</div>
                    <h3>Confiance</h3>
                    <p class="text-muted">Un espace sécurisé et modéré pour des échanges sereins.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>