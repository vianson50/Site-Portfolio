<?php
/**
 * BLACK_PROTOCOL — Profil Utilisateur
 * User profile management page with cyberpunk terminal aesthetic.
 * Handles profile updates and password changes.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
requireLogin();

$user = getCurrentUser();

if (!$user) {
    // Session corrompue — détruire et rediriger
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}

// Load DB connection via auth helper (évite la double inclusion de database.php)
$pdo = getDB();

// Message state
$message = "";
$messageType = ""; // 'success' or 'error'

// ── Handle POST Requests ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // ── Update Profile ──
    if ($action === "update_profile") {
        $data = [
            "bio" => trim($_POST["bio"] ?? ""),
            "github_url" => trim($_POST["github_url"] ?? ""),
            "linkedin_url" => trim($_POST["linkedin_url"] ?? ""),
            "discord_url" => trim($_POST["discord_url"] ?? ""),
        ];

        // ── Avatar upload ──
        if (
            isset($_FILES["avatar"]) &&
            $_FILES["avatar"]["error"] === UPLOAD_ERR_OK
        ) {
            $file = $_FILES["avatar"];
            $allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/gif",
                "image/webp",
            ];
            $maxSize = 2 * 1024 * 1024; // 2 Mo

            if (!in_array($file["type"], $allowedTypes)) {
                $message =
                    "FORMAT NON SUPPORTÉ // JPG, PNG, GIF, WEBP UNIQUEMENT";
                $messageType = "error";
            } elseif ($file["size"] > $maxSize) {
                $message = "FICHIER TROP LOURD // MAXIMUM 2 MO";
                $messageType = "error";
            } else {
                $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
                $safeName = "user_" . $user["id"] . "_" . time() . "." . $ext;
                $uploadDir = __DIR__ . "/uploads/avatars/";

                // Supprimer l'ancien avatar (si c'est un fichier local)
                if (
                    !empty($user["avatar"]) &&
                    strpos($user["avatar"], "uploads/avatars/") !== false
                ) {
                    $oldPath = __DIR__ . "/" . $user["avatar"];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                if (
                    move_uploaded_file(
                        $file["tmp_name"],
                        $uploadDir . $safeName,
                    )
                ) {
                    $data["avatar"] = "uploads/avatars/" . $safeName;
                }
            }
        }

        if (empty($message)) {
            if (updateProfile((int) $user["id"], $data)) {
                $message =
                    "PROFIL MIS À JOUR AVEC SUCCÈS // DONNÉES SYNCHRONISÉES";
                $messageType = "success";
                // Refresh user data
                $user = getCurrentUser();
            } else {
                $message = "ERREUR // ÉCHEC DE LA MISE À JOUR DU PROFIL";
                $messageType = "error";
            }
        }
    }

    // ── Change Password ──
    if ($action === "change_password") {
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        // Verify current password
        if (!password_verify($currentPassword, $user["password"])) {
            $message = "ERREUR // MOT DE PASSE ACTUEL INCORRECT";
            $messageType = "error";
        }
        // Validate new password length
        elseif (strlen($newPassword) < 8) {
            $message =
                "ERREUR // LE NOUVEAU MOT DE PASSE DOIT CONTENIR AU MOINS 8 CARACTÈRES";
            $messageType = "error";
        }
        // Confirm passwords match
        elseif ($newPassword !== $confirmPassword) {
            $message = "ERREUR // LES MOTS DE PASSE NE CORRESPONDENT PAS";
            $messageType = "error";
        }
        // Update password
        else {
            if (updatePassword((int) $user["id"], $newPassword)) {
                $message =
                    "MOT DE PASSE MODIFIÉ AVEC SUCCÈS // SÉCURITÉ RENFORCÉE";
                $messageType = "success";
                // Refresh user data (new hash)
                $user = getCurrentUser();
            } else {
                $message = "ERREUR // ÉCHEC DE LA MISE À JOUR DU MOT DE PASSE";
                $messageType = "error";
            }
        }
    }
}

// Prepare display values
$year = date("Y");
$username = htmlspecialchars($user["username"] ?? "UNKNOWN");
$email = htmlspecialchars($user["email"] ?? "N/A");
$role = $user["role"] ?? "user";
$bio = htmlspecialchars($user["bio"] ?? "");
$githubUrl = htmlspecialchars($user["github_url"] ?? "");
$linkedinUrl = htmlspecialchars($user["linkedin_url"] ?? "");
$discordUrl = htmlspecialchars($user["discord_url"] ?? "");
$avatar =
    $user["avatar"] ??
    "https://lh3.googleusercontent.com/aida-public/AB6AXuB4b3gbtVkhHvi8Hn6IJ4xFJ72NQKz74gEMW8eAF2AuE19vjailbpVCojG-0tUymwFkXGLRcrP8Gn7SmFpN31KIMtUbe3tstVdrTpmSBexT6nXsIGs69pxKwB88IkFtBFEQcnrniMyVgNHgZxgrC10msciQLtfYPKpkINreY3t-GDhZJpZwRglyOLujHrKdaIh4TAQG5C_OHuh7H_TsonZ2b1iwiprz6HNmCr1uBu1m2wLq9H6EDD3oHCnq40SYW2Z-4RYrVrbT1-w";
$createdAt = $user["created_at"] ?? date("Y-m-d");
$memberSince = date("d/m/Y", strtotime($createdAt));
$roleBadge = strtoupper($role);
$roleIsAdmin = $role === "admin";

// Format social handles for display
$githubHandle = $githubUrl
    ? preg_replace("#^https?://github\.com/#", "", $githubUrl)
    : "";
$linkedinHandle = $linkedinUrl
    ? preg_replace("#^https?://linkedin\.com/in/#", "", $linkedinUrl)
    : "";
$discordHandle = $discordUrl ? ltrim($discordUrl, "@") : "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= $username ?> | BLACK_PROTOCOL</title>
    <?= renderMeta(
        "Profil de {$username} — Espace Personnel",
        "Gérez votre profil BLACK_PROTOCOL. Modifiez vos informations, liens sociaux GitHub, LinkedIn, Discord et renforcez la sécurité de votre compte.",
        "profile",
    ) ?>

    <!-- Fonts: Space Grotesk + Inter + Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Material Symbols config -->
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    <!-- Profile-specific CSS -->
    <style>
        /* ========================================
           PROFILE PAGE — SPECIFIC STYLES
           ======================================== */

        .profile-section {
            padding: var(--sp-xl) 0 var(--sp-3xl);
        }

        /* ── Section Header ── */
        .profile-section-header {
            margin-bottom: var(--sp-lg);
        }

        .profile-section-label {
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
            margin-bottom: var(--sp-sm);
        }

        .profile-section-label__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--secondary);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(0, 158, 96, 0.5); }
            50% { opacity: 0.7; box-shadow: 0 0 8px 2px rgba(0, 158, 96, 0.3); }
        }

        .profile-section-label span:last-child {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--secondary-bright);
        }

        .profile-section-header h1 {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: var(--primary);
            margin-bottom: var(--sp-xs);
        }

        .profile-section-header h1 .username {
            color: var(--white);
        }

        .profile-section-header .bio-display {
            font-family: var(--font-body);
            font-size: 16px;
            color: var(--on-surface-variant);
            max-width: 600px;
            line-height: 1.6;
            margin-top: var(--sp-sm);
        }

        /* ── Bento Grid ── */
        .profile-bento {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-md);
        }

        @media (min-width: 768px) {
            .profile-bento {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .profile-bento {
                grid-template-columns: repeat(12, 1fr);
            }
        }

        /* ── Profile Card Base ── */
        .profile-card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.06);
            position: relative;
            overflow: hidden;
        }

        .profile-card__bar {
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
            padding: var(--sp-md);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.2);
        }

        .profile-card__bar-dots {
            display: flex;
            gap: 6px;
        }

        .profile-card__bar-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .profile-card__bar-dot--red { background: #ff5f57; }
        .profile-card__bar-dot--yellow { background: #febc2e; }
        .profile-card__bar-dot--green { background: #28c840; }

        .profile-card__bar-title {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--on-surface-variant);
        }

        .profile-card__body {
            padding: var(--sp-lg);
        }

        /* ── Profile Info Card (larger) ── */
        @media (min-width: 1024px) {
            .profile-card--info {
                grid-column: span 5;
            }
        }

        .profile-info__avatar-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto var(--sp-lg);
        }

        .profile-info__avatar-glow {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                var(--primary),
                var(--white),
                var(--secondary),
                var(--primary)
            );
            animation: avatar-rotate 6s linear infinite;
        }

        @keyframes avatar-rotate {
            to { transform: rotate(360deg); }
        }

        .profile-info__avatar-frame {
            position: absolute;
            inset: 3px;
            border-radius: 50%;
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-info__avatar-frame img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-info__name {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            color: var(--white);
            text-align: center;
            margin-bottom: var(--sp-xs);
        }

        .profile-info__email {
            font-family: var(--font-display);
            font-size: 14px;
            letter-spacing: 0.03em;
            color: var(--on-surface-variant);
            text-align: center;
            margin-bottom: var(--sp-md);
        }

        .profile-info__badge {
            display: inline-flex;
            align-items: center;
            gap: var(--sp-xs);
            padding: var(--sp-xs) var(--sp-sm);
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin: 0 auto var(--sp-md);
            text-align: center;
        }

        .profile-info__badge--admin {
            background: rgba(255, 130, 0, 0.15);
            color: var(--primary);
            border: 1px solid rgba(255, 130, 0, 0.3);
        }

        .profile-info__badge--user {
            background: rgba(0, 158, 96, 0.15);
            color: var(--secondary-bright);
            border: 1px solid rgba(0, 158, 96, 0.3);
        }

        .profile-info__meta {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: var(--sp-md);
        }

        .profile-info__meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--sp-sm) 0;
        }

        .profile-info__meta-key {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
        }

        .profile-info__meta-value {
            font-family: var(--font-display);
            font-size: 14px;
            color: var(--on-surface);
        }

        .profile-info__session-status {
            display: flex;
            align-items: center;
            gap: var(--sp-xs);
        }

        .profile-info__session-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--secondary);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        /* ── Edit Profile Card ── */
        @media (min-width: 1024px) {
            .profile-card--edit {
                grid-column: span 7;
            }
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: var(--sp-md);
        }

        .profile-form__group {
            display: flex;
            flex-direction: column;
            gap: var(--sp-xs);
        }

        .profile-form__label {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--on-surface-variant);
        }

        .profile-form__input,
        .profile-form__textarea {
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--white);
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-sm) var(--sp-md);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .profile-form__input::placeholder,
        .profile-form__textarea::placeholder {
            color: rgba(255, 255, 255, 0.25);
            font-family: var(--font-display);
            font-size: 13px;
            letter-spacing: 0.03em;
        }

        .profile-form__input:focus,
        .profile-form__textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 12px rgba(255, 130, 0, 0.15);
        }

        .profile-form__textarea {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        .profile-form__submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-sm);
            padding: var(--sp-sm) var(--sp-lg);
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            align-self: flex-start;
        }

        .profile-form__submit--orange {
            background: var(--primary);
            color: var(--void);
        }

        .profile-form__submit--orange:hover {
            background: #e67500;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .profile-form__submit--orange:active {
            transform: scale(0.97);
        }

        .profile-form__submit--green-outline {
            background: transparent;
            color: var(--secondary-bright);
            border: 1px solid var(--secondary);
        }

        .profile-form__submit--green-outline:hover {
            background: rgba(0, 158, 96, 0.1);
            box-shadow: 0 0 20px var(--secondary-glow);
        }

        .profile-form__submit--green-outline:active {
            transform: scale(0.97);
        }

        /* ── Avatar Upload ── */
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--sp-sm);
        }
        .avatar-upload__preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            background-color: var(--surface);
            border: 2px solid rgba(255,255,255,0.1);
            position: relative;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .avatar-upload__preview:hover {
            border-color: var(--primary);
        }
        .avatar-upload__overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
            color: var(--white);
        }
        .avatar-upload__preview:hover .avatar-upload__overlay {
            opacity: 1;
        }
        .avatar-upload__overlay .material-symbols-outlined {
            font-size: 24px;
        }
        .avatar-upload__btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 14px;
            font-family: var(--font-display);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--primary);
            background: rgba(255,130,0,0.08);
            border: 1px solid rgba(255,130,0,0.2);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
        }
        .avatar-upload__btn:hover {
            background: rgba(255,130,0,0.15);
            border-color: rgba(255,130,0,0.4);
        }
        .avatar-upload__hint {
            font-family: var(--font-display);
            font-size: 10px;
            color: rgba(255,255,255,0.25);
            letter-spacing: 0.05em;
        }

        /* ── Social Links Card ── */
        @media (min-width: 1024px) {
            .profile-card--social {
                grid-column: span 6;
            }
        }

        .social-links__list {
            display: flex;
            flex-direction: column;
            gap: var(--sp-sm);
        }

        .social-links__item {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
            padding: var(--sp-sm) var(--sp-md);
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.2s ease;
        }

        .social-links__item:hover {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(0, 0, 0, 0.35);
        }

        .social-links__item-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }

        .social-links__item-icon .material-symbols-outlined {
            font-size: 20px;
            color: var(--on-surface-variant);
        }

        .social-links__item:hover .social-links__item-icon {
            border-color: var(--primary);
        }

        .social-links__item:hover .social-links__item-icon .material-symbols-outlined {
            color: var(--primary);
        }

        .social-links__item-platform {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--white);
        }

        .social-links__item-handle {
            font-family: var(--font-display);
            font-size: 13px;
            letter-spacing: 0.03em;
            color: var(--on-surface-variant);
        }

        .social-links__empty {
            font-family: var(--font-display);
            font-size: 13px;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.3);
            padding: var(--sp-sm) 0;
        }

        /* ── Change Password Card ── */
        @media (min-width: 1024px) {
            .profile-card--password {
                grid-column: span 6;
            }
        }

        /* ── Quick Links Card ── */
        @media (min-width: 1024px) {
            .profile-card--quicklinks {
                grid-column: span 12;
            }
        }

        .quicklinks__list {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-sm);
        }

        .quicklinks__item {
            display: inline-flex;
            align-items: center;
            gap: var(--sp-sm);
            padding: var(--sp-sm) var(--sp-md);
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--on-surface);
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            background: none;
        }

        .quicklinks__item:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(255, 130, 0, 0.05);
        }

        .quicklinks__item .material-symbols-outlined {
            font-size: 18px;
        }

        .quicklinks__item--admin {
            border-color: rgba(255, 130, 0, 0.3);
            color: var(--primary);
        }

        .quicklinks__item--admin:hover {
            background: rgba(255, 130, 0, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 12px rgba(255, 130, 0, 0.15);
        }

        .quicklinks__item--logout {
            border-color: rgba(255, 90, 90, 0.3);
            color: #ff6b6b;
        }

        .quicklinks__item--logout:hover {
            background: rgba(255, 90, 90, 0.08);
            border-color: #ff6b6b;
            color: #ff4040;
        }

        /* ── Toast / Message Banner ── */
        .profile-message {
            padding: var(--sp-sm) var(--sp-md);
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: var(--sp-lg);
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
            border-left: 3px solid;
        }

        .profile-message--success {
            background: rgba(0, 158, 96, 0.1);
            border-color: var(--secondary);
            color: var(--secondary-bright);
        }

        .profile-message--error {
            background: rgba(255, 90, 90, 0.1);
            border-color: #ff6b6b;
            color: #ff8a8a;
        }

        .profile-message .material-symbols-outlined {
            font-size: 20px;
        }

        /* ── Scanline overlay on cards ── */
        .profile-card--scanlines::after {
            content: "";
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.06) 2px,
                rgba(0, 0, 0, 0.06) 4px
            );
            pointer-events: none;
            z-index: 1;
        }

        /* ── Animate In ── */
        .profile-card {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .profile-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger delays */
        .profile-card:nth-child(1) { transition-delay: 0.0s; }
        .profile-card:nth-child(2) { transition-delay: 0.06s; }
        .profile-card:nth-child(3) { transition-delay: 0.12s; }
        .profile-card:nth-child(4) { transition-delay: 0.18s; }
        .profile-card:nth-child(5) { transition-delay: 0.24s; }

        /* ── Password Strength Indicator ── */
        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 4px;
        }

        .password-strength__bar {
            height: 2px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: background 0.3s ease;
        }

        .password-strength__bar--active-weak { background: #ff6b6b; }
        .password-strength__bar--active-medium { background: #febc2e; }
        .password-strength__bar--active-strong { background: var(--secondary); }
    </style>
</head>
<body>

    <!-- ================================
         TOP HEADER BAR
         ================================ -->
    <header class="top-header">
        <div class="top-header__inner">
            <div class="top-header__brand">
                <div class="top-header__avatar">
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Profile">
                </div>
                <span class="top-header__name">BLACK_PROTOCOL</span>
            </div>
            <div class="top-header__actions">
                <a href="index.php" class="top-header__btn top-header__btn--login">
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                    PORTFOLIO
                </a>
                <?php if (isset($user) && $user["role"] === "admin"): ?>
                    <a href="admin.php" class="top-header__btn top-header__btn--admin">
                        <span class="material-symbols-outlined" style="font-size:16px;">shield</span>
                        ADMIN
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="top-header__btn top-header__btn--logout">
                    <span class="material-symbols-outlined" style="font-size:16px;">logout</span>
                    DÉCO.
                </a>
            </div>
        </div>
    </header>

    <!-- ================================
         PROFILE SECTION
         ================================ -->
    <section class="profile-section">
        <div class="container">

            <!-- Section Header -->
            <div class="profile-section-header">
                <div class="profile-section-label">
                    <span class="profile-section-label__dot"></span>
                    <span>PROFIL_UTILISATEUR</span>
                </div>
                <h1>MON_PROFIL // <span class="username"><?= $username ?></span></h1>
                <?php if (!empty($bio)): ?>
                    <p class="bio-display"><?= nl2br($bio) ?></p>
                <?php endif; ?>
            </div>

            <!-- Toast Message -->
            <?php if (!empty($message)): ?>
                <div class="profile-message profile-message--<?= $messageType ?>">
                    <span class="material-symbols-outlined"><?= $messageType ===
                    "success"
                        ? "check_circle"
                        : "error" ?></span>
                    <span><?= $message ?></span>
                </div>
            <?php endif; ?>

            <!-- Bento Grid -->
            <div class="profile-bento">

                <!-- ================================
                     PROFILE INFO CARD
                     ================================ -->
                <div class="profile-card profile-card--info profile-card--scanlines">
                    <div class="profile-card__bar">
                        <div class="profile-card__bar-dots">
                            <span class="profile-card__bar-dot profile-card__bar-dot--red"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--yellow"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--green"></span>
                        </div>
                        <span class="profile-card__bar-title">USER_DATA</span>
                    </div>
                    <div class="profile-card__body" style="text-align:center;">
                        <!-- Avatar with gradient border -->
                        <div class="profile-info__avatar-wrap">
                            <div class="profile-info__avatar-glow"></div>
                            <div class="profile-info__avatar-frame">
                                <img src="<?= htmlspecialchars(
                                    $avatar,
                                ) ?>" alt="<?= $username ?>">
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="profile-info__name"><?= $username ?></div>

                        <!-- Email -->
                        <div class="profile-info__email"><?= $email ?></div>

                        <!-- Role Badge -->
                        <div style="text-align:center;">
                            <span class="profile-info__badge <?= $roleIsAdmin
                                ? "profile-info__badge--admin"
                                : "profile-info__badge--user" ?>">
                                <span class="material-symbols-outlined" style="font-size:14px;"><?= $roleIsAdmin
                                    ? "shield"
                                    : "person" ?></span>
                                <?= $roleBadge ?>
                            </span>
                        </div>

                        <!-- Meta Rows -->
                        <div class="profile-info__meta">
                            <div class="profile-info__meta-row">
                                <span class="profile-info__meta-key">MEMBRE_DEPUIS</span>
                                <span class="profile-info__meta-value"><?= $memberSince ?></span>
                            </div>
                            <div class="profile-info__meta-row">
                                <span class="profile-info__meta-key">STATUT</span>
                                <span class="profile-info__meta-value">
                                    <span class="profile-info__session-status">
                                        <span class="profile-info__session-dot"></span>
                                        <span style="color: var(--secondary-bright); font-family: var(--font-display); font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase;">SESSION_ACTIVE</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================================
                     EDIT PROFILE CARD
                     ================================ -->
                <div class="profile-card profile-card--edit">
                    <div class="profile-card__bar">
                        <div class="profile-card__bar-dots">
                            <span class="profile-card__bar-dot profile-card__bar-dot--red"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--yellow"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--green"></span>
                        </div>
                        <span class="profile-card__bar-title">
                            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:4px;">edit</span>
                            EDIT_PROFILE
                        </span>
                    </div>
                    <div class="profile-card__body">
                        <form class="profile-form" method="POST" action="profile.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">

                            <!-- Avatar Upload -->
                            <div class="profile-form__group" style="align-items:center; margin-bottom:var(--sp-lg);">
                                <label class="profile-form__label" style="margin-bottom:8px;">PHOTO_DE_PROFIL</label>
                                <div class="avatar-upload">
                                    <div class="avatar-upload__preview" id="avatarPreview" style="background-image:url('<?= htmlspecialchars(
                                        $avatar,
                                    ) ?>');">
                                        <div class="avatar-upload__overlay">
                                            <span class="material-symbols-outlined">photo_camera</span>
                                        </div>
                                    </div>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">
                                    <button type="button" class="avatar-upload__btn" onclick="document.getElementById('avatarInput').click();">
                                        <span class="material-symbols-outlined" style="font-size:14px;">upload</span>
                                        Changer la photo
                                    </button>
                                    <span class="avatar-upload__hint">JPG, PNG, GIF ou WEBP — 2 Mo max</span>
                                </div>
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="bio">BIO</label>
                                <textarea
                                    class="profile-form__textarea"
                                    id="bio"
                                    name="bio"
                                    placeholder="Décrivez-vous..."
                                    rows="4"
                                ><?= htmlspecialchars(
                                    $user["bio"] ?? "",
                                ) ?></textarea>
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="github_url">GITHUB_URL</label>
                                <input
                                    class="profile-form__input"
                                    type="text"
                                    id="github_url"
                                    name="github_url"
                                    placeholder="https://github.com/votre-profil"
                                    value="<?= $githubUrl ?>"
                                >
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="linkedin_url">LINKEDIN_URL</label>
                                <input
                                    class="profile-form__input"
                                    type="text"
                                    id="linkedin_url"
                                    name="linkedin_url"
                                    placeholder="https://linkedin.com/in/votre-profil"
                                    value="<?= $linkedinUrl ?>"
                                >
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="discord_url">DISCORD_URL</label>
                                <input
                                    class="profile-form__input"
                                    type="text"
                                    id="discord_url"
                                    name="discord_url"
                                    placeholder="@votre_pseudo_discord"
                                    value="<?= $discordUrl ?>"
                                >
                            </div>

                            <button type="submit" class="profile-form__submit profile-form__submit--orange">
                                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                                <span>METTRE_À_JOUR</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ================================
                     SOCIAL LINKS CARD
                     ================================ -->
                <div class="profile-card profile-card--social">
                    <div class="profile-card__bar">
                        <div class="profile-card__bar-dots">
                            <span class="profile-card__bar-dot profile-card__bar-dot--red"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--yellow"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--green"></span>
                        </div>
                        <span class="profile-card__bar-title">
                            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:4px;">link</span>
                            DIRECT_CHANNELS
                        </span>
                    </div>
                    <div class="profile-card__body">
                        <div class="social-links__list">
                            <?php if (!empty($githubUrl)): ?>
                                <a href="<?= htmlspecialchars(
                                    $githubUrl,
                                ) ?>" target="_blank" rel="noopener noreferrer" class="social-links__item">
                                    <div class="social-links__item-icon">
                                        <span class="material-symbols-outlined">terminal</span>
                                    </div>
                                    <div>
                                        <div class="social-links__item-platform">GITHUB</div>
                                        <div class="social-links__item-handle"><?= htmlspecialchars(
                                            $githubHandle ?: $githubUrl,
                                        ) ?></div>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($linkedinUrl)): ?>
                                <a href="<?= htmlspecialchars(
                                    $linkedinUrl,
                                ) ?>" target="_blank" rel="noopener noreferrer" class="social-links__item">
                                    <div class="social-links__item-icon">
                                        <span class="material-symbols-outlined">account_circle</span>
                                    </div>
                                    <div>
                                        <div class="social-links__item-platform">LINKEDIN</div>
                                        <div class="social-links__item-handle"><?= htmlspecialchars(
                                            $linkedinHandle ?: $linkedinUrl,
                                        ) ?></div>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($discordUrl)): ?>
                                <div class="social-links__item">
                                    <div class="social-links__item-icon">
                                        <span class="material-symbols-outlined">forum</span>
                                    </div>
                                    <div>
                                        <div class="social-links__item-platform">DISCORD</div>
                                        <div class="social-links__item-handle"><?= htmlspecialchars(
                                            $discordHandle ?: $discordUrl,
                                        ) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (
                                empty($githubUrl) &&
                                empty($linkedinUrl) &&
                                empty($discordUrl)
                            ): ?>
                                <div class="social-links__empty">
                                    <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:4px;">link_off</span>
                                    AUCUN CANAL CONFIGURÉ // METTEZ À JOUR VOS LIENS
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ================================
                     CHANGE PASSWORD CARD
                     ================================ -->
                <div class="profile-card profile-card--password">
                    <div class="profile-card__bar">
                        <div class="profile-card__bar-dots">
                            <span class="profile-card__bar-dot profile-card__bar-dot--red"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--yellow"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--green"></span>
                        </div>
                        <span class="profile-card__bar-title">
                            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:4px;">lock</span>
                            SECURITY_MODULE
                        </span>
                    </div>
                    <div class="profile-card__body">
                        <form class="profile-form" method="POST" action="profile.php">
                            <input type="hidden" name="action" value="change_password">

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="current_password">MOT_DE_PASSE_ACTUEL</label>
                                <input
                                    class="profile-form__input"
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    placeholder="••••••••"
                                    required
                                >
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="new_password">NOUVEAU_MOT_DE_PASSE</label>
                                <input
                                    class="profile-form__input"
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    placeholder="Min. 8 caractères"
                                    required
                                    minlength="8"
                                >
                                <div class="password-strength" id="password-strength">
                                    <div class="password-strength__bar" id="str-bar-1"></div>
                                    <div class="password-strength__bar" id="str-bar-2"></div>
                                    <div class="password-strength__bar" id="str-bar-3"></div>
                                    <div class="password-strength__bar" id="str-bar-4"></div>
                                </div>
                            </div>

                            <div class="profile-form__group">
                                <label class="profile-form__label" for="confirm_password">CONFIRMER</label>
                                <input
                                    class="profile-form__input"
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="••••••••"
                                    required
                                >
                            </div>

                            <button type="submit" class="profile-form__submit profile-form__submit--green-outline">
                                <span class="material-symbols-outlined" style="font-size:18px;">lock</span>
                                <span>CHANGER_MOT_DE_PASSE</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ================================
                     QUICK LINKS CARD
                     ================================ -->
                <div class="profile-card profile-card--quicklinks">
                    <div class="profile-card__bar">
                        <div class="profile-card__bar-dots">
                            <span class="profile-card__bar-dot profile-card__bar-dot--red"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--yellow"></span>
                            <span class="profile-card__bar-dot profile-card__bar-dot--green"></span>
                        </div>
                        <span class="profile-card__bar-title">
                            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:4px;">quick_reference_all</span>
                            QUICK_ACCESS
                        </span>
                    </div>
                    <div class="profile-card__body">
                        <div class="quicklinks__list">
                            <?php if ($roleIsAdmin): ?>
                                <a href="admin.php" class="quicklinks__item quicklinks__item--admin">
                                    <span class="material-symbols-outlined">shield</span>
                                    <span>ADMIN_PANEL</span>
                                </a>
                            <?php endif; ?>

                            <a href="index.php" class="quicklinks__item">
                                <span class="material-symbols-outlined">home</span>
                                <span>PORTFOLIO</span>
                            </a>

                            <a href="logout.php" class="quicklinks__item quicklinks__item--logout">
                                <span class="material-symbols-outlined">logout</span>
                                <span>DÉCONNEXION</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div><!-- /profile-bento -->
        </div><!-- /container -->
    </section>

    <!-- ================================
         FOOTER
         ================================ -->
    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <p class="footer__copy">
                    &copy; <?= $year ?> — <span>BLACK_PROTOCOL</span>. Tous droits réservés.
                    <span class="footer__flag">
                        <span class="footer__flag-orange"></span>
                        <span class="footer__flag-white"></span>
                        <span class="footer__flag-green"></span>
                    </span>
                </p>
                <p class="footer__copy code-sm">
                    Construit avec <span style="color: var(--primary);">PHP</span> +
                    <span style="color: var(--secondary-bright);">passion</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- ================================
         BOTTOM NAVIGATION
         ================================ -->
    <nav class="nav-bottom" aria-label="Navigation principale">
        <ul class="nav-bottom__list">
            <li class="nav-bottom__item">
                <a href="index.php" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">home</span>
                    <span class="nav-bottom__text">Home</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#skills" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">bolt</span>
                    <span class="nav-bottom__text">Skills</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#projects" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">grid_view</span>
                    <span class="nav-bottom__text">Projects</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="profile.php" class="nav-bottom__link active">
                    <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' 1;">person</span>
                    <span class="nav-bottom__text">Profile</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- ================================
         JAVASCRIPT
         ================================ -->
    <script>
        // ── Animate cards on scroll ──
        const cards = document.querySelectorAll('.profile-card');
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });

        cards.forEach(card => cardObserver.observe(card));

        // ── Password strength indicator ──
        const newPasswordInput = document.getElementById('new_password');
        const bars = [
            document.getElementById('str-bar-1'),
            document.getElementById('str-bar-2'),
            document.getElementById('str-bar-3'),
            document.getElementById('str-bar-4')
        ];

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function () {
                const val = this.value;
                let score = 0;

                if (val.length >= 4) score++;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

                bars.forEach((bar, i) => {
                    bar.className = 'password-strength__bar';
                    if (i < score) {
                        if (score <= 1) bar.classList.add('password-strength__bar--active-weak');
                        else if (score <= 2) bar.classList.add('password-strength__bar--active-medium');
                        else if (score <= 3) bar.classList.add('password-strength__bar--active-medium');
                        else bar.classList.add('password-strength__bar--active-strong');
                    }
                });
            });
        }

        // ── Auto-hide message after 6 seconds ──
        const msgEl = document.querySelector('.profile-message');
        if (msgEl) {
            setTimeout(() => {
                msgEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                msgEl.style.opacity = '0';
                msgEl.style.transform = 'translateY(-10px)';
                setTimeout(() => msgEl.remove(), 500);
            }, 6000);
            }

            // ── Avatar preview ──
            document.getElementById('avatarInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        document.getElementById('avatarPreview').style.backgroundImage = 'url(' + ev.target.result + ')';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Click preview to open file picker
            document.getElementById('avatarPreview').addEventListener('click', function() {
                document.getElementById('avatarInput').click();
            });
        </script>

</body>
</html>
