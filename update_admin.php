<?php
/**
 * BLACK_PROTOCOL — Mise à jour des identifiants admin
 * Script ponctuel : change l'email et le mot de passe de l'admin
 * SUPPRIME CE FICHIER après utilisation !
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'black_protocol';
$DB_CHARSET = 'utf8mb4';

$newEmail = 'vianson50@gmail.com';
$newPassword = '1995-M@i-30';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET email = :email, password = :password WHERE username = 'admin'");
        $stmt->execute([':email' => $newEmail, ':password' => $hash]);

        $message = "Admin mis à jour avec succès ! Email : {$newEmail}";
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Erreur : ' . htmlspecialchars($e->getMessage());
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACK_PROTOCOL | Mise à jour Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;background:#0A0A0B;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;font-family:'Inter',sans-serif;color:#E0E0E0}
        .card{background:#111113;border:1px solid #222;max-width:480px;width:100%;overflow:hidden}
        .bar{display:flex;align-items:center;gap:12px;padding:14px 18px;background:#0A0A0B;border-bottom:1px solid #222}
        .dots{display:flex;gap:6px}.dot{width:10px;height:10px;border-radius:50%}.r{background:#FF5F57}.y{background:#FEBC2E}.g{background:#28C840}
        .bar-t{font-family:'Space Grotesk';font-size:11px;color:#555;letter-spacing:2px}
        .body{padding:2rem}
        .title{font-family:'Space Grotesk';font-size:1.4rem;font-weight:700;color:#FF8200;letter-spacing:2px;margin-bottom:1.5rem}
        .msg{padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;font-size:0.9rem;line-height:1.5}
        .msg-s{background:rgba(0,158,96,0.1);border:1px solid rgba(0,158,96,0.3);color:#009E60}
        .msg-e{background:rgba(255,82,82,0.1);border:1px solid rgba(255,82,82,0.3);color:#FF5252}
        .info{background:rgba(255,130,0,0.08);border:1px solid rgba(255,130,0,0.2);padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:0.85rem;color:#FF8200;line-height:1.6}
        .info strong{color:#ffb785}
        .btn{width:100%;padding:14px 24px;background:#FF8200;color:#0A0A0B;border:none;font-family:'Space Grotesk';font-size:0.95rem;font-weight:600;letter-spacing:2px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px}
        .btn:hover{background:#FF9A33}
        .links{display:flex;gap:12px;margin-top:1.5rem}
        .link{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:#1A1A1D;border:1px solid #333;color:#E0E0E0;text-decoration:none;font-family:'Space Grotesk';font-size:0.85rem;letter-spacing:1px;transition:border-color 0.2s,color 0.2s}
        .link:hover{border-color:#FF8200;color:#FF8200}
        .warn{margin-top:1.5rem;font-size:0.75rem;color:#FF5252;text-align:center;letter-spacing:1px}
    </style>
</head>
<body>
    <div class="card">
        <div class="bar">
            <div class="dots"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
            <span class="bar-t">ADMIN_UPDATE</span>
        </div>
        <div class="body">
            <h1 class="title">METTRE À JOUR L'ADMIN</h1>

            <?php if (!empty($message)): ?>
                <div class="msg msg-<?= $messageType ?>"><?= $message ?></div>
                <?php if ($messageType === 'success'): ?>
                    <div class="links">
                        <a href="login.php" class="link">SE CONNECTER</a>
                        <a href="index.php" class="link">ACCUEIL</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="info">
                    <strong>Email :</strong> <?= htmlspecialchars($newEmail) ?><br>
                    <strong>Mot de passe :</strong> sera hashé en bcrypt automatiquement
                </div>
                <form method="POST">
                    <button type="submit" name="update" class="btn">METTRE À JOUR L'ADMIN</button>
                </form>
                <p class="warn">⚠ Supprime ce fichier après utilisation</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
