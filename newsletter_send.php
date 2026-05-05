<?php
/**
 * BLACK_PROTOCOL — Newsletter Send Action
 * Envoie un email à tous les abonnés actifs via Gmail SMTP
 * Accessible uniquement par les admins
 */
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/newsletter.php";
require_once __DIR__ . "/includes/SMTPMailer.php";

// Sécurité : admin uniquement
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Accès non autorisé."]);
    exit;
}

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// ── Récupérer les données ──
$subject = trim($_POST["subject"] ?? "");
$content = trim($_POST["content"] ?? "");
$testEmail = trim($_POST["test_email"] ?? "");
$isTest = isset($_POST["test_only"]);

// ── Validation ──
if (empty($subject)) {
    echo json_encode(["success" => false, "message" => "Le sujet est requis."]);
    exit;
}
if (empty($content)) {
    echo json_encode(["success" => false, "message" => "Le contenu est requis."]);
    exit;
}

// ── Construire le template HTML ──
$htmlBody = buildNewsletterTemplate($subject, $content);
$textBody = strip_tags($content);

$mailer = new SMTPMailer(true); // debug mode

// ── Mode TEST ──
if ($isTest) {
    $target = !empty($testEmail) ? $testEmail : SMTP_FROM_EMAIL;
    $result = $mailer->send($target, "[TEST] " . $subject, $htmlBody, $textBody);

    echo json_encode([
        "success" => $result["success"],
        "message" => $result["message"],
        "sent" => $result["success"] ? 1 : 0,
        "debug" => $mailer->getLog(),
    ]);
    exit;
}

// ── Mode ENVOI RÉEL ──
$subscribers = getNewsletterSubscribers();
if (empty($subscribers)) {
    echo json_encode(["success" => false, "message" => "Aucun abonné actif."]);
    exit;
}

$sent = 0;
$failed = 0;
$errors = [];

foreach ($subscribers as $sub) {
    $email = $sub["email"] ?? "";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        continue;
    }

    $result = $mailer->send($email, $subject, $htmlBody, $textBody);

    if ($result["success"]) {
        $sent++;
    } else {
        $failed++;
        $errors[] = "{$email}: " . $result["message"];
    }

    // Pause entre emails pour éviter le rate limiting Gmail
    if ($sent < count($subscribers)) {
        sleep(NL_PAUSE_BETWEEN);
    }
}

$message = "{$sent} email(s) envoyé(s)";
if ($failed > 0) {
    $message .= ", {$failed} échoué(s)";
}

echo json_encode([
    "success" => $sent > 0,
    "message" => $message,
    "sent" => $sent,
    "failed" => $failed,
    "errors" => $errors,
    "total" => count($subscribers),
]);

// ═══════════════════════════════════════
//  Template HTML de la newsletter
// ═══════════════════════════════════════
function buildNewsletterTemplate(string $subject, string $body): string
{
    $year = date("Y");
    $siteUrl = "http://" . ($_SERVER["HTTP_HOST"] ?? "localhost");

    return '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin:0; padding:0; background:#0d0d0d; font-family:\'Segoe UI\',Arial,sans-serif; color:#e0e0e0;">

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0d;">
<tr><td align="center" style="padding:20px 10px;">

<!-- Card -->
<table width="600" cellpadding="0" cellspacing="0" style="background:#141414; border:1px solid rgba(255,255,255,0.08); border-radius:12px; overflow:hidden;">

<!-- Header -->
<tr>
<td style="background:#0a0a0a; padding:20px 30px; border-bottom:1px solid rgba(255,130,0,0.2);">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="font-size:11px; letter-spacing:0.2em; color:#6a58ff; font-weight:600;">BLACK_PROTOCOL</td>
<td align="right" style="font-size:10px; color:rgba(255,255,255,0.3);">' . date("d.m.Y") . '</td>
</tr>
</table>
</td>
</tr>

<!-- Title -->
<tr>
<td style="padding:30px 30px 10px;">
<h1 style="margin:0; font-size:22px; font-weight:700; color:#ffffff; letter-spacing:0.02em;">' . htmlspecialchars($subject) . '</h1>
</td>
</tr>

<!-- Orange bar -->
<tr>
<td style="padding:0 30px;">
<div style="width:60px; height:3px; background:linear-gradient(90deg,#6a58ff,#009e60); border-radius:2px;"></div>
</td>
</tr>

<!-- Body -->
<tr>
<td style="padding:20px 30px 30px; font-size:15px; line-height:1.7; color:rgba(255,255,255,0.7);">
' . nl2br(htmlspecialchars($body)) . '
</td>
</tr>

<!-- Footer -->
<tr>
<td style="background:#0a0a0a; padding:20px 30px; border-top:1px solid rgba(255,255,255,0.06);">
<p style="margin:0; font-size:11px; color:rgba(255,255,255,0.25);">
&copy; ' . $year . ' BLACK_PROTOCOL — Abidjan, C&ocirc;te d\'Ivoire<br>
<a href="' . $siteUrl . '/index.php" style="color:#6a58ff; text-decoration:none;">Visiter le portfolio</a>
&nbsp;&bull;&nbsp;
<a href="' . $siteUrl . '/newsletter_action.php?action=unsubscribe&email={{EMAIL}}" style="color:#6a58ff; text-decoration:none;">Se d&eacute;sabonner</a>
</p>
</td>
</tr>

</table>
</td></tr>
</table>

</body>
</html>';
}
