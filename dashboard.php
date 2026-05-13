



<?php
 $pageTitle = 'Tableau de bord';
require_once 'includes/auth.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();

// Stats based on role
if ($currentUser['role'] === 'benevole') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures WHERE benevole_id = ?");
    $stmt->execute([$currentUser['id']]);
    $candidaturesCount = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures WHERE benevole_id = ? AND status = 'acceptee'");
    $stmt->execute([$currentUser['id']]);
    $missionsAcceptees = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures WHERE benevole_id = ? AND status = 'en_attente'");
    $stmt->execute([$currentUser['id']]);
    $enAttente = $stmt->fetchColumn();
    
    // Recent candidatures
    $stmt = $db->prepare("
        SELECT c.*, m.title, m.date_mission, m.city, u.first_name, u.last_name
        FROM candidatures c
        JOIN missions m ON c.mission_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE c.benevole_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['id']]);
    $recentCandidatures = $stmt->fetchAll();
    
} elseif ($currentUser['role'] === 'demandeur') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM missions WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $missionsCount = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM missions WHERE user_id = ? AND status = 'ouverte'");
    $stmt->execute([$currentUser['id']]);
    $missionsOuvertes = $stmt->fetchColumn();
    
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM candidatures c
        JOIN missions m ON c.mission_id = m.id
        WHERE m.user_id = ? AND c.status = 'en_attente'
    ");
    $stmt->execute([$currentUser['id']]);
    $candidaturesEnAttente = $stmt->fetchColumn();
    
    // Recent missions
    $stmt = $db->prepare("
        SELECT m.*, 
            (SELECT COUNT(*) FROM candidatures WHERE mission_id = m.id AND status = 'acceptee') as accepted_count
        FROM missions m
        WHERE m.user_id = ?
        ORDER BY m.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['id']]);
    $recentMissions = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <?php if (isset($_GET['welcome'])): ?>
        <div class="alert alert-success" style="background: rgba(45, 198, 83, 0.1); border: 1px solid rgba(45, 198, 83, 0.2); color: var(--success); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
            Bienvenue <?= htmlspecialchars($currentUser['first_name']) ?> ! Votre compte a ete cree avec succes.
        </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="user-card">
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></div>
                        <div class="user-role"><?= ucfirst($currentUser['role']) ?></div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">Navigation</div>
                    <nav class="sidebar-nav">
                        <a href="dashboard.php" class="sidebar-link active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            Tableau de bord
                        </a>
                        <a href="missions.php" class="sidebar-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Rechercher
                        </a>
                        <a href="messages.php" class="sidebar-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Messages
                            <?php $unreadCount = getUnreadMessagesCount($currentUser['id']); ?>
                            <?php if ($unreadCount > 0): ?>
                            <span class="badge"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if ($currentUser['role'] === 'demandeur'): ?>
                        <a href="mission-create.php" class="sidebar-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Publier une mission
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </aside>
            
            <div class="dashboard-content">
                <h2 style="margin-bottom: 1.5rem;">Bienvenue, <?= htmlspecialchars($currentUser['first_name']) ?></h2>
                
                <?php if ($currentUser['role'] === 'benevole'): ?>
                <!-- Stats Benevole -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon accent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                    <polyline points="10 9 9 9 8 9"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $candidaturesCount ?></div>
                        <div class="stat-card-label">Candidatures</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $missionsAcceptees ?></div>
                        <div class="stat-card-label">Missions acceptees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $enAttente ?></div>
                        <div class="stat-card-label">En attente</div>
                    </div>
                </div>
                
                <!-- Recent candidatures -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>Mes candidatures recentes</h3>
                        <a href="missions.php" class="btn btn-ghost btn-sm">Voir tout</a>
                    </div>
                    <?php if (empty($recentCandidatures)): ?>
                    <div class="empty-state" style="padding: 2rem;">
                        <p class="text-muted">Aucune candidature pour le moment</p>
                        <a href="missions.php" class="btn btn-primary mt-2">Trouver des missions</a>
                    </div>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Mission</th>
                                    <th>Date</th>
                                    <th>Demandeur</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCandidatures as $c): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($c['date_mission'])) ?></td>
                                    <td><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $c['status'] === 'acceptee' ? 'ouverte' : ($c['status'] === 'en_attente' ? 'ouverte' : 'annulee') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php elseif ($currentUser['role'] === 'demandeur'): ?>
                <!-- Stats Demandeur -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon accent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $missionsCount ?></div>
                        <div class="stat-card-label">Missions publiees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="16"/>
                                    <line x1="8" y1="12" x2="16" y2="12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $missionsOuvertes ?></div>
                        <div class="stat-card-label">Missions ouvertes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-icon warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $candidaturesEnAttente ?></div>
                        <div class="stat-card-label">Candidatures en attente</div>
                    </div>
                </div>
                
                <!-- Recent missions -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>Mes missions</h3>
                        <a href="mission-create.php" class="btn btn-primary btn-sm">Nouvelle mission</a>
                    </div>
                    <?php if (empty($recentMissions)): ?>
                    <div class="empty-state" style="padding: 2rem;">
                        <p class="text-muted">Aucune mission publiee</p>
                        <a href="mission-create.php" class="btn btn-primary mt-2">Publier une mission</a>
                    </div>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Date</th>
                                    <th>Categorie</th>
                                    <th>Benevoles</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMissions as $m): ?>
                                <tr>
                                    <td>
                                        <a href="mission-details.php?id=<?= $m['id'] ?>"><strong><?= htmlspecialchars($m['title']) ?></strong></a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($m['date_mission'])) ?></td>
                                    <td><?= htmlspecialchars($m['category']) ?></td>
                                    <td><?= $m['accepted_count'] ?>/<?= $m['max_benevoles'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $m['status'] ?>">
                                            <?= ucfirst($m['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>