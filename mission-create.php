



<?php
 $pageTitle = 'Publier une mission';
require_once 'includes/auth.php';
requireLogin();
requireRole(['demandeur', 'admin']);

 $currentUser = getCurrentUser();
 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $remuneration = trim($_POST['remuneration'] ?? ''); // NOUVEAU : Récupération de la rémunération
    $category = $_POST['category'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $date_mission = $_POST['date_mission'] ?? '';
    $time_start = $_POST['time_start'] ?? '';
    $time_end = $_POST['time_end'] ?? '';
    $duration = (int)($_POST['duration'] ?? 60);
    $urgency = $_POST['urgency'] ?? 'moyenne';
    $max_benevoles = (int)($_POST['max_benevoles'] ?? 1);
    
    // Validation
    if (empty($title)) $errors['title'] = 'Titre requis';
    if (empty($description)) $errors['description'] = 'Description requise';
    if (empty($category)) $errors['category'] = 'Categorie requise';
    if (empty($location)) $errors['location'] = 'Adresse requise';
    if (empty($city)) $errors['city'] = 'Ville requise';
    if (empty($date_mission)) $errors['date_mission'] = 'Date requise';
    if (empty($time_start)) $errors['time_start'] = 'Heure de debut requise';
    
    if (empty($errors)) {
        $db = getDB();
        // NOUVEAU : Ajout de 'remuneration' dans la requête SQL
        $stmt = $db->prepare("
            INSERT INTO missions (user_id, title, description, remuneration, category, location, city, postal_code, 
                                  date_mission, time_start, time_end, duration_estimated, urgency, max_benevoles)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([
            $currentUser['id'], 
            $title, 
            $description, 
            $remuneration, // NOUVEAU : Ajout de la variable
            $category, 
            $location, 
            $city, 
            $postal_code,
            $date_mission, 
            $time_start, 
            $time_end, 
            $duration, 
            $urgency, 
            $max_benevoles
        ])) {
            header('Location: dashboard.php?mission_created=1');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 0.5rem;">Publier une mission</h1>
            <p class="text-muted mb-4">Decrivez votre besoin pour trouver des benevoles disponibles</p>
            
            <form method="POST" class="card" style="padding: 2rem;">
                <div class="form-group">
                    <label class="form-label">Titre de la mission *</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                           placeholder="Ex: Aide pour courses hebdomadaires" required>
                    <?php if (!empty($errors['title'])): ?>
                    <span class="form-error"><?= $errors['title'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-textarea" rows="4" 
                              placeholder="Decrivez en detail ce dont vous avez besoin..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <?php if (!empty($errors['description'])): ?>
                    <span class="form-error"><?= $errors['description'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- NOUVEAU CHAMP : Rémunération -->
                <div class="form-group">
                    <label class="form-label">Rémunération proposée (Optionnel)</label>
                    <input type="text" name="remuneration" class="form-input" 
                           value="<?= htmlspecialchars($_POST['remuneration'] ?? '') ?>" 
                           placeholder="Ex: 15€/heure, 50€ forfait, Repas offert...">
                    <small class="text-muted" style="display:block; margin-top:0.5rem;">
                        💡 Indiquer une rémunération accélère souvent la recherche de bénévole.
                    </small>
                </div>
                
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Categorie *</label>
                        <select name="category" class="form-select" required>
                            <option value="">Selectionner...</option>
                            <option value="compagnie" <?= ($_POST['category'] ?? '') === 'compagnie' ? 'selected' : '' ?>>Compagnie</option>
                            <option value="courses" <?= ($_POST['category'] ?? '') === 'courses' ? 'selected' : '' ?>>Courses</option>
                            <option value="jardinage" <?= ($_POST['category'] ?? '') === 'jardinage' ? 'selected' : '' ?>>Jardinage</option>
                            <option value="bricolage" <?= ($_POST['category'] ?? '') === 'bricolage' ? 'selected' : '' ?>>Bricolage</option>
                            <option value="demenagement" <?= ($_POST['category'] ?? '') === 'demenagement' ? 'selected' : '' ?>>Demenagement</option>
                            <option value="soutien_scolaire" <?= ($_POST['category'] ?? '') === 'soutien_scolaire' ? 'selected' : '' ?>>Soutien scolaire</option>
                            <option value="administratif" <?= ($_POST['category'] ?? '') === 'administratif' ? 'selected' : '' ?>>Aide administrative</option>
                            <option value="autre" <?= ($_POST['category'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                        <?php if (!empty($errors['category'])): ?>
                        <span class="form-error"><?= $errors['category'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Niveau d'urgence</label>
                        <select name="urgency" class="form-select">
                            <option value="faible" <?= ($_POST['urgency'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
                            <option value="moyenne" <?= ($_POST['urgency'] ?? 'moyenne') === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                            <option value="elevee" <?= ($_POST['urgency'] ?? '') === 'elevee' ? 'selected' : '' ?>>Elevee</option>
                        </select>
                    </div>
                </div>
                
                <h3 style="margin: 2rem 0 1rem;">Localisation</h3>
                
                <div class="form-group">
                    <label class="form-label">Adresse *</label>
                    <input type="text" name="location" class="form-input" 
                           value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" 
                           placeholder="Ex: 123 rue de la Paix" required>
                    <?php if (!empty($errors['location'])): ?>
                    <span class="form-error"><?= $errors['location'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Ville *</label>
                        <input type="text" name="city" class="form-input" 
                               value="<?= htmlspecialchars($_POST['city'] ?? $currentUser['city'] ?? '') ?>" required>
                        <?php if (!empty($errors['city'])): ?>
                        <span class="form-error"><?= $errors['city'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="postal_code" class="form-input" 
                               value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
                    </div>
                </div>
                
                <h3 style="margin: 2rem 0 1rem;">Date et horaire</h3>
                
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date_mission" class="form-input" 
                               value="<?= htmlspecialchars($_POST['date_mission'] ?? '') ?>" required>
                        <?php if (!empty($errors['date_mission'])): ?>
                        <span class="form-error"><?= $errors['date_mission'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heure debut *</label>
                        <input type="time" name="time_start" class="form-input" 
                               value="<?= htmlspecialchars($_POST['time_start'] ?? '') ?>" required>
                        <?php if (!empty($errors['time_start'])): ?>
                        <span class="form-error"><?= $errors['time_start'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heure fin</label>
                        <input type="time" name="time_end" class="form-input" 
                               value="<?= htmlspecialchars($_POST['time_end'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Duree estimee (minutes)</label>
                        <input type="number" name="duration" class="form-input" 
                               value="<?= htmlspecialchars($_POST['duration'] ?? 60) ?>" min="15" step="15">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre de benevoles</label>
                        <input type="number" name="max_benevoles" class="form-input" 
                               value="<?= htmlspecialchars($_POST['max_benevoles'] ?? 1) ?>" min="1" max="10">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Publier la mission</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>