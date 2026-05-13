

<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

 $db = getDB();
 $id = (int)($_GET['id'] ?? 0);

// Récupérer les infos de la mission
 $stmt = $db->prepare("
    SELECT m.*, u.first_name, u.last_name, u.email, u.id as owner_id, u.city as user_city
    FROM missions m
    JOIN users u ON m.user_id = u.id
    WHERE m.id = ?
");
 $stmt->execute([$id]);
 $mission = $stmt->fetch();

if (!$mission) {
    header('Location: missions.php');
    exit;
}

 $currentUser = getCurrentUser();
 $success = false;
 $error = '';
 $isOwner = ($currentUser && $currentUser['id'] === $mission['user_id']);

// Traitement des actions (Candidature, Accepter, Refuser)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Postuler
    if (isset($_POST['apply']) && $currentUser && $currentUser['role'] === 'benevole') {
        $check = $db->prepare("SELECT id FROM candidatures WHERE mission_id = ? AND benevole_id = ?");
        $check->execute([$id, $currentUser['id']]);
        if (!$check->fetch()) {
            $stmt = $db->prepare("INSERT INTO candidatures (mission_id, benevole_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$id, $currentUser['id'], $_POST['message'] ?? '']);
            $success = true;
        } else {
            $error = "Vous avez déjà postulé.";
        }
    }
    
    // 2. Accepter une candidature (Demandeur)
    if (isset($_POST['accept_candidature']) && $isOwner) {
        $cand_id = (int)$_POST['candidature_id'];
        
        // Récupérer l'ID du bénévole pour la notification
        $stmtCand = $db->prepare("SELECT benevole_id FROM candidatures WHERE id = ?");
        $stmtCand->execute([$cand_id]);
        $candData = $stmtCand->fetch();

        if ($candData) {
            // Mettre à jour le statut
            $db->prepare("UPDATE candidatures SET status = 'acceptee' WHERE id = ?")->execute([$cand_id]);
            
            // Envoyer une notification au bénévole
            $notifMsg = "Félicitations ! Votre candidature pour '" . $mission['title'] . "' a été acceptée.";
            $db->prepare("INSERT INTO notifications (user_id, type, title, content, link) VALUES (?, 'candidature', 'Candidature acceptée', ?, 'my-applications.php')")
               ->execute([$candData['benevole_id'], $notifMsg]);

            // Vérifier si la mission est complète
            $stmt = $db->prepare("SELECT COUNT(*) FROM candidatures WHERE mission_id = ? AND status = 'acceptee'");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() >= $mission['max_benevoles']) {
                $db->prepare("UPDATE missions SET status = 'pourvue' WHERE id = ?")->execute([$id]);
                $mission['status'] = 'pourvue'; // Màj locale
            }
            $success = true;
        }
    }
    
    // 3. Refuser une candidature (Demandeur)
    if (isset($_POST['reject_candidature']) && $isOwner) {
        $cand_id = (int)$_POST['candidature_id'];

        // Récupérer l'ID du bénévole pour la notification
        $stmtCand = $db->prepare("SELECT benevole_id FROM candidatures WHERE id = ?");
        $stmtCand->execute([$cand_id]);
        $candData = $stmtCand->fetch();

        if ($candData) {
            // Mettre à jour le statut
            $db->prepare("UPDATE candidatures SET status = 'refusee' WHERE id = ?")->execute([$cand_id]);
            
            // Envoyer une notification au bénévole
            $notifMsg = "Votre candidature pour '" . $mission['title'] . "' a été refusée.";
            $db->prepare("INSERT INTO notifications (user_id, type, title, content, link) VALUES (?, 'candidature', 'Candidature refusée', ?, 'my-applications.php')")
               ->execute([$candData['benevole_id'], $notifMsg]);

            $success = true;
        }
    }
}

// Récupérer les candidatures (pour le propriétaire)
 $candidatures = [];
if ($isOwner) {
    $stmt = $db->prepare("
        SELECT c.*, u.first_name, u.last_name, u.email 
        FROM candidatures c 
        JOIN users u ON c.benevole_id = u.id 
        WHERE c.mission_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$id]);
    $candidatures = $stmt->fetchAll();
}

// Vérifier si l'utilisateur a déjà postulé
 $hasApplied = false;
if ($currentUser && $currentUser['role'] === 'benevole') {
    $stmt = $db->prepare("SELECT status FROM candidatures WHERE mission_id = ? AND benevole_id = ?");
    $stmt->execute([$id, $currentUser['id']]);
    $appl = $stmt->fetch();
    if ($appl) {
        $hasApplied = true;
        $applicationStatus = $appl['status'];
    }
}

 $pageTitle = $mission['title'];
require_once 'includes/header.php';
?>

<main>
    <div class="container" style="max-width: 900px;">
        <nav style="margin: 1.5rem 0;">
            <a href="missions.php" class="text-muted">&larr; Retour aux missions</a>
        </nav>

        <?php if ($success): ?>
        <div class="alert alert-success" id="success-msg">Action enregistrée avec succès !</div>
        <?php elseif ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span class="mission-category"><?= htmlspecialchars($mission['category']) ?></span>
                <span class="badge urgency-<?= $mission['urgency'] ?>"><?= ucfirst($mission['urgency']) ?></span>
            </div>

            <h1 style="margin-bottom: 1rem;"><?= htmlspecialchars($mission['title']) ?></h1>
            <p style="white-space: pre-wrap; margin-bottom: 2rem; font-size: 1.05rem;"><?= htmlspecialchars($mission['description']) ?></p>

            <!-- AFFICHAGE RÉMUNÉRATION -->
            <?php if (!empty($mission['remuneration'])): ?>
            <div style="background: rgba(45, 198, 83, 0.1); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; border: 1px solid rgba(45, 198, 83, 0.2); text-align: center;">
                <div style="font-weight: 700; font-size: 1.4rem; color: var(--success);">
                    💰 <?= htmlspecialchars($mission['remuneration']) ?>
                </div>
                <div style="font-size: 0.9rem; color: var(--fg-muted);">Rémunération proposée</div>
            </div>
            <?php endif; ?>

            <div class="mission-meta" style="background: var(--bg-elevated); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
                <div>
                    <strong>📍 Lieu</strong><br>
                    <?= htmlspecialchars($mission['city']) ?><br>
                    <small class="text-muted"><?= htmlspecialchars($mission['location']) ?></small>
                </div>
                <div>
                    <strong>📅 Date</strong><br>
                    <?= date('d/m/Y', strtotime($mission['date_mission'])) ?>
                </div>
                <div>
                    <strong>⏰ Horaire</strong><br>
                    <?= $mission['time_start'] ?>
                </div>
            </div>

            <!-- Section Demandeur -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-top: 1px solid var(--border);">
                <div class="user-avatar" style="width: 50px; height: 50px;">
                    <?= strtoupper(substr($mission['first_name'], 0, 1)) ?>
                </div>
                <div style="flex: 1;">
                    <strong><?= htmlspecialchars($mission['first_name'] . ' ' . $mission['last_name']) ?></strong><br>
                    <small class="text-muted">Demandeur</small>
                </div>
                <?php if ($currentUser && !$isOwner): ?>
                <a href="messages.php?to=<?= $mission['owner_id'] ?>&mission=<?= $id ?>" class="btn btn-secondary btn-sm">
                    💬 Contacter
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION GESTION CANDIDATURES (Visible par le propriétaire) -->
        <?php if ($isOwner): ?>
        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Gestion des candidatures</h2>
            <?php if (empty($candidatures)): ?>
            <p class="text-muted">Aucun bénévole n'a encore postulé.</p>
            <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($candidatures as $c): ?>
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-elevated); border-radius: 8px;">
                    <div class="user-avatar"><?= strtoupper(substr($c['first_name'], 0, 1)) ?></div>
                    <div style="flex:1;">
                        <strong><?= htmlspecialchars($c['first_name']) ?></strong>
                        <p style="font-size: 0.9rem; color: var(--fg-secondary); margin: 0;">
                            <?= htmlspecialchars($c['message']) ?>
                        </p>
                    </div>
                    <?php if ($c['status'] === 'en_attente'): ?>
                    <form method="POST" style="display:flex; gap:0.5rem;">
                        <input type="hidden" name="candidature_id" value="<?= $c['id'] ?>">
                        <button name="accept_candidature" class="btn btn-success btn-sm">Accepter</button>
                        <button name="reject_candidature" class="btn btn-danger btn-sm">Refuser</button>
                    </form>
                    <?php else: ?>
                    <span class="badge status-<?= $c['status'] === 'acceptee' ? 'ouverte' : 'annulee' ?>">
                        <?= ucfirst($c['status']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- SECTION POSTULER (Visible par les bénévoles) -->
        <?php if ($currentUser && $currentUser['role'] === 'benevole' && !$isOwner): ?>
        <div class="card" style="margin-top: 2rem;">
            <h3>Postuler pour cette mission</h3>
            <?php if ($mission['status'] !== 'ouverte'): ?>
            <p class="text-muted">Cette mission n'est plus ouverte aux candidatures.</p>
            <?php elseif ($hasApplied): ?>
            <p class="text-muted">Vous avez déjà postulé (Statut: <strong><?= $applicationStatus ?></strong>).</p>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Message (optionnel)</label>
                    <textarea name="message" class="form-textarea" rows="3" placeholder="Présentez-vous brièvement..."></textarea>
                </div>
                <button type="submit" name="apply" class="btn btn-primary btn-lg">Envoyer ma candidature</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>