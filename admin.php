<?php
/**
 * BLACK_PROTOCOL — Admin Dashboard
 * Design System: Minimalist Cyberpunk × Professional Brutalism
 * Access: Admin only (requires auth)
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
require_once __DIR__ . "/includes/newsletter.php";
requireAdmin();

$currentUser = getCurrentUser();

if (!$currentUser) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}
$allUsers = getAllUsers();
$messages = getMessages();
$unreadCount = getMessageCount();
$subscribers = getNewsletterSubscribers();
$subscriberCount = getNewsletterCount();
$totalUsers = is_array($allUsers) ? count($allUsers) : 0;
$totalMsgs = is_array($messages) ? count($messages) : 0;

// ── Handle POST actions ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action === "delete_user" && isset($_POST["user_id"])) {
        $userId = (int) $_POST["user_id"];
        if ($userId !== (int) $currentUser["id"]) {
            deleteUser($userId);
        }
        header("Location: admin.php");
        exit();
    } elseif ($action === "toggle_active" && isset($_POST["user_id"])) {
        $userId = (int) $_POST["user_id"];
        $targetUser = null;
        foreach ($allUsers as $u) {
            if ((int) $u["id"] === $userId) {
                $targetUser = $u;
                break;
            }
        }
        if ($targetUser && $userId !== (int) $currentUser["id"]) {
            // Interdire de désactiver un compte admin
            $targetRole = $targetUser["role"] ?? "user";
            $currentActive = (bool) ($targetUser["is_active"] ?? true);
            if ($targetRole === "admin" && $currentActive) {
                // Ne jamais désactiver un admin
                header("Location: admin.php");
                exit();
            }
            $newStatus = $currentActive ? 0 : 1;
            // Update user active status directly
            $pdo = getDB();
            $stmt = $pdo->prepare(
                "UPDATE users SET is_active = :active WHERE id = :id",
            );
            $stmt->execute([":active" => $newStatus, ":id" => $userId]);
        }
        header("Location: admin.php");
        exit();
    } elseif ($action === "delete_message" && isset($_POST["message_id"])) {
        $messageId = (int) $_POST["message_id"];
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute([":id" => $messageId]);
        header("Location: admin.php");
        exit();
    } elseif (
        $action === "delete_subscriber" &&
        isset($_POST["subscriber_id"])
    ) {
        $subId = (int) $_POST["subscriber_id"];
        deleteSubscriber($subId);
        header("Location: admin.php");
        exit();
    }
}

$lang = "fr";
$year = date("Y");
$seoTitle = "Panneau d'Administration — Gestion du Système";
$title = $seoTitle . " | BLACK_PROTOCOL";
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <?= renderMeta(
        $seoTitle,
        "Panneau d'administration BLACK_PROTOCOL. Gestion centralisée des utilisateurs, abonnés newsletter, messages et configuration système en temps réel.",
        "website",
    ) ?>

    <!-- Fonts: Space Grotesk + Inter + Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ── Material Symbols Config ── */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* ── Admin-specific Styles ── */
        .admin-wrapper {
            min-height: 100vh;
            padding-bottom: 96px;
        }

        /* Dashboard Header */
        .admin-header {
            padding: var(--sp-lg) 0 var(--sp-md);
            border-left: 4px solid var(--primary);
            padding-left: var(--sp-md);
            margin-bottom: var(--sp-lg);
        }

        .admin-header__title {
            font-family: var(--font-display);
            font-size: clamp(24px, 5vw, 40px);
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .admin-header__subtitle {
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.05em;
            margin-top: var(--sp-xs);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: var(--sp-md);
            margin-bottom: var(--sp-xl);
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-lg);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card--orange::before { background: var(--primary); }
        .stat-card--green::before  { background: var(--secondary); }

        .stat-card__label {
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: var(--sp-sm);
        }

        .stat-card__value {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 700;
            color: var(--white);
            line-height: 1;
        }

        .stat-card__icon {
            position: absolute;
            top: var(--sp-md);
            right: var(--sp-md);
            font-size: 28px;
            color: rgba(255, 255, 255, 0.15);
        }

        .stat-card__icon .material-symbols-outlined {
            font-size: 28px;
        }

        /* Pulse dot */
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .pulse-dot--green {
            background: var(--secondary);
            box-shadow: 0 0 8px var(--secondary-glow), 0 0 16px var(--secondary-glow);
        }

        .pulse-dot--orange {
            background: var(--primary);
            box-shadow: 0 0 8px var(--primary-glow), 0 0 16px var(--primary-glow);
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.4); }
        }

        /* Section headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
            margin-bottom: var(--sp-lg);
            flex-wrap: wrap;
        }

        .section-header__title {
            font-family: var(--font-display);
            font-size: clamp(18px, 3vw, 24px);
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.01em;
        }

        .badge {
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border: 1px solid;
        }

        .badge--green {
            color: var(--secondary-bright);
            border-color: rgba(0, 158, 96, 0.4);
            background: rgba(0, 158, 96, 0.1);
        }

        .badge--orange {
            color: var(--primary);
            border-color: rgba(255, 130, 0, 0.4);
            background: rgba(255, 130, 0, 0.1);
        }

        /* Users Cards */
        .users-list {
            display: flex;
            flex-direction: column;
            gap: var(--sp-sm);
            margin-bottom: var(--sp-xl);
        }

        .user-card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-md) var(--sp-lg);
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-sm);
            transition: border-color 0.2s ease;
        }

        @media (min-width: 768px) {
            .user-card {
                grid-template-columns: 50px 1fr 1fr 100px 100px 120px 120px;
                align-items: center;
                gap: var(--sp-md);
            }
        }

        .user-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .user-card__id {
            font-family: var(--font-display);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.05em;
        }

        .user-card__username {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 600;
            color: var(--white);
        }

        .user-card__email {
            font-family: var(--font-body);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .user-card__role {
            display: inline-flex;
            align-items: center;
        }

        .role-badge {
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 3px 8px;
            border: 1px solid;
        }

        .role-badge--admin {
            color: var(--primary);
            border-color: rgba(255, 130, 0, 0.4);
            background: rgba(255, 130, 0, 0.08);
        }

        .role-badge--user {
            color: var(--secondary-bright);
            border-color: rgba(0, 158, 96, 0.4);
            background: rgba(0, 158, 96, 0.08);
        }

        .user-card__status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.03em;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .status-dot--active {
            background: var(--secondary);
            box-shadow: 0 0 6px var(--secondary-glow);
        }

        .status-dot--inactive {
            background: #ff4444;
            box-shadow: 0 0 6px rgba(255, 68, 68, 0.3);
        }

        .user-card__date {
            font-family: var(--font-display);
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.03em;
        }

        .user-card__actions {
            display: flex;
            gap: var(--sp-xs);
        }

        .action-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.5);
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .action-btn .material-symbols-outlined {
            font-size: 18px;
        }

        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(255, 130, 0, 0.08);
        }

        .action-btn--danger:hover {
            border-color: #ff4444;
            color: #ff4444;
            background: rgba(255, 68, 68, 0.08);
        }

        /* Users table header (desktop) */
        .users-table-header {
            display: none;
            grid-template-columns: 50px 1fr 1fr 100px 100px 120px 120px;
            gap: var(--sp-md);
            padding: var(--sp-sm) var(--sp-lg);
            margin-bottom: var(--sp-xs);
        }

        @media (min-width: 768px) {
            .users-table-header {
                display: grid;
            }
        }

        .users-table-header__label {
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.3);
        }

        /* Messages Cards */
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: var(--sp-sm);
            margin-bottom: var(--sp-xl);
        }

        .message-card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-lg);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: var(--sp-sm);
            transition: border-color 0.2s ease;
            position: relative;
        }

        .message-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .message-card--unread {
            border-left: 3px solid var(--secondary);
        }

        .message-card__sender {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 600;
            color: var(--white);
        }

        .message-card__email {
            font-family: var(--font-body);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
            margin-left: var(--sp-sm);
        }

        .message-card__subject {
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 500;
            color: var(--primary);
            margin-top: var(--sp-xs);
        }

        .message-card__preview {
            font-family: var(--font-body);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: var(--sp-xs);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .message-card__meta {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
            margin-top: var(--sp-sm);
        }

        .message-card__date {
            font-family: var(--font-display);
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.03em;
        }

        .message-card__indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: var(--font-display);
            font-size: 11px;
            letter-spacing: 0.05em;
        }

        .message-card__right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: var(--sp-sm);
        }

        /* Admin Profile Card */
        .admin-profile {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-lg);
            position: relative;
            overflow: hidden;
        }

        .admin-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }

        .admin-profile__header {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
            margin-bottom: var(--sp-lg);
        }

        .admin-profile__avatar {
            width: 48px;
            height: 48px;
            overflow: hidden;
            border: 2px solid var(--primary);
        }

        .admin-profile__avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-profile__name {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            color: var(--white);
        }

        .admin-profile__email {
            font-family: var(--font-body);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }

        .admin-profile__role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .admin-profile__detail {
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
            padding: var(--sp-sm) 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .admin-profile__detail-label {
            font-family: var(--font-display);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
        }

        .admin-profile__detail-value {
            font-family: var(--font-display);
            font-size: 13px;
            color: var(--secondary-bright);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Grid layout for admin page */
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-xl);
        }

        @media (min-width: 900px) {
            .admin-grid {
                grid-template-columns: 1fr 320px;
            }
        }

        .admin-main { }
        .admin-sidebar { }

        /* Delete form inline */
        .inline-form {
            display: inline;
        }

        /* Scanline effect on section titles */
        .scanline-text {
            position: relative;
        }

        /* Empty state */
        .empty-state {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--sp-xl);
            text-align: center;
        }

        .empty-state__icon {
            font-size: 48px;
            color: rgba(255, 255, 255, 0.1);
            margin-bottom: var(--sp-md);
        }

        .empty-state__icon .material-symbols-outlined {
            font-size: 48px;
        }

        .empty-state__text {
            font-family: var(--font-display);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.05em;
        }

        /* Confirm overlay via JS */
        .confirm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            align-items: center;
            justify-content: center;
        }

        .confirm-overlay.active {
            display: flex;
        }

        .confirm-dialog {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: var(--sp-xl);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .confirm-dialog__title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: var(--sp-sm);
        }

        .confirm-dialog__text {
            font-family: var(--font-body);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: var(--sp-lg);
        }

        .confirm-dialog__actions {
            display: flex;
            gap: var(--sp-sm);
            justify-content: center;
        }

        .btn {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 10px 20px;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn--cancel {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.6);
        }

        .btn--cancel:hover {
            border-color: rgba(255, 255, 255, 0.4);
            color: var(--white);
        }

        .btn--danger {
            background: rgba(255, 68, 68, 0.15);
            border-color: #ff4444;
            color: #ff4444;
        }

        .btn--danger:hover {
            background: rgba(255, 68, 68, 0.3);
        }

        /* Section divider */
        .section-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
            margin: var(--sp-xl) 0;
        }
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
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c" alt="Profile">
                </div>
                <span class="top-header__name">BLACK_PROTOCOL</span>
            </div>
            <div class="top-header__actions">
                <a href="index.php" class="top-header__btn top-header__btn--login">
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                    PORTFOLIO
                </a>
                <a href="profile.php" class="top-header__btn top-header__btn--register">
                    <span class="material-symbols-outlined" style="font-size:16px;">person</span>
                    PROFIL
                </a>
                <a href="logout.php" class="top-header__btn top-header__btn--logout">
                    <span class="material-symbols-outlined" style="font-size:16px;">logout</span>
                    DÉCO.
                </a>
            </div>
        </div>
    </header>

    <!-- ================================
         ADMIN DASHBOARD CONTENT
         ================================ -->
    <div class="admin-wrapper">
        <div class="container">

            <!-- Dashboard Header -->
            <div class="admin-header">
                <h1 class="admin-header__title">ADMIN_PANEL</h1>
                <p class="admin-header__subtitle">System_Overview.v2 — <?= date(
                    "Y-m-d H:i:s",
                ) ?></p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card stat-card--orange">
                    <span class="stat-card__label">Utilisateurs</span>
                    <span class="stat-card__value"><?= $totalUsers ?></span>
                    <span class="stat-card__icon"><span class="material-symbols-outlined">people</span></span>
                </div>
                <div class="stat-card stat-card--green">
                    <span class="stat-card__label">Messages</span>
                    <span class="stat-card__value"><?= $totalMsgs ?></span>
                    <span class="stat-card__icon"><span class="material-symbols-outlined">mail</span></span>
                </div>
                <div class="stat-card stat-card--orange">
                    <span class="stat-card__label">Newsletter</span>
                    <span class="stat-card__value"><?= $subscriberCount ?></span>
                    <span class="stat-card__icon"><span class="material-symbols-outlined">campaign</span></span>
                </div>
                <div class="stat-card stat-card--orange">
                    <span class="stat-card__label">Non_lus</span>
                    <span class="stat-card__value"><?= (int) $unreadCount ?></span>
                    <span class="stat-card__icon"><span class="material-symbols-outlined">mark_email_unread</span></span>
                </div>
                <div class="stat-card stat-card--green">
                    <span class="stat-card__label">Admin_access</span>
                    <span class="stat-card__value" style="display:flex; align-items:center; gap:10px;">
                        ACTIVE <span class="pulse-dot pulse-dot--green"></span>
                    </span>
                    <span class="stat-card__icon"><span class="material-symbols-outlined">shield</span></span>
                </div>
            </div>

            <!-- Main Grid: Content + Sidebar -->
            <div class="admin-grid">

                <!-- ── Main Column ── -->
                <div class="admin-main">

                    <!-- ====== Users Management ====== -->
                    <div class="section-header">
                        <span class="material-symbols-outlined" style="color:var(--primary); font-size:24px;">admin_panel_settings</span>
                        <h2 class="section-header__title">GESTION_UTILISATEURS</h2>
                        <span class="badge badge--green">SYSTEM_NOMINAL</span>
                    </div>

                    <!-- Table header (desktop) -->
                    <div class="users-table-header">
                        <span class="users-table-header__label">ID</span>
                        <span class="users-table-header__label">Username</span>
                        <span class="users-table-header__label">Email</span>
                        <span class="users-table-header__label">Rôle</span>
                        <span class="users-table-header__label">Statut</span>
                        <span class="users-table-header__label">Inscription</span>
                        <span class="users-table-header__label">Actions</span>
                    </div>

                    <?php if (!empty($allUsers) && is_array($allUsers)): ?>
                    <div class="users-list">
                        <?php foreach ($allUsers as $user): ?>
                        <?php
                        $uid = $user["id"] ?? 0;
                        $uname = htmlspecialchars($user["username"] ?? "N/A");
                        $uemail = htmlspecialchars($user["email"] ?? "N/A");
                        $urole = $user["role"] ?? "user";
                        $uactive = isset($user["is_active"])
                            ? (bool) $user["is_active"]
                            : true;
                        $udate = isset($user["created_at"])
                            ? date("d/m/Y", strtotime($user["created_at"]))
                            : "—";
                        $isSelf = $uid == ($currentUser["id"] ?? null);
                        $isAdmin = $urole === "admin";
                        ?>
                        <div class="user-card">
                            <span class="user-card__id">#<?= $uid ?></span>
                            <span class="user-card__username"><?= $uname ?></span>
                            <span class="user-card__email"><?= $uemail ?></span>
                            <span class="user-card__role">
                                <span class="role-badge <?= $isAdmin
                                    ? "role-badge--admin"
                                    : "role-badge--user" ?>">
                                    <?= $isAdmin ? "admin" : "user" ?>
                                </span>
                            </span>
                            <span class="user-card__status">
                                <span class="status-dot <?= $uactive
                                    ? "status-dot--active"
                                    : "status-dot--inactive" ?>"></span>
                                <span style="color: <?= $uactive
                                    ? "var(--secondary-bright)"
                                    : "#ff4444" ?>">
                                    <?= $uactive ? "actif" : "inactif" ?>
                                </span>
                            </span>
                            <span class="user-card__date"><?= $udate ?></span>
                            <div class="user-card__actions">
                                <?php if (!$isSelf): ?>
                                <form class="inline-form" method="POST" action="admin.php">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <button type="submit" class="action-btn" title="Basculer le statut">
                                        <span class="material-symbols-outlined">toggle_on</span>
                                    </button>
                                </form>
                                <form class="inline-form" method="POST" action="admin.php" onsubmit="return confirmDelete(this, 'Supprimer l\'utilisateur #<?= $uid ?> ?')">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <button type="submit" class="action-btn action-btn--danger" title="Supprimer">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span style="font-family:var(--font-display); font-size:11px; color:rgba(255,255,255,0.2); letter-spacing:0.05em;">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state__icon"><span class="material-symbols-outlined">person_off</span></div>
                        <p class="empty-state__text">AUCUN UTILISATEUR TROUVÉ</p>
                    </div>
                    <?php endif; ?>

                    <div class="section-divider"></div>

                    <!-- ====== Messages Section ====== -->
                    <div class="section-header">
                        <span class="material-symbols-outlined" style="color:var(--secondary-bright); font-size:24px;">mark_email_read</span>
                        <h2 class="section-header__title">MESSAGES_REÇUS</h2>
                        <?php if ((int) $unreadCount > 0): ?>
                        <span class="badge badge--orange"><?= $unreadCount ?> non lu<?= (int) $unreadCount >
 1
     ? "s"
     : "" ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($messages) && is_array($messages)): ?>
                    <div class="messages-list">
                        <?php foreach ($messages as $msg): ?>
                        <?php
                        $mid = $msg["id"] ?? 0;
                        $mname = htmlspecialchars(
                            $msg["name"] ?? ($msg["sender_name"] ?? "Anonyme"),
                        );
                        $memail = htmlspecialchars(
                            $msg["email"] ?? ($msg["sender_email"] ?? ""),
                        );
                        $msubject = htmlspecialchars(
                            $msg["subject"] ?? "Sans objet",
                        );
                        $mbody = htmlspecialchars(
                            $msg["message"] ?? ($msg["body"] ?? ""),
                        );
                        $mdate = isset($msg["created_at"])
                            ? date("d/m/Y H:i", strtotime($msg["created_at"]))
                            : "—";
                        $mread = isset($msg["is_read"])
                            ? (bool) $msg["is_read"]
                            : false;
                        ?>
                        <div class="message-card <?= !$mread
                            ? "message-card--unread"
                            : "" ?>">
                            <div>
                                <div>
                                    <span class="message-card__sender"><?= $mname ?></span>
                                    <span class="message-card__email">&lt;<?= $memail ?>&gt;</span>
                                </div>
                                <div class="message-card__subject"><?= $msubject ?></div>
                                <div class="message-card__preview"><?= $mbody ?></div>
                                <div class="message-card__meta">
                                    <span class="message-card__date"><?= $mdate ?></span>
                                    <span class="message-card__indicator">
                                        <?php if (!$mread): ?>
                                        <span class="status-dot status-dot--active"></span>
                                        <span style="color:var(--secondary-bright); font-weight:600;">NON LU</span>
                                        <?php else: ?>
                                        <span class="material-symbols-outlined" style="font-size:14px; color:rgba(255,255,255,0.3);">drafts</span>
                                        <span style="color:rgba(255,255,255,0.3);">LU</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="message-card__right">
                                <form class="inline-form" method="POST" action="admin.php" onsubmit="return confirmDelete(this, 'Supprimer ce message ?')">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?= $mid ?>">
                                    <button type="submit" class="action-btn action-btn--danger" title="Supprimer le message">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state__icon"><span class="material-symbols-outlined">inbox</span></div>
                        <p class="empty-state__text">AUCUN MESSAGE REÇU</p>
                    </div>
                    <?php endif; ?>

                    <!-- ====== Newsletter Subscribers ====== -->
                    <div class="section-header" style="margin-top:var(--sp-xl);">
                        <span class="material-symbols-outlined" style="color:var(--primary); font-size:24px;">campaign</span>
                        <h2 class="section-header__title">NEWSLETTER_ABONNÉS</h2>
                        <span class="badge badge--green"><?= $subscriberCount ?> ACTIF<?= $subscriberCount !==
 1
     ? "S"
     : "" ?></span>
                    </div>

                    <?php if (!empty($subscribers)): ?>
                    <div class="messages-list">
                        <?php foreach ($subscribers as $sub): ?>
                        <?php
                        $subEmail = htmlspecialchars($sub["email"] ?? "");
                        $subDate = date(
                            "d/m/Y H:i",
                            strtotime($sub["subscribed_at"] ?? "now"),
                        );
                        $subId = (int) ($sub["id"] ?? 0);
                        ?>
                        <div class="message-card">
                            <div>
                                <div>
                                    <span class="message-card__sender">
                                        <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">mail</span>
                                        <?= $subEmail ?>
                                    </span>
                                </div>
                                <div class="message-card__meta">
                                    <span class="message-card__date">Abonné le <?= $subDate ?></span>
                                    <span class="message-card__indicator">
                                        <span class="status-dot status-dot--active"></span>
                                        <span style="color:var(--secondary-bright); font-weight:600;">ACTIF</span>
                                    </span>
                                </div>
                            </div>
                            <div class="message-card__right">
                                <form class="inline-form" method="POST" action="admin.php" onsubmit="return confirmDelete(this, 'Supprimer cet abonné ?')">
                                    <input type="hidden" name="action" value="delete_subscriber">
                                    <input type="hidden" name="subscriber_id" value="<?= $subId ?>">
                                    <button type="submit" class="action-btn action-btn--danger" title="Supprimer l'abonné">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state__icon"><span class="material-symbols-outlined">campaign</span></div>
                        <p class="empty-state__text">AUCUN ABONNÉ NEWSLETTER</p>
                    </div>
                    <?php endif; ?>

                    <!-- ====== Newsletter Composer ====== -->
                    <div class="section-header" style="margin-top:var(--sp-xl);">
                        <span class="material-symbols-outlined" style="color:var(--primary); font-size:24px;">edit_square</span>
                        <h2 class="section-header__title">ENVOYER_NEWSLETTER</h2>
                        <span class="badge badge--orange"><?= $subscriberCount ?> DESTINATAIRE<?= $subscriberCount !==
 1
     ? "S"
     : "" ?></span>
                    </div>

                    <form id="newsletter-compose-form" style="margin-top:var(--sp-md);">
                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-family:var(--font-display); font-size:11px; letter-spacing:0.1em; color:rgba(255,255,255,0.5); margin-bottom:6px;">SUJET</label>
                            <input type="text" name="subject" id="nl-subject" required placeholder="Ex: Nouvelle mise à jour BLACK_PROTOCOL" style="width:100%; padding:10px 14px; background:var(--surface-container); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:var(--white); font-size:14px; font-family:inherit; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-family:var(--font-display); font-size:11px; letter-spacing:0.1em; color:rgba(255,255,255,0.5); margin-bottom:6px;">CONTENU</label>
                            <textarea name="content" id="nl-content" required rows="8" placeholder="Rédigez votre newsletter ici..." style="width:100%; padding:10px 14px; background:var(--surface-container); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:var(--white); font-size:14px; font-family:inherit; outline:none; resize:vertical; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <button type="button" id="nl-test-btn" style="font-family:var(--font-display); font-size:11px; letter-spacing:0.1em; padding:10px 18px; background:transparent; color:var(--primary); border:1px solid rgba(255,130,0,0.3); border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s;">
                                <span class="material-symbols-outlined" style="font-size:16px;">science</span>
                                TEST UNICAST
                            </button>
                            <button type="button" id="nl-send-btn" style="font-family:var(--font-display); font-size:11px; letter-spacing:0.1em; padding:10px 18px; background:var(--secondary); color:#00391f; border:none; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px; font-weight:600; transition:all 0.2s;">
                                <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                                ENVOYER À TOUS (<?= $subscriberCount ?>)
                            </button>
                            <div id="nl-status" style="font-family:var(--font-display); font-size:11px; color:rgba(255,255,255,0.4); margin-left:8px;"></div>
                        </div>
                    </form>

                    <!-- Newsletter result -->
                    <div id="nl-result" style="display:none; margin-top:var(--sp-md); padding:14px 18px; border-radius:8px; font-family:var(--font-display); font-size:13px; line-height:1.5;"></div>

                </div>

                <!-- ── Sidebar ── -->
                <div class="admin-sidebar">

                    <!-- Admin Profile Card -->
                    <div class="admin-profile">
                        <div class="admin-profile__header">
                            <div class="admin-profile__avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c" alt="Admin Avatar">
                            </div>
                            <div>
                                <div class="admin-profile__name"><?= htmlspecialchars(
                                    $currentUser["username"] ?? "Admin",
                                ) ?></div>
                                <div class="admin-profile__email"><?= htmlspecialchars(
                                    $currentUser["email"] ?? "",
                                ) ?></div>
                                <div class="admin-profile__role">
                                    <span class="role-badge role-badge--admin">
                                        <span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">shield</span>
                                        admin
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="admin-profile__detail">
                            <span class="admin-profile__detail-label">Statut session</span>
                            <span class="admin-profile__detail-value">
                                <span class="pulse-dot pulse-dot--green"></span>
                                SESSION_ACTIVE
                            </span>
                        </div>
                        <div class="admin-profile__detail">
                            <span class="admin-profile__detail-label">Dernière connexion</span>
                            <span class="admin-profile__detail-value" style="color:rgba(255,255,255,0.6);">
                                <?= date("d/m/Y H:i") ?>
                            </span>
                        </div>
                        <div class="admin-profile__detail">
                            <span class="admin-profile__detail-label">IP Address</span>
                            <span class="admin-profile__detail-value" style="color:rgba(255,255,255,0.6);">
                                <?= $_SERVER["REMOTE_ADDR"] ?? "N/A" ?>
                            </span>
                        </div>
                    </div>

                </div>

            </div><!-- /admin-grid -->

        </div><!-- /container -->
    </div><!-- /admin-wrapper -->

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
                <a href="index.php#home" class="nav-bottom__link" data-section="home">
                    <span class="material-symbols-outlined nav-bottom__icon">home</span>
                    <span class="nav-bottom__text">Home</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#skills" class="nav-bottom__link" data-section="skills">
                    <span class="material-symbols-outlined nav-bottom__icon">bolt</span>
                    <span class="nav-bottom__text">Skills</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#projects" class="nav-bottom__link" data-section="projects">
                    <span class="material-symbols-outlined nav-bottom__icon">grid_view</span>
                    <span class="nav-bottom__text">Projects</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#contact" class="nav-bottom__link" data-section="contact">
                    <span class="material-symbols-outlined nav-bottom__icon">alternate_email</span>
                    <span class="nav-bottom__text">Contact</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="admin.php" class="nav-bottom__link active" data-section="admin">
                    <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' 1;">admin_panel_settings</span>
                    <span class="nav-bottom__text">Admin</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- ================================
         CONFIRM DIALOG (for deletions)
         ================================ -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-dialog">
            <div class="confirm-dialog__title">
                <span class="material-symbols-outlined" style="font-size:32px; color:#ff4444; display:block; margin-bottom:var(--sp-sm);">warning</span>
                CONFIRMER LA SUPPRESSION
            </div>
            <p class="confirm-dialog__text" id="confirmText">Êtes-vous sûr ?</p>
            <div class="confirm-dialog__actions">
                <button class="btn btn--cancel" onclick="closeConfirm()">ANNULER</button>
                <button class="btn btn--danger" id="confirmBtn">SUPPRIMER</button>
            </div>
        </div>
    </div>

    <!-- ================================
         JAVASCRIPT
         ================================ -->
    <script>
        // ── Confirm Dialog System ──
        let pendingForm = null;

        function confirmDelete(formEl, message) {
            pendingForm = formEl;
            document.getElementById('confirmText').textContent = message;
            document.getElementById('confirmOverlay').classList.add('active');
            return false; // Prevent default submit
        }

        document.getElementById('confirmBtn').addEventListener('click', function() {
            if (pendingForm) {
                pendingForm.submit();
            }
            closeConfirm();
        });

        function closeConfirm() {
            document.getElementById('confirmOverlay').classList.remove('active');
            pendingForm = null;
        }

        // Close on overlay click
        document.getElementById('confirmOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeConfirm();
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeConfirm();
        });

        // ── Scroll-triggered animations ──
        const animateElements = document.querySelectorAll(
            '.stat-card, .user-card, .message-card, .admin-profile'
        );

        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateElements.forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition =
                `opacity 0.4s ${i * 0.04}s cubic-bezier(0.16,1,0.3,1),` +
                `transform 0.4s ${i * 0.04}s cubic-bezier(0.16,1,0.3,1)`;
            fadeObserver.observe(el);
        });
    </script>

    <script>
    (function() {
        const form = document.getElementById('newsletter-compose-form');
        const subjectEl = document.getElementById('nl-subject');
        const contentEl = document.getElementById('nl-content');
        const testBtn = document.getElementById('nl-test-btn');
        const sendBtn = document.getElementById('nl-send-btn');
        const statusEl = document.getElementById('nl-status');
        const resultEl = document.getElementById('nl-result');

        function showResult(success, html) {
            resultEl.style.display = 'block';
            resultEl.style.background = success ? 'rgba(0,158,96,0.08)' : 'rgba(255,107,107,0.08)';
            resultEl.style.border = success ? '1px solid rgba(0,158,96,0.2)' : '1px solid rgba(255,107,107,0.2)';
            resultEl.style.color = success ? 'var(--secondary-bright)' : '#ff6b6b';
            resultEl.innerHTML = html;
        }

        function setLoading(btn) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px; animation:spin 1s linear infinite; vertical-align:middle;">progress_activity</span> Transmission en cours...';
        }

        function resetBtn(btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            statusEl.innerHTML = '';
        }

        // ── Test ──
        testBtn.addEventListener('click', function() {
            if (!subjectEl.value.trim() || !contentEl.value.trim()) {
                showResult(false, '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">warning</span>Sujet et contenu requis.');
                return;
            }
            setLoading(testBtn);
            const fd = new FormData();
            fd.append('subject', subjectEl.value);
            fd.append('content', contentEl.value);
            fd.append('test_only', '1');
            fd.append('test_email', 'vianson50@gmail.com');

            fetch('newsletter_send.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                let html = '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">' + (data.success ? 'check_circle' : 'error') + '</span>' + data.message;
                showResult(data.success, html);
            })
            .catch(() => showResult(false, '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">wifi_off</span>Erreur réseau.'))
            .finally(() => resetBtn(testBtn));
        });

        // ── Send All ──
        sendBtn.addEventListener('click', function() {
            if (!subjectEl.value.trim() || !contentEl.value.trim()) {
                showResult(false, '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">warning</span>Sujet et contenu requis.');
                return;
            }
            if (!confirm('Envoyer cette newsletter à TOUS les abonnés actifs ?')) return;
            setLoading(sendBtn);
            const fd = new FormData();
            fd.append('subject', subjectEl.value);
            fd.append('content', contentEl.value);

            fetch('newsletter_send.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                let html = '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">' + (data.success ? 'check_circle' : 'error') + '</span>' + data.message;
                if (data.sent) html += '<br><span style="color:rgba(255,255,255,0.4); font-size:11px;">' + data.sent + '/' + data.total + ' envoyés</span>';
                if (data.errors && data.errors.length) {
                    html += '<br><span style="color:#ff6b6b; font-size:11px;">Erreurs: ' + data.errors.join(', ') + '</span>';
                }
                showResult(data.success, html);
            })
            .catch(() => showResult(false, '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">wifi_off</span>Erreur réseau.'))
            .finally(() => resetBtn(sendBtn));
        });
    })();
    </script>

</body>
</html>
