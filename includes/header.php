<?php
require_once __DIR__ . '/auth.php';

 $currentUser = getCurrentUser();
 $unreadCount = $currentUser ? getUnreadMessagesCount($currentUser['id']) : 0;

 $current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Reseau Entraide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span>Reseau Entraide</span>
                    <!-- MARQUEUR DE DEBUG : Si vous voyez ceci, le fichier est bien mis à jour -->
                    <span style="color: var(--danger); font-size: 0.7rem; font-weight: bold; margin-left: 10px;">(MENU MIS À JOUR)</span>
                </a>
                
                <?php if ($currentUser): ?>
                <!-- MENU PRINCIPAL (Caché sur mobile, visible sur PC) -->
                <nav class="nav" id="mainNav">
                    <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a>
                    
                    <!-- LIENS SPÉCIFIQUES AU RÔLE -->
                    <?php if ($currentUser['role'] === 'demandeur'): ?>
                        <a href="find-volunteers.php" class="nav-link <?= $current_page === 'find-volunteers.php' ? 'active' : '' ?>">Trouver des bénévoles</a>
                        <a href="mission-create.php" class="nav-link <?= $current_page === 'mission-create.php' ? 'active' : '' ?>">Publier</a>
                    <?php elseif ($currentUser['role'] === 'benevole'): ?>
                        <!-- Lien spécifique bénévole si besoin -->
                    <?php endif; ?>

                    <!-- LIENS COMMUNS -->
                    <a href="missions.php" class="nav-link <?= $current_page === 'missions.php' ? 'active' : '' ?>">Missions</a>
                    <a href="community.php" class="nav-link <?= $current_page === 'community.php' ? 'active' : '' ?>">Communauté</a>

                    <a href="messages.php" class="nav-link <?= $current_page === 'messages.php' ? 'active' : '' ?>">
                        Messages
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="nav-link">Admin</a>
                    <?php endif; ?>
                </nav>
                
                <?php else: ?>
                <!-- MENU NON CONNECTÉ -->
                <nav class="nav">
                    <a href="missions.php" class="nav-link">Voir les missions</a>
                </nav>
                <?php endif; ?>
                
                <!-- BOUTONS DROITE -->
                <div class="nav-actions">
                    <?php if ($currentUser): ?>
                    <a href="profile.php" class="btn btn-ghost" title="Mon Profil">
                        <?= htmlspecialchars($currentUser['first_name']) ?>
                    </a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Deconnexion</a>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-ghost">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
                
                <!-- BOUTON MENU MOBILE (apparaît sur petits écrans) -->
                <button class="mobile-menu-btn" aria-label="Menu" id="mobileMenuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>