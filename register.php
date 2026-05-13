









<?php
 $pageTitle = 'Inscription';
require_once 'includes/header.php';

 $errors = [];
 $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'benevole';
    $city = trim($_POST['city'] ?? '');
    $terms = $_POST['terms'] ?? false;
    
    // Validation
    if (empty($email)) $errors['email'] = 'L\'email est requis';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email invalide';
    
    if (empty($password)) $errors['password'] = 'Le mot de passe est requis';
    elseif (strlen($password) < 8) $errors['password'] = 'Minimum 8 caracteres';
    
    if ($password !== $password_confirm) $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
    
    if (empty($first_name)) $errors['first_name'] = 'Le prenom est requis';
    if (empty($last_name)) $errors['last_name'] = 'Le nom est requis';
    
    if (!in_array($role, ['benevole', 'demandeur'])) $role = 'benevole';
    
    if (!$terms) $errors['terms'] = 'Vous devez accepter les conditions';
    
    // Check if email exists
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cet email est deja utilise';
        }
    }
    
    if (empty($errors)) {
        $data = [
            'email' => $email,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'role' => $role,
            'city' => $city
        ];
        
        if (register($data)) {
            $success = true;
            // Auto-login after registration
            login($email, $password);
            header('Location: dashboard.php?welcome=1');
            exit;
        } else {
            $errors['general'] = 'Une erreur est survenue. Veuillez reessayer.';
        }
    }
}
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Creer un compte</h1>
                <p class="auth-subtitle">Rejoignez notre communaute solidaire</p>
            </div>
            
            <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <?= htmlspecialchars($errors['general']) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="role-selector">
                    <label class="role-option <?= ($_POST['role'] ?? 'benevole') === 'benevole' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="benevole" <?= ($_POST['role'] ?? 'benevole') === 'benevole' ? 'checked' : '' ?>>
                        <div class="role-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </div>
                        <div class="role-info">
                            <div class="role-name">Benevole</div>
                            <div class="role-desc">Je souhaite aider</div>
                        </div>
                    </label>
                    
                    <label class="role-option <?= ($_POST['role'] ?? '') === 'demandeur' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="demandeur" <?= ($_POST['role'] ?? '') === 'demandeur' ? 'checked' : '' ?>>
                        <div class="role-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M16 16s-1.5-2-4-2-4 2-4 2"/>
                                <line x1="9" y1="9" x2="9.01" y2="9"/>
                                <line x1="15" y1="9" x2="15.01" y2="9"/>
                            </svg>
                        </div>
                        <div class="role-info">
                            <div class="role-name">Demandeur</div>
                            <div class="role-desc">J'ai besoin d'aide</div>
                        </div>
                    </label>
                </div>
                
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Prenom *</label>
                        <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                        <?php if (!empty($errors['first_name'])): ?>
                        <span class="form-error"><?= $errors['first_name'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                        <?php if (!empty($errors['last_name'])): ?>
                        <span class="form-error"><?= $errors['last_name'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <?php if (!empty($errors['email'])): ?>
                    <span class="form-error"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Telephone</label>
                    <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
                
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" name="password" class="form-input" required>
                        <?php if (!empty($errors['password'])): ?>
                        <span class="form-error"><?= $errors['password'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer *</label>
                        <input type="password" name="password_confirm" class="form-input" required>
                        <?php if (!empty($errors['password_confirm'])): ?>
                        <span class="form-error"><?= $errors['password_confirm'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="terms" required>
                        <span class="form-check-label">J'accepte les conditions d'utilisation et la politique de confidentialite</span>
                    </label>
                    <?php if (!empty($errors['terms'])): ?>
                    <span class="form-error"><?= $errors['terms'] ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Creer mon compte
                </button>
            </form>
            
            <p class="auth-footer">
                Deja inscrit ? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>
</main>

<script>
document.querySelectorAll('.role-option input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('selected'));
        this.closest('.role-option').classList.add('selected');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>