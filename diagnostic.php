<?php
/**
 * Diagnostic de connexion — à SUPPRIMER après utilisation
 */
session_start();
require_once "includes/auth.php";

$email = 'vianson50@gmail.com';
$password = '1995-M@i-30';
$result = [];

// Test 1 : Connexion BDD
$pdo = getDB();
$result['db_connected'] = $pdo ? '✅ OUI' : '❌ NON';

if ($pdo) {
    // Test 2 : L'utilisateur existe ?
    $stmt = $pdo->prepare("SELECT id, username, email, role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $result['user_exists'] = $user ? '✅ OUI' : '❌ NON';

    if ($user) {
        $result['user_id'] = $user['id'];
        $result['username'] = $user['username'];
        $result['email'] = $user['email'];
        $result['role'] = $user['role'];
        $result['is_active'] = $user['is_active'] ? '✅ Actif' : '❌ Inactif';

        // Test 3 : Le mot de passe match ?
        $stmt2 = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt2->execute([$email]);
        $hash = $stmt2->fetchColumn();
        $result['hash_start'] = substr($hash, 0, 20) . '...';
        $result['password_match'] = password_verify($password, $hash) ? '✅ OUI' : '❌ NON';

        // Test 4 : Fonction login()
        $result['login_function'] = login($email, $password) ? '✅ OK' : '❌ ÉCHEC';
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Diagnostic</title>
<style>body{background:#0a0a0b;color:#e0e0e0;font-family:monospace;padding:2rem;font-size:14px;line-height:2}
.ok{color:#009E60}.err{color:#FF5252}.title{color:#FF8200;font-size:1.2rem;font-weight:bold;margin-bottom:1rem}
a{color:#FF8200}</style></head>
<body>
<div class="title">🔍 DIAGNOSTIC DE CONNEXION</div>
<?php foreach($result as $k => $v): ?>
<div><strong><?= $k ?> :</strong> <?= $v ?></div>
<?php endforeach; ?>
<br><a href="login.php">← Retour connexion</a>
<br><a href="update_admin.php">→ Mettre à jour l'admin</a>
<br><br><small style="color:#ff5257">⚠ Supprime ce fichier après utilisation</small>
</body></html>
