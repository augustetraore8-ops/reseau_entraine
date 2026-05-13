

<?php
 $pageTitle = 'Mon Profil';
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

 $currentUser = getCurrentUser();
 $db = getDB();
 $success = false;
 $errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $school = trim($_POST['school'] ?? ''); // Récupération de l'école
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation des champs texte
    if (empty($first_name)) $errors[] = "Le prénom est requis.";
    if (empty($last_name)) $errors[] = "Le nom est requis.";

    // 1. Mise à jour des informations de base
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, city = ?, phone = ?, school = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $city, $phone, $school, $currentUser['id']]);
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour du profil.";
        }
    }

    // 2. Changement de mot de passe (uniquement si rempli)
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Le mot de passe doit faire au moins 6 caractères.";
        } elseif ($password !== $password_confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $currentUser['id']]);
        }
    }

    // Si tout est OK
    if (empty($errors)) {
        $success = true;
        // Rafraîchir les données utilisateur en session
        $currentUser = getCurrentUser(); 
    }
}

require_once 'includes/header.php';
?>

<main>
    <div class="container" style="max-width: 800px; margin: 0 auto;">
        
        <!-- Header du profil -->
        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <div class="user-avatar" style="width: 100px; height: 100px; font-size: 2.5rem;">
                <?= strtoupper(substr($currentUser['first_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="margin-bottom: 0.25rem;"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></h1>
                <p class="text-muted" style="margin: 0;">
                    Membre depuis <?= date('F Y', strtotime($currentUser['created_at'])) ?> • 
                    <span class="badge status-ouverte" style="font-size: 0.8rem;"><?= ucfirst($currentUser['role']) ?></span>
                </p>
                <?php if ($currentUser['school']): ?>
                <p style="margin-top: 0.5rem; color: var(--accent);">🎓 <?= htmlspecialchars($currentUser['school']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications -->
        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: 2rem;">
            <strong>Succès !</strong> Votre profil a été mis à jour.
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: 2rem;">
            <?php foreach ($errors as $e) echo "<p style='margin:0;'>• $e</p>"; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Informations personnelles</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mb-3">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($currentUser['first_name']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($currentUser['last_name']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email (non modifiable)</label>
                <input type="email" class="form-input" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label class="form-label">École / Université</label>
                <input type="text" name="school" class="form-input" value="<?= htmlspecialchars($currentUser['school'] ?? '') ?>" placeholder="Ex: ESPRIT, IHEC Carthage, FST...">
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="Ex: +216 XX XXX XXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($currentUser['city'] ?? '') ?>" placeholder="Ex: Tunis, Sfax...">
                </div>
            </div>

            <hr style="border: 1px solid var(--border); margin: 2.5rem 0;">

            <h3 style="margin-bottom: 0.5rem;">Changer de mot de passe</h3>
            <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1.5rem;">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe.</p>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" class="form-input" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-ghost">← Retour</a>
                <button type="submit" class="btn btn-primary btn-lg">Enregistrer les modifications</button>
            </div>
        </form>

        <!-- Zone Dangereuse (Optionnel) -->
        <div class="card" style="border: 1px solid var(--danger);">
            <h3 style="color: var(--danger);">Zone de danger</h3>
            <p class="text-muted" style="font-size: 0.9rem;">Vous pouvez demander la suppression définitive de votre compte en contactant l'administrateur.</p>
            <a href="mailto:admin@reseau-entraine.fr" class="btn btn-danger btn-sm">Contacter l'administration</a>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>