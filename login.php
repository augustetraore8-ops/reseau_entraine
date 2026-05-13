






<?php
 $pageTitle = 'Connexion';
require_once 'includes/header.php';

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) $errors['email'] = 'Email requis';
    if (empty($password)) $errors['password'] = 'Mot de passe requis';
    
    if (empty($errors)) {
        if (login($email, $password)) {
            $redirect = $_SESSION['user_role'] === 'admin' ? 'admin/index.php' : 'dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $errors['general'] = 'Email ou mot de passe incorrect';
        }
    }
}
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Connexion</h1>
                <p class="auth-subtitle">Accedez a votre espace personnel</p>
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
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    <?php if (!empty($errors['email'])): ?>
                    <span class="form-error"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required>
                    <?php if (!empty($errors['password'])): ?>
                    <span class="form-error"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Se connecter
                </button>
            </form>
            
            <p class="auth-footer">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
            </p>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>