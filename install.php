<?php
/**
 * BLACK_PROTOCOL — Installateur de base de données (AUTONOME)
 * Ne dépend PAS de config/database.php
 */
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_CHARSET = "utf8mb4";
$DB_NAME = "black_protocol";
$message = "";
$messageType = "";
$alreadyInstalled = false;

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $stmt = $pdo->query("SELECT COUNT(*) FROM users LIMIT 1");
    if ($stmt !== false) {
        $alreadyInstalled = true;
    }
} catch (PDOException $e) {
    $alreadyInstalled = false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["install"])) {
    try {
        $dsn = "mysql:host={$DB_HOST};charset={$DB_CHARSET}";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $sqlFile = __DIR__ . "/database.sql";
        if (!file_exists($sqlFile)) {
            throw new Exception("Fichier database.sql introuvable.");
        }
        $sql = file_get_contents($sqlFile);
        $statements = array_filter(
            array_map("trim", explode(";", $sql)),
            function ($s) {
                $lines = array_filter(
                    array_map("trim", explode("\n", $s)),
                    function ($l) {
                        return !empty($l) && !str_starts_with($l, "--");
                    },
                );
                return !empty(implode("", $lines));
            },
        );
        foreach ($statements as $st) {
            $c = trim($st);
            if (!empty($c)) {
                $pdo->exec($c);
            }
        }
        // Mettre à jour le mot de passe admin avec le vrai hash bcrypt
        $adminHash = password_hash("1995-M@i-30", PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "UPDATE black_protocol.users SET password = ? WHERE username = 'admin'",
        );
        $stmt->execute([$adminHash]);
        $message = "Base de données installée avec succès !";
        $messageType = "success";
        $alreadyInstalled = true;
    } catch (PDOException $e) {
        $message = "Erreur : " . htmlspecialchars($e->getMessage());
        $messageType = "error";
    } catch (Exception $e) {
        $message = "Erreur : " . htmlspecialchars($e->getMessage());
        $messageType = "error";
    }
}
$year = date("Y");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACK_PROTOCOL | INSTALLER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
        body{min-height:100vh;background:#0A0A0B;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;font-family:'Inter',sans-serif;color:#E0E0E0}
        .card{background:#111113;border:1px solid #222;width:100%;max-width:540px;overflow:hidden}
        .card-bar{display:flex;align-items:center;gap:12px;padding:14px 18px;background:#0A0A0B;border-bottom:1px solid #222}
        .dots{display:flex;gap:6px}.dot{width:10px;height:10px;border-radius:50%}
        .dot-r{background:#FF5F57}.dot-y{background:#FEBC2E}.dot-g{background:#28C840}
        .bar-title{font-family:'Space Grotesk',monospace;font-size:11px;color:#555;letter-spacing:2px}
        .card-body{padding:2rem}
        .title{font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;color:#FF8200;letter-spacing:2px;margin-bottom:0.5rem}
        .subtitle{font-size:0.85rem;color:#666;letter-spacing:1px;margin-bottom:1.5rem}
        .msg{padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:10px;font-size:0.9rem;line-height:1.5}
        .msg-s{background:rgba(0,158,96,0.1);border:1px solid rgba(0,158,96,0.3);color:#009E60}
        .msg-e{background:rgba(255,82,82,0.1);border:1px solid rgba(255,82,82,0.3);color:#FF5252}
        .msg-i{background:rgba(255,130,0,0.08);border:1px solid rgba(255,130,0,0.2);color:#FF8200}
        .msg .material-symbols-outlined{font-size:20px;flex-shrink:0}
        .btn{width:100%;padding:14px 24px;background:#FF8200;color:#0A0A0B;border:none;font-family:'Space Grotesk',sans-serif;font-size:0.95rem;font-weight:600;letter-spacing:2px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px}
        .btn:hover{background:#FF9A33}
        .btn .material-symbols-outlined{font-size:20px}
        .badge{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(0,158,96,0.1);border:1px solid rgba(0,158,96,0.3);color:#009E60;font-family:'Space Grotesk',sans-serif;font-size:0.8rem;font-weight:600;letter-spacing:1.5px;margin-bottom:1rem}
        .badge .material-symbols-outlined{font-size:18px}
        .links{display:flex;gap:12px;margin-top:1.5rem}
        .link{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;background:#1A1A1D;border:1px solid #333;color:#E0E0E0;text-decoration:none;font-family:'Space Grotesk',sans-serif;font-size:0.85rem;font-weight:500;letter-spacing:1px;transition:border-color 0.2s,color 0.2s}
        .link:hover{border-color:#FF8200;color:#FF8200}
        .link .material-symbols-outlined{font-size:18px}
        .foot{margin-top:1.5rem;font-size:0.75rem;color:#444;letter-spacing:1px;display:flex;align-items:center;gap:8px}
        .foot .material-symbols-outlined{font-size:14px}
    </style>
</head>
<body>
    <div class="card">
        <div class="card-bar">
            <div class="dots"><span class="dot dot-r"></span><span class="dot dot-y"></span><span class="dot dot-g"></span></div>
            <span class="bar-title">DB_INSTALL_TERMINAL</span>
        </div>
        <div class="card-body">
            <h1 class="title">INSTALLER_LA_BASE</h1>
            <p class="subtitle">// CONFIGURATION DE BLACK_PROTOCOL</p>
            <?php if ($alreadyInstalled && empty($message)): ?>
                <div class="badge"><span class="material-symbols-outlined">check_circle</span> BASE INSTALLEE</div>
                <div class="msg msg-s"><span class="material-symbols-outlined">database</span><span>La base <strong>black_protocol</strong> est opérationnelle.</span></div>
                <div class="links">
                    <a href="login.php" class="link"><span class="material-symbols-outlined">login</span> SE_CONNECTER</a>
                    <a href="index.php" class="link"><span class="material-symbols-outlined">home</span> ACCUEIL</a>
                </div>
            <?php elseif (!empty($message)): ?>
                <div class="msg msg-<?= $messageType ?>"><span class="material-symbols-outlined"><?= $messageType ===
"success"
    ? "check_circle"
    : "error" ?></span><span><?= $message ?></span></div>
                <?php if ($messageType === "success"): ?>
                    <div class="links">
                        <a href="login.php" class="link"><span class="material-symbols-outlined">login</span> SE_CONNECTER</a>
                        <a href="index.php" class="link"><span class="material-symbols-outlined">home</span> ACCUEIL</a>
                    </div>
                <?php else: ?>
                    <form method="POST"><button type="submit" name="install" class="btn"><span class="material-symbols-outlined">sync</span> RÉESSAYER</button></form>
                <?php endif; ?>
            <?php else: ?>
                <div class="msg msg-i">
                    <span class="material-symbols-outlined">info</span>
                    <span>Crée la base <strong>black_protocol</strong>, les tables + admin par défaut.<br><br><strong>Admin :</strong> vianson50@gmail.com / votre mot de passe<br><strong>Demo :</strong> demo@blackprotocol.com / password</span>
                </div>
                <form method="POST"><button type="submit" name="install" class="btn"><span class="material-symbols-outlined">rocket_launch</span> INSTALLER LA BASE</button></form>
            <?php endif; ?>
        </div>
    </div>
    <div class="foot"><span class="material-symbols-outlined">terminal</span> BLACK_PROTOCOL // DB_INSTALL v1.0 // <?= $year ?></div>
</body>
</html>
