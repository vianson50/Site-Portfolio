<?php
/**
 * BLACK_PROTOCOL — Déconnexion
 * Détruit complètement la session et redirige
 */

// Démarrer la session si pas encore active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Réinitialiser le singleton PDO pour forcer une reconnexion
// (utile car database.php est un require_once)
$GLOBALS["pdoInstance"] = null;

// Supprimer toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session si il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"],
    );
}

// Détruire la session complètement
session_destroy();

// Démarrer une nouvelle session vide pour éviter les résidus
session_start();
session_regenerate_id(true);
session_write_close();

// Redirection vers la page de connexion
header("Location: login.php?logout=1");
exit();
