<?php
/**
 * BLACK_PROTOCOL — Login Terminal
 * Design: Minimalist Cyberpunk × Professional Brutalism
 */

require_once "includes/auth.php";
require_once "includes/seo.php";

// Redirect if already logged in and user data is valid
if (isLoggedIn() && getCurrentUser()) {
    if (isAdmin()) {
        header("Location: admin.php");
    } else {
        header("Location: profile.php");
    }
    exit();
}

$errors = [];

// Check for registration success message
$registered = isset($_GET["registered"]);
$loggedOut = isset($_GET["logout"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validate fields non-empty
    if (empty($email) || empty($password)) {
        $errors[] = "Tous les champs sont requis.";
    } else {
        // Attempt login
        $success = login($email, $password);
        if ($success) {
            // Redirect based on role stored in session
            if (isAdmin()) {
                header("Location: admin.php");
            } else {
                header("Location: profile.php");
            }
            exit();
        } else {
            $errors[] = "Identifiants incorrects.";
        }
    }
}

$year = date("Y");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Sécurisée | BLACK_PROTOCOL</title>
    <?= renderMeta(
        "Connexion Sécurisée — Terminal d'Authentification",
        "Accédez à votre espace BLACK_PROTOCOL. Terminal d'authentification sécurisé pour développeurs et créatifs. Gérez vos projets, newsletter et paramètres cybers.",
        "website",
    ) ?>

    <!-- Fonts: Space Grotesk + Inter + Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Material Symbols config -->
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    <!-- Auth-specific Styles -->
    <style>
        /* ========================================
           AUTH PAGE LAYOUT
           ======================================== */
        .auth-page {
            min-height: 100vh;
            background: var(--void);
            display: flex;
            flex-direction: column;
            padding-top: 64px;
        }

        .auth-page__body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--sp-xl) var(--sp-lg);
        }

        /* ========================================
           AUTH CARD (Terminal Window)
           ======================================== */
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            border-radius: var(--radius);
        }

        .auth-card__scanline {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%),
                linear-gradient(
                    90deg,
                    rgba(255, 0, 0, 0.06),
                    rgba(0, 255, 0, 0.02),
                    rgba(0, 0, 255, 0.06)
                );
            background-size:
                100% 2px,
                3px 100%;
            opacity: 0.1;
            pointer-events: none;
            z-index: 1;
        }

        .auth-card__bar {
            background: var(--void);
            padding: var(--sp-sm) var(--sp-md);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .auth-card__dots {
            display: flex;
            gap: 8px;
        }

        .auth-card__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .auth-card__dot--red {
            background: rgba(255, 180, 171, 0.4);
        }
        .auth-card__dot--yellow {
            background: rgba(255, 183, 133, 0.4);
        }
        .auth-card__dot--green {
            background: rgba(97, 221, 152, 0.4);
        }

        .auth-card__bar-title {
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.2em;
            color: rgba(255, 255, 255, 0.4);
        }

        /* ========================================
           AUTH FORM CONTENT
           ======================================== */
        .auth-card__content {
            padding: var(--sp-lg);
            position: relative;
            z-index: 10;
        }

        .auth-card__header {
            margin-bottom: var(--sp-lg);
        }

        .auth-card__title {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--on-surface);
            margin-bottom: var(--sp-xs);
        }

        .auth-card__subtitle {
            font-family: var(--font-display);
            font-size: 12px;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.4);
        }

        .auth-card__form {
            display: flex;
            flex-direction: column;
            gap: var(--sp-lg);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--sp-xs);
        }

        .form-group__label {
            display: block;
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--on-surface-variant);
        }

        .form-group__input {
            width: 100%;
            padding: var(--sp-sm) var(--sp-md);
            font-family: var(--font-body);
            font-size: 16px;
            color: var(--on-surface);
            background: var(--void);
            border: 1px solid rgba(255, 255, 255, 0.1);
            outline: none;
            transition:
                border-color 0.3s,
                box-shadow 0.3s;
            border-radius: 2px;
            box-sizing: border-box;
        }

        .form-group__input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 12px rgba(255, 130, 0, 0.1);
        }

        .form-group__input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* ========================================
           AUTH SUBMIT BUTTON
           ======================================== */
        .auth-submit {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-md);
            padding: var(--sp-md) var(--sp-xl);
            background: var(--primary);
            color: var(--void);
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(255, 130, 0, 0.15);
        }

        .auth-submit:hover {
            box-shadow: 0 0 25px rgba(255, 130, 0, 0.35);
        }

        .auth-submit:active {
            transform: scale(0.98);
        }

        .auth-submit .material-symbols-outlined {
            font-size: 18px;
            transition: transform 0.3s;
        }

        .auth-submit:hover .material-symbols-outlined {
            transform: translateX(4px);
        }

        /* ========================================
           AUTH FOOTER LINK
           ======================================== */
        .auth-card__footer {
            padding: var(--sp-md) var(--sp-lg);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 10;
        }

        .auth-footer__link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-sm);
            font-family: var(--font-body);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            transition: color 0.3s;
        }

        .auth-footer__link:hover {
            color: var(--on-surface);
        }

        .auth-footer__link-highlight {
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--primary);
            transition: text-shadow 0.3s;
        }

        .auth-footer__link:hover .auth-footer__link-highlight {
            text-shadow: 0 0 8px rgba(255, 130, 0, 0.3);
        }

        /* ========================================
           ERROR MESSAGES
           ======================================== */
        .auth-errors {
            margin-bottom: var(--sp-lg);
            display: flex;
            flex-direction: column;
            gap: var(--sp-sm);
        }

        .auth-error {
            padding: var(--sp-sm) var(--sp-md);
            background: rgba(255, 180, 171, 0.05);
            border-left: 3px solid var(--error);
            border-radius: 0;
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--error);
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
        }

        .auth-error .material-symbols-outlined {
            font-size: 18px;
            color: var(--error);
            flex-shrink: 0;
        }

        /* ========================================
           SUCCESS MESSAGES
           ======================================== */
        .auth-success {
            margin-bottom: var(--sp-lg);
            padding: var(--sp-sm) var(--sp-md);
            background: rgba(97, 221, 152, 0.05);
            border-left: 3px solid var(--secondary-bright);
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--secondary-bright);
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
        }

        .auth-success .material-symbols-outlined {
            font-size: 18px;
            color: var(--secondary-bright);
            flex-shrink: 0;
        }

        /* ========================================
           LOCATION BAR
           ======================================== */
        .location-bar {
            margin-top: var(--sp-lg);
            display: flex;
            flex-direction: column;
            gap: var(--sp-lg);
            background: rgba(22, 22, 24, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: var(--sp-md);
            border-radius: var(--radius);
            max-width: 460px;
            width: 100%;
        }

        .location-bar__coord {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
        }

        .location-bar__coord .material-symbols-outlined {
            color: var(--secondary-bright);
        }

        .location-bar__coord span:last-child {
            font-family: var(--font-display);
            font-size: 14px;
            letter-spacing: 0.05em;
            color: var(--on-surface-variant);
        }

        .location-bar__time {
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.3em;
            color: rgba(255, 255, 255, 0.2);
        }

        /* ========================================
           ANIMATION
           ======================================== */
        .auth-card {
            opacity: 0;
            transform: translateY(24px);
            animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
        }

        .location-bar {
            opacity: 0;
            transform: translateY(24px);
            animation: authFadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.25s forwards;
        }

        @keyframes authFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                    RETOUR
                </a>
                <a href="register.php" class="top-header__btn top-header__btn--register">
                    <span class="material-symbols-outlined" style="font-size:16px;">person_add</span>
                    INSCRIPTION
                </a>
            </div>
        </div>
    </header>

    <!-- ================================
         AUTH PAGE BODY
         ================================ -->
    <main class="auth-page">
        <div class="auth-page__body">

            <div class="auth-card">
                <div class="auth-card__scanline"></div>

                <!-- Terminal Title Bar -->
                <div class="auth-card__bar">
                    <div class="auth-card__dots">
                        <span class="auth-card__dot auth-card__dot--red"></span>
                        <span class="auth-card__dot auth-card__dot--yellow"></span>
                        <span class="auth-card__dot auth-card__dot--green"></span>
                    </div>
                    <span class="auth-card__bar-title">SECURE_AUTH_TERMINAL</span>
                </div>

                <!-- Card Content -->
                <div class="auth-card__content">
                    <div class="auth-card__header">
                        <h1 class="auth-card__title">SE_CONNECTER</h1>
                        <p class="auth-card__subtitle">// AUTHENTIFICATION SÉCURISÉE REQUISE</p>
                    </div>

                    <!-- Success Message (from registration) -->
                    <?php if ($registered): ?>
                        <div class="auth-success">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Compte créé avec succès !</span>
                        </div>
                    <?php endif; ?>

                    <!-- Logout Message -->
                    <?php if ($loggedOut): ?>
                        <div class="auth-success">
                            <span class="material-symbols-outlined">logout</span>
                            <span>Déconnecté avec succès !</span>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="auth-errors">
                            <?php foreach ($errors as $error): ?>
                                <div class="auth-error">
                                    <span class="material-symbols-outlined">error</span>
                                    <span><?= htmlspecialchars($error) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form class="auth-card__form" method="POST" action="login.php" novalidate>
                        <div class="form-group">
                            <label class="form-group__label" for="email">EMAIL</label>
                            <input class="form-group__input" type="email" id="email" name="email" placeholder="utilisateur@domaine.com" value="<?= htmlspecialchars(
                                $_POST["email"] ?? "",
                            ) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-group__label" for="password">MOT_DE_PASSE</label>
                            <input class="form-group__input" type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                        </div>

                        <button type="submit" class="auth-submit">
                            <span>SE_CONNECTER</span>
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                </div>

                <!-- Footer Link -->
                <div class="auth-card__footer">
                    <a href="register.php" class="auth-footer__link">
                        <span>Pas de compte ?</span>
                        <span class="auth-footer__link-highlight">CRÉER_UN_COMPTE →</span>
                    </a>
                </div>
            </div>

            <!-- Location Metadata Bar -->
            <div class="location-bar">
                <div class="location-bar__coord">
                    <span class="material-symbols-outlined">location_on</span>
                    <span>ABIDJAN, CÔTE D'IVOIRE // SECURE_AUTH_TERMINAL</span>
                </div>
            </div>

        </div>
    </main>

</body>
</html>
