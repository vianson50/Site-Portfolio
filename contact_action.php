<?php
/**
 * BLACK_PROTOCOL — Contact Form AJAX Endpoint
 * Processes contact form submissions with validation, rate limiting,
 * spam protection, and database storage.
 */
require_once __DIR__ . "/includes/auth.php";

header("Content-Type: application/json; charset=utf-8");

// Only accept POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée.",
    ]);
    exit();
}

// ── Rate Limiting via Session ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$maxPerHour = 5;
$now = time();
$window = 3600; // 1 hour

if (!isset($_SESSION["contact_attempts"])) {
    $_SESSION["contact_attempts"] = [];
}

// Clean old attempts
$_SESSION["contact_attempts"] = array_filter(
    $_SESSION["contact_attempts"],
    fn($t) => $now - $t < $window,
);

if (count($_SESSION["contact_attempts"]) >= $maxPerHour) {
    http_response_code(429);
    echo json_encode([
        "success" => false,
        "message" => "Trop de messages envoyés. Réessayez dans une heure.",
    ]);
    exit();
}

// ── Honeypot Check ──
if (!empty($_POST["website"])) {
    // Bot filled the honeypot — silently accept but don't store
    echo json_encode([
        "success" => true,
        "message" => "Message envoyé avec succès !",
    ]);
    exit();
}

// ── Retrieve & Sanitize Fields ──
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$message = trim($_POST["message"] ?? "");

// ── Validation ──
$errors = [];

if (empty($name) || strlen($name) > 100) {
    $errors[] = "Le nom est requis (max 100 caractères).";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Adresse email invalide.";
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = "Le message doit contenir au moins 10 caractères.";
}

if (strlen($message) > 5000) {
    $errors[] = "Le message ne doit pas dépasser 5000 caractères.";
}

// Basic spam pattern detection
$spamPatterns = [
    "/\[url=/i",
    "/\[link=/i",
    "/<a\s+href/i",
    "/(viagra|casino|lottery|prize|winner)/i",
    "/(http[s]?:\/\/.*){3,}/i",
];
foreach ($spamPatterns as $pattern) {
    if (preg_match($pattern, $message)) {
        echo json_encode([
            "success" => false,
            "message" => "Le message a été détecté comme spam.",
        ]);
        exit();
    }
}

if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => implode(" ", $errors),
    ]);
    exit();
}

// ── Sanitize for DB ──
$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");

// ── Insert into Database ──
try {
    $pdo = getDB();

    if (!$pdo) {
        echo json_encode([
            "success" => false,
            "message" => "Erreur de connexion au serveur. Réessayez plus tard.",
        ]);
        exit();
    }

    // Check if messages table exists, create if not
    $stmt = $pdo->query("SHOW TABLES LIKE 'messages'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (name, email, message)
        VALUES (:name, :email, :message)
    ");
    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":message" => $message,
    ]);

    // Record this attempt for rate limiting
    $_SESSION["contact_attempts"][] = $now;

    echo json_encode([
        "success" => true,
        "message" => "Transmission reçue avec succès ! Réponse sous 24h.",
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur. Veuillez réessayer plus tard.",
    ]);
}
