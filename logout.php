



<?php
// 1. Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Détruire toutes les variables de session
 $_SESSION = array();

// 3. Détruire le cookie de session (sécurité supplémentaire)
// Cela force la session à expirer côté navigateur aussi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalement, détruire la session sur le serveur
session_destroy();

// 5. Rediriger vers la page d'accueil
header("Location: index.php");
exit;
?>