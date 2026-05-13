


<?php
 $pageTitle = 'Plateforme solidaire en Tunisie';
require_once 'includes/auth.php';
require_once 'config/database.php';

 $db = getDB();

// Stats
 $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'benevole'");
 $benevolesCount = $stmt->fetchColumn();

 $stmt = $db->query("SELECT COUNT(*) FROM missions WHERE status = 'ouverte'");
 $missionsCount = $stmt->fetchColumn();

 $stmt = $db->query("SELECT COUNT(*) FROM candidatures WHERE status = 'acceptee'");
 $missionsCompletees = $stmt->fetchColumn();

// Missions récentes
 $stmt = $db->query("
    SELECT m.*, u.first_name, u.last_name, u.city as user_city
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
    <!-- HERO SECTION -->
    <section class="hero" style="padding: 5rem 0; position: relative; overflow: hidden;">
        <!-- Decorative background elements -->
        <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(232, 93, 4, 0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        
        <div class="container">
            <div class="hero-badge animate-slide-up stagger-1" style="background: rgba(232, 93, 4, 0.1); border: 1px solid rgba(232, 93, 4, 0.3);">
                <span style="margin-right: 8px;">🇹🇳</span> Plateforme Solidaire Tunisienne
            </div>
            
            <h1 class="hero-title animate-slide-up stagger-2" style="font-size: clamp(2.5rem, 6vw, 4.5rem);">
                Entraide & Solidarité<br>
                <span style="background: linear-gradient(135deg, var(--accent), var(--accent-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">en Tunisie</span>
            </h1>
            
            <p class="hero-subtitle animate-slide-up stagger-3" style="max-width: 700px; margin: 0 auto 2.5rem;">
                Connectez-vous avec des étudiants bénévoles et des citoyens ayant besoin d'aide à Tunis, Sfax, Sousse et partout en Tunisie.
            </p>

            <!-- BARRE DE RECHERCHE RAPIDE -->
            <div class="animate-slide-up stagger-4" style="max-width: 600px; margin: 0 auto 2rem; background: var(--bg-card); padding: 0.5rem; border-radius: 50px; border: 1px solid var(--border); display: flex;">
                <form action="missions.php" method="GET" style="display: flex; width: 100%;">
                    <input type="text" name="search" class="form-input" placeholder="Rechercher une mission (ex: Aide cours Sfax...)" style="border: none; background: transparent; padding: 0.75rem 1.5rem; flex: 1;">
                    <button type="submit" class="btn btn-primary" style="border-radius: 50px; padding: 0 2rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </form>
            </div>

            <!-- BOUTONS D'ACTION RAPIDES -->
            <div class="hero-actions animate-slide-up stagger-5" style="justify-content: center; gap: 1.5rem; margin-bottom: 3rem;">
                <a href="missions.php" class="btn btn-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    Je veux aider
                </a>
                <a href="mission-create.php" class="btn btn-secondary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    J'ai besoin d'aide
                </a>
                <a href="community.php" class="btn btn-ghost btn-lg" style="border: 1px solid var(--border);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Communauté
                </a>
            </div>
            
            <div class="hero-stats animate-slide-up stagger-6">
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= number_format($benevolesCount) ?></div>
                    <div class="hero-stat-label">Bénévoles actifs</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= number_format($missionsCount) ?></div>
                    <div class="hero-stat-label">Missions ouvertes</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= number_format($missionsCompletees) ?></div>
                    <div class="hero-stat-label">Missions réussies</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- MISSIONS RECENTES -->
    <section class="section" style="padding: 4rem 0; background: var(--bg-secondary);">
        <div class="container">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h2 style="margin-bottom: 0.5rem;">Missions récentes en Tunisie</h2>
                    <p class="text-muted">Opportunités d'entraide à Tunis, Sfax, Sousse...</p>
                </div>
                <a href="missions.php" class="btn btn-secondary">Voir toutes les missions</a>
            </div>
            
            <?php if (empty($recentMissions)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3>Aucune mission disponible pour le moment</h3>
                <p class="text-muted">Soyez le premier à proposer de l'aide !</p>
                <a href="mission-create.php" class="btn btn-primary mt-2">Publier une mission</a>
            </div>
            <?php else: ?>
            <div class="missions-grid">
                <?php foreach ($recentMissions as $index => $mission): ?>
                <a href="mission-details.php?id=<?= $mission['id'] ?>" class="card mission-card animate-slide-up stagger-<?= $index + 1 ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <span class="mission-category"><?= htmlspecialchars($mission['category']) ?></span>
                        <?php if (!empty($mission['remuneration'])): ?>
                        <span class="badge" style="background: rgba(45, 198, 83, 0.15); color: var(--success); font-size: 0.8rem;">
                            💰 <?= htmlspecialchars($mission['remuneration']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="mission-title"><?= htmlspecialchars($mission['title']) ?></h3>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;"><?= substr(htmlspecialchars($mission['description']), 0, 100) ?>...</p>
                    
                    <div class="mission-meta">
                        <div class="mission-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= htmlspecialchars($mission['city']) ?>
                        </div>
                        <div class="mission-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <?= date('d/m/Y', strtotime($mission['date_mission'])) ?>
                        </div>
                    </div>
                    
                    <div class="card-footer" style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem;">
                        <span class="urgency-badge urgency-<?= $mission['urgency'] ?>">
                            <?= ucfirst($mission['urgency']) ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION UNIVERSITÉS PARTENAIRES (Tunisie) -->
    <section class="section" style="padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 1rem;">Nos étudiants partenaires</h2>
            <p class="text-muted" style="text-align: center; margin-bottom: 3rem;">Rejoignez la communauté des grandes écoles tunisiennes</p>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; align-items: center; opacity: 0.8;">
                <div class="card" style="padding: 1.5rem 2.5rem; border: none; background: var(--bg-elevated);">ESPRIT</div>
                <div class="card" style="padding: 1.5rem 2.5rem; border: none; background: var(--bg-elevated);">IHEC Carthage</div>
                <div class="card" style="padding: 1.5rem 2.5rem; border: none; background: var(--bg-elevated);">Sup'Com</div>
                <div class="card" style="padding: 1.5rem 2.5rem; border: none; background: var(--bg-elevated);">ENIT</div>
                <div class="card" style="padding: 1.5rem 2.5rem; border: none; background: var(--bg-elevated);">FST</div>
            </div>
        </div>
    </section>

    <!-- SECTION TÉMOIGNAGES -->
    <section class="section" style="padding: 4rem 0; background: var(--bg-card);">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem;">Ce qu'ils en disent</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <div class="card" style="background: var(--bg-primary);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div class="user-avatar" style="width: 50px; height: 50px; background: var(--accent);">A</div>
                        <div>
                            <strong>Ahmed (Bénévole)</strong><br>
                            <small class="text-muted">Étudiant à ESPRIT, Tunis</small>
                        </div>
                    </div>
                    <p class="text-muted">"Grâce à cette plateforme, j'ai pu aider une famille à La Marsa pour leurs courses. Une expérience humaine incroyable et le système de messagerie est top !"</p>
                </div>

                <div class="card" style="background: var(--bg-primary);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div class="user-avatar" style="width: 50px; height: 50px; background: var(--success);">S</div>
                        <div>
                            <strong>Sami (Demandeur)</strong><br>
                            <small class="text-muted">Retraité, Sfax</small>
                        </div>
                    </div>
                    <p class="text-muted">"J'avais besoin d'aide pour mon jardin. En 2 jours, deux étudiants de l'Université de Sfax se sont proposés. Service rapide et efficace."</p>
                </div>

            </div>
        </div>
    </section>

    <!-- CALL TO ACTION FINAL -->
    <section class="section" style="padding: 6rem 0; text-align: center;">
        <div class="container">
            <h2 style="margin-bottom: 1rem;">Prêt à faire la différence ?</h2>
            <p class="text-muted" style="margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">Rejoignez des milliers de Tunisiens solidaires. L'inscription est gratuite et ne prend qu'une minute.</p>
            <a href="register.php" class="btn btn-primary btn-lg" style="padding: 1.25rem 3rem; font-size: 1.1rem;">
                Créer mon compte gratuitement
            </a>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>