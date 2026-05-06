<?php
/**
 * BLACK_PROTOCOL — Ivorian Cyber-Professional Portfolio
 * Design System: Minimalist Cyberpunk × Professional Brutalism
 * Colors: Côte d'Ivoire national colors reimagined for digital context
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
require_once __DIR__ . "/includes/articles.php";
require_once __DIR__ . "/includes/comments.php";

$lang = "fr";
$charset = "UTF-8";
$seoTitle = "Développeur Full-Stack & Ethical Hacker — Portfolio Cyberpunk";
$seoDesc =
    "Explorez l'univers BLACK_PROTOCOL : portfolio cyberpunk d'un Développeur Full-Stack, Ethical Hacker & Designer basé à Abidjan. Cybersécurité offensive, architectures cloud, game dev et créations numériques d'avant-garde.";
$title = $seoTitle . " | BLACK_PROTOCOL";
$desc = $seoDesc;
$seoKeywords = [
    "portfolio développeur",
    "ethical hacker Abidjan",
    'cybersécurité Côte d\'Ivoire',
    "full-stack developer Africa",
];
$year = date("Y");

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
// Si getCurrentUser() retourne null (session corrompue), on considère déconnecté
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

// Helpers compatibles sans mbstring
if (!function_exists("safeSubstr")) {
    function safeSubstr(string $str, int $start, ?int $length = null): string
    {
        return function_exists("mb_substr")
            ? mb_substr($str, $start, $length)
            : substr($str, $start, $length);
    }
}
if (!function_exists("safeStrlen")) {
    function safeStrlen(string $str): int
    {
        return function_exists("mb_strlen") ? mb_strlen($str) : strlen($str);
    }
}

// Récupérer les articles publiés pour le blog feed
$publishedArticles = getPublishedArticles(6);

// ── Suppression d'article (admin) ──
$articleDeleted = false;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_article"])) {
    if ($isLoggedIn && isAdmin()) {
        $delId = (int) ($_POST["delete_article"] ?? 0);
        if ($delId > 0) {
            $articleDeleted = deleteAnyArticle($delId);
            // Recharger la liste
            $publishedArticles = getPublishedArticles(6);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="<?= $charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <?= renderMeta($seoTitle, $seoDesc, "website", null, $seoKeywords) ?>

    <!-- Preconnect for external resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts: load non-blocking with font-display swap + reduced weight set -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap">

    <!-- Material Symbols: load async -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"></noscript>

    <!-- Critical CSS: inline the most important styles -->
    <style>
        :root{--void:#0a0a0b;--surface:#161618;--primary:#ff8200;--primary-dim:#ffb785;--primary-glow:rgba(255,130,0,0.35);--secondary:#009e60;--secondary-bright:#61dd98;--secondary-glow:rgba(0,158,96,0.3);--white:#ffffff;--on-surface:#f4ded2;--on-surface-variant:#dec1af;--outline:#a68b7b;--font-display:"Space Grotesk",sans-serif;--font-body:"Inter",sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font-body);background:var(--void);color:var(--on-surface);overflow-x:hidden;padding-bottom:80px;padding-top:64px}
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
    </style>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ================================
         TOP HEADER BAR
         ================================ -->
    <header class="top-header">
        <div class="top-header__inner">
            <div class="top-header__brand">
                <div class="top-header__avatar">
                    <img src="<?= htmlspecialchars(
                        $isLoggedIn && $currentUser && $currentUser["avatar"]
                            ? $currentUser["avatar"]
                            : "https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c",
                    ) ?>" loading="eager" decoding="async" alt="Profile">
                </div>
                <span class="top-header__name">BLACK_PROTOCOL</span>
            </div>
            <div class="top-header__actions">
                <?php if ($isLoggedIn && $currentUser): ?>
                    <!-- Utilisateur connecté -->
                    <a href="profile.php" class="top-header__user">
                        <div class="top-header__user-avatar">
                            <?php if (
                                $isLoggedIn &&
                                $currentUser &&
                                $currentUser["avatar"]
                            ): ?>
                            <img src="<?= htmlspecialchars(
                                $currentUser["avatar"],
                            ) ?>" loading="eager" decoding="async" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                            <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1;">person</span>
                            <?php endif; ?>
                        </div>
                        <span class="top-header__user-name"><?= htmlspecialchars(
                            $currentUser["username"],
                        ) ?></span>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="top-header__btn top-header__btn--admin">
                            <span class="material-symbols-outlined" style="font-size:16px;">shield</span>
                            ADMIN
                        </a>
                        <a href="profile.php" class="top-header__btn top-header__btn--login">
                            <span class="material-symbols-outlined" style="font-size:16px;">person</span>
                            PROFIL
                        </a>
                    <?php else: ?>
                        <a href="profile.php" class="top-header__btn top-header__btn--login">
                            <span class="material-symbols-outlined" style="font-size:16px;">person</span>
                            PROFIL
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="top-header__btn top-header__btn--logout">
                        <span class="material-symbols-outlined" style="font-size:16px;">logout</span>
                        DÉCO.
                    </a>
                <?php else: ?>
                    <!-- Visiteur -->
                    <a href="login.php" class="top-header__btn top-header__btn--login">
                        <span class="material-symbols-outlined" style="font-size:16px;">login</span>
                        CONNEXION
                    </a>
                    <a href="register.php" class="top-header__btn top-header__btn--register">
                        <span class="material-symbols-outlined" style="font-size:16px;">person_add</span>
                        INSCRIPTION
                    </a>
                <?php endif; ?>
                    <a href="agenda.php" class="top-header__btn top-header__btn--login" style="border-color:rgba(0,158,96,0.4);">
                        <span class="material-symbols-outlined" style="font-size:16px;">calendar_month</span>
                        AGENDA
                    </a>
                    <a href="hardware.php" class="top-header__btn top-header__btn--login" style="border-color:rgba(56,189,248,0.4);">
                        <span class="material-symbols-outlined" style="font-size:16px;">memory</span>
                        HARDWARE
                    </a>
                    <a href="article_structurer.php" class="top-header__btn top-header__btn--login" style="border-color:rgba(255,130,0,0.3);">
                        <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
                        ARTICLE
                    </a>
            </div>
        </div>
    </header>

    <!-- ================================
         RSS TICKER BAR
         ================================ -->
    <div class="rss-ticker-bar">
        <div class="rss-ticker-bar__label">
            <span class="material-symbols-outlined" style="font-size:14px;">radar</span>
            <span>LIVE</span>
            <span class="live-indicator">
                <span class="pulse-dot pulse-dot--green" style="width:6px;height:6px;"></span>
                <span id="refresh-countdown" style="font-size:9px; color:rgba(255,255,255,0.4); min-width:22px; text-align:center;">60s</span>
            </span>
        </div>
        <div class="rss-ticker-bar__content">
            <rssapp-ticker id="TVOvmVGK5J7yEQY4"></rssapp-ticker><script data-src="https://widget.rss.app/v1/ticker.js" type="text/javascript" async class="rss-lazy"></script>
        </div>
        <div id="refresh-bar" style="position:absolute;bottom:0;left:0;height:1px;background:var(--primary);width:0%;transition:width 1s linear;"></div>
    </div>

    <!-- ================================
         SECTION 1 — HERO / ACCUEIL
         ================================ -->
    <section class="hero" id="home">
        <!-- Background Decorative Elements -->
        <div class="hero__decor">
            <div class="hero__decor-square"></div>
            <div class="hero__decor-circle"></div>
            <div class="hero__decor-scanline"></div>
        </div>

        <!-- Centered Hero Content -->
        <div class="hero__content">
            <!-- Profile Image with Gradient Border -->
            <div class="hero__profile">
                <div class="hero__profile-glow"></div>
                <div class="hero__profile-frame">
                    <div class="hero__profile-inner">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4b3gbtVkhHvi8Hn6IJ4xFJ72NQKz74gEMW8eAF2AuE19vjailbpVCojG-0tUymwFkXGLRcrP8Gn7SmFpN31KIMtUbe3tstVdrTpmSBexT6nXsIGs69pxKwB88IkFtBFEQcnrniMyVgNHgZxgrC10msciQLtfYPKpkINreY3t-GDhZJpZwRglyOLujHrKdaIh4TAQG5C_OHuh7H_TsonZ2b1iwiprz6HNmCr1uBu1m2wLq9H6EDD3oHCnq40SYW2Z-4RYrVrbT1-w" loading="eager" decoding="async" alt="Professional Profile">
                    </div>
                </div>
                <div class="hero__verified">
                    <span class="material-symbols-outlined">verified</span>
                </div>
            </div>

            <!-- Headlines -->
            <div class="hero__headline">
                <h1>
                    DÉVELOPPEUR <span class="highlight-orange">FULL-STACK</span> &amp; <span class="highlight-green">ETHICAL HACKER</span>
                </h1>
                <p>
                    Passionné par le <span class="italic">design</span> et le <span class="bold">gaming</span>. Architecte de solutions sécurisées au cœur de l'écosystème numérique.
                </p>
            </div>

            <!-- CTA Button -->
            <a href="#projects" class="hero__cta">
                VOIR MES PROJETS
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>

        <!-- Bento Stats Grid -->
        <div class="bento-stats">
            <!-- Tech Stack Card -->
            <div class="bento-card full-width">
                <div class="bento-card__header">
                    <span class="label-caps text-white-40">TECH_STACK</span>
                    <span class="code-sm text-secondary" style="font-size:12px;">SYSTEMS_NOMINAL</span>
                </div>

                <!-- Frontend -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">code</span>
                        FRONTEND
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.typescriptlang.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">TYPESCRIPT</a>
                        <a href="https://react.dev/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">REACT</a>
                        <a href="https://nextjs.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">NEXT.JS</a>
                        <a href="https://tailwindcss.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">TAILWIND</a>
                        <a href="https://vuejs.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">VUE.JS</a>
                    </div>
                </div>

                <!-- Backend -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">terminal</span>
                        BACKEND
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.php.net/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">PHP</a>
                        <a href="https://nodejs.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">NODE.JS</a>
                        <a href="https://www.python.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">PYTHON</a>
                        <a href="https://www.rust-lang.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">RUST</a>
                        <a href="https://expressjs.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">EXPRESS</a>
                    </div>
                </div>

                <!-- DevOps & Cloud -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">cloud</span>
                        DEVOPS
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.docker.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">DOCKER</a>
                        <a href="https://kubernetes.io/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">KUBERNETES</a>
                        <a href="https://aws.amazon.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">AWS</a>
                        <a href="https://www.terraform.io/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">TERRAFORM</a>
                        <a href="https://github.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--neutral">GIT</a>
                        <a href="https://www.linux.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--neutral">LINUX</a>
                    </div>
                </div>

                <!-- Security -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">shield</span>
                        SECURITY
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.kali.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">KALI LINUX</a>
                        <a href="https://www.metasploit.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">METASPLOIT</a>
                        <a href="https://www.wireshark.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">WIRESHARK</a>
                        <a href="https://www.burpsuite.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">BURP SUITE</a>
                        <a href="https://nmap.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--neutral">NMAP</a>
                    </div>
                </div>

                <!-- Database -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">database</span>
                        DATABASE
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.mysql.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">MYSQL</a>
                        <a href="https://www.postgresql.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">POSTGRESQL</a>
                        <a href="https://www.mongodb.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">MONGODB</a>
                        <a href="https://redis.io/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">REDIS</a>
                    </div>
                </div>

                <!-- Game Dev -->
                <div class="bento-card__group">
                    <span class="bento-card__group-label">
                        <span class="material-symbols-outlined" style="font-size:12px;">sports_esports</span>
                        GAME_DEV
                    </span>
                    <div class="bento-card__tags">
                        <a href="https://www.unrealengine.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--orange">UNREAL 5</a>
                        <a href="https://unity.com/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--green">UNITY</a>
                        <a href="https://godotengine.org/" target="_blank" rel="noopener" class="bento-tag bento-tag--link bento-tag--tertiary">GODOT</a>
                    </div>
                </div>
            </div>

            <!-- Experience Card -->
            <div class="stat-mini">
                <div class="stat-mini__value stat-mini__value--orange h2">05+</div>
                <div class="stat-mini__label">ANNÉES D'EXPÉRIENCE</div>
            </div>

            <!-- Projects Card -->
            <div class="stat-mini stat-mini--green">
                <div class="stat-mini__value stat-mini__value--green h2">42</div>
                <div class="stat-mini__label">PROJETS DÉPLOYÉS</div>
            </div>
        </div>

        <!-- Featured Project Card -->
        <a href="https://bit.ly/429h10k" target="_blank" rel="noopener noreferrer" class="featured-card featured-card--link">
            <div class="featured-card__inner">
                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuABBW_K8WCMfXGyZxOO97Hi45WaDy87J_qH8oIx_Kmlk4PkynVBOygDN1xVlhuFHfBF-199rj8YE2A5ywyBCTyKvpLDWfjoZDaR6_9nGYpFNTz-J2WwrT6o3ROFTon-Yz88P30vIHng3ATkVWRxUDxUfQgM5tgzpnidsGd3UESekrI0Om5t7XvqnTqqSrM2vQOGmhTqJHZHL1-G6JY0oibKEjIVbk8x0Rg5E3DfYCeXhnLBhO8khsO9I-eIyENHB5wQQOCgLhHyNiE" loading="lazy" decoding="async" alt="Project Preview">
                <div class="featured-card__gradient"></div>
                <div class="featured-card__overlay">
                    <span class="material-symbols-outlined">open_in_new</span>
                </div>
                <div class="featured-card__content">
                    <span class="featured-card__badge label-caps">FEATURED_WORK</span>
                    <div class="featured-card__title">SÉCURITÉ_RÉSEAU_V3</div>
                    <div class="featured-card__meta">
                        <span style="font-size:14px;">Cybersecurity Analysis Tool</span>
                        <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                    </div>
                </div>
            </div>
        </a>
    </section>

    <!-- ================================
         SECTION 2 — SKILLS / COMPÉTENCES
         ================================ -->
    <section class="skills-section" id="skills">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-label">
                    <span class="section-label__dot"></span>
                    System Diagnostics / Capabilities
                </div>
                <h1 class="h1 text-on-surface" style="text-transform:uppercase; letter-spacing:-0.02em;">
                    Tech Stack <span class="text-primary">OS_v2.0</span>
                </h1>
            </div>

            <!-- Bento Grid Layout -->
            <div class="skills-bento">

                <!-- ================================
                     CYBER SECURITY (7 cols)
                     ================================ -->
                <div class="skills-bento__card skills-bento__card--security">
                    <span class="material-symbols-outlined skills-bento__card-bg-icon">security</span>
                    <div class="skills-bento__card-header">
                        <div>
                            <h2 class="skills-bento__card-title">Cyber Security</h2>
                            <p class="skills-bento__card-desc">Tests de pénétration, analyse de vulnérabilités et sécurité offensive sur infrastructures distribuées.</p>
                        </div>
                        <span class="skills-bento__badge skills-bento__badge--orange">Focus Area</span>
                    </div>
                    <div style="position:relative; z-index:10;">
                        <!-- Kali Linux -->
                        <div class="progress-bar">
                            <div class="progress-bar__header">
                                <a href="https://www.kali.org/" target="_blank" rel="noopener" class="progress-bar__label progress-bar__label--link">Kali Linux</a>
                                <span class="progress-bar__value">95%</span>
                            </div>
                            <div class="progress-bar__track">
                                <div class="progress-bar__fill" style="width:95%;"></div>
                            </div>
                        </div>
                        <!-- Metasploit -->
                        <div class="progress-bar">
                            <div class="progress-bar__header">
                                <a href="https://www.metasploit.com/" target="_blank" rel="noopener" class="progress-bar__label progress-bar__label--link">Metasploit Framework</a>
                                <span class="progress-bar__value">88%</span>
                            </div>
                            <div class="progress-bar__track">
                                <div class="progress-bar__fill" style="width:88%;"></div>
                            </div>
                        </div>
                        <!-- Nmap -->
                        <div class="progress-bar">
                            <div class="progress-bar__header">
                                <a href="https://nmap.org/" target="_blank" rel="noopener" class="progress-bar__label progress-bar__label--link">Nmap Scanning</a>
                                <span class="progress-bar__value">94%</span>
                            </div>
                            <div class="progress-bar__track">
                                <div class="progress-bar__fill" style="width:94%;"></div>
                            </div>
                        </div>
                        <!-- Wireshark -->
                        <div class="progress-bar">
                            <div class="progress-bar__header">
                                <a href="https://www.wireshark.org/" target="_blank" rel="noopener" class="progress-bar__label progress-bar__label--link">Wireshark</a>
                                <span class="progress-bar__value">90%</span>
                            </div>
                            <div class="progress-bar__track">
                                <div class="progress-bar__fill" style="width:90%;"></div>
                            </div>
                        </div>
                        <!-- Tags -->
                        <div class="tags-row">
                            <a href="https://www.burpsuite.com/" target="_blank" rel="noopener" class="tag-neutral tag-neutral--link">BURP SUITE</a>
                            <a href="https://www.owasp.org/" target="_blank" rel="noopener" class="tag-neutral tag-neutral--link">OWASP</a>
                            <span class="tag-neutral">REVERSE_ENG</span>
                        </div>
                    </div>
                </div>

                <!-- ================================
                     CREATIVE DESIGN (5 cols)
                     ================================ -->
                <div class="skills-bento__card skills-bento__card--creative">
                    <div class="skills-bento__card-header">
                        <div>
                            <h2 class="skills-bento__card-title">Creative</h2>
                            <p class="skills-bento__card-desc">Communication visuelle, UI/UX design &amp; 3D modeling.</p>
                        </div>
                        <span class="skills-bento__badge skills-bento__badge--green">Mastery</span>
                    </div>
                    <div class="creative-tools">
                        <!-- Figma -->
                        <a href="https://www.figma.com/" target="_blank" rel="noopener" class="creative-tool creative-tool--link">
                            <span class="material-symbols-outlined creative-tool__icon" style="font-variation-settings:'FILL' 1;">pentagon</span>
                            <span class="creative-tool__name">FIGMA</span>
                            <div class="creative-tool__dots">
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                            </div>
                        </a>
                        <!-- Photoshop -->
                        <a href="https://www.adobe.com/products/photoshop.html" target="_blank" rel="noopener" class="creative-tool creative-tool--link">
                            <span class="material-symbols-outlined creative-tool__icon" style="font-variation-settings:'FILL' 1;">brush</span>
                            <span class="creative-tool__name">PHOTOSHOP</span>
                            <div class="creative-tool__dots">
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                            </div>
                        </a>
                        <!-- Blender -->
                        <a href="https://www.blender.org/" target="_blank" rel="noopener" class="creative-tool creative-tool--link">
                            <span class="material-symbols-outlined creative-tool__icon" style="font-variation-settings:'FILL' 1;">deployed_code</span>
                            <span class="creative-tool__name">BLENDER</span>
                            <div class="creative-tool__dots">
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                            </div>
                        </a>
                        <!-- After Effects -->
                        <a href="https://www.adobe.com/products/aftereffects.html" target="_blank" rel="noopener" class="creative-tool creative-tool--link">
                            <span class="material-symbols-outlined creative-tool__icon" style="font-variation-settings:'FILL' 1;">movie</span>
                            <span class="creative-tool__name">AFTER EFFECTS</span>
                            <div class="creative-tool__dots">
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                                <span class="creative-tool__dot creative-tool__dot--empty"></span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- ================================
                     GAMING / GAME DEV (4 cols)
                     ================================ -->
                <div class="skills-bento__card skills-bento__card--gaming">
                    <div style="position:relative; z-index:10;">
                        <div style="display:flex; align-items:center; gap:var(--sp-sm); margin-bottom:var(--sp-md); color:rgba(255,255,255,0.6);">
                            <span class="material-symbols-outlined" style="font-size:16px;">sports_esports</span>
                            <span class="label-caps" style="font-size:10px; letter-spacing:0.2em;">Game Engine Architecture</span>
                        </div>
                        <h2 class="skills-bento__card-title" style="margin-bottom:var(--sp-md);">Gaming</h2>
                        <div class="gaming-highlight">
                            <a href="https://www.unrealengine.com/" target="_blank" rel="noopener" class="gaming-highlight__title code-sm gaming-highlight__title--link">Unreal Engine 5</a>
                            <p class="gaming-highlight__desc">C++ Visual Scripting, Niagara Particles, Lumen Global Illumination.</p>
                        </div>
                        <div class="gaming-highlight" style="margin-top:var(--sp-sm);">
                            <a href="https://unity.com/" target="_blank" rel="noopener" class="gaming-highlight__title code-sm gaming-highlight__title--link">Unity</a>
                            <p class="gaming-highlight__desc">C# Scripting, Shader Graph, 2D &amp; 3D Rendering Pipeline.</p>
                        </div>
                        <div class="tags-row" style="margin-top:var(--sp-sm);">
                            <a href="https://godotengine.org/" target="_blank" rel="noopener" class="tag-neutral tag-neutral--link">GODOT</a>
                            <span class="tag-neutral">SHADERS</span>
                            <span class="tag-neutral">PROC_GEN</span>
                        </div>
                    </div>
                </div>

                <!-- ================================
                     FULL-STACK ENGINEERING (8 cols)
                     ================================ -->
                <div class="skills-bento__card skills-bento__card--fullstack">
                    <h2 class="skills-bento__card-title" style="margin-bottom:var(--sp-lg);">Full-Stack Engineering</h2>
                    <div class="fullstack-grid">
                        <div style="display:flex; flex-direction:column; gap:var(--sp-md);">
                            <!-- Frontend -->
                            <a href="https://react.dev/" target="_blank" rel="noopener" class="fullstack-item fullstack-item--link">
                                <div class="fullstack-item__icon-box fullstack-item__icon-box--green">
                                    <span class="material-symbols-outlined">code</span>
                                </div>
                                <div>
                                    <div class="fullstack-item__label">Frontend Stack</div>
                                    <div class="fullstack-item__detail">TypeScript, React, Next.js, Tailwind CSS, Vue.js</div>
                                </div>
                            </a>
                            <!-- Backend -->
                            <a href="https://nodejs.org/" target="_blank" rel="noopener" class="fullstack-item fullstack-item--link">
                                <div class="fullstack-item__icon-box fullstack-item__icon-box--orange">
                                    <span class="material-symbols-outlined">terminal</span>
                                </div>
                                <div>
                                    <div class="fullstack-item__label">Backend Systems</div>
                                    <div class="fullstack-item__detail">Node.js, PHP, Python, Rust, Express</div>
                                </div>
                            </a>
                            <!-- Database -->
                            <a href="https://www.mongodb.com/" target="_blank" rel="noopener" class="fullstack-item fullstack-item--link">
                                <div class="fullstack-item__icon-box fullstack-item__icon-box--green">
                                    <span class="material-symbols-outlined">database</span>
                                </div>
                                <div>
                                    <div class="fullstack-item__label">Database &amp; Storage</div>
                                    <div class="fullstack-item__detail">MySQL, PostgreSQL, MongoDB, Redis</div>
                                </div>
                            </a>
                            <!-- DevOps -->
                            <a href="https://www.docker.com/" target="_blank" rel="noopener" class="fullstack-item fullstack-item--link">
                                <div class="fullstack-item__icon-box fullstack-item__icon-box--orange">
                                    <span class="material-symbols-outlined">cloud</span>
                                </div>
                                <div>
                                    <div class="fullstack-item__label">DevOps &amp; Cloud</div>
                                    <div class="fullstack-item__detail">Docker, Kubernetes, AWS, Terraform, Linux</div>
                                </div>
                            </a>
                        </div>
                        <!-- Efficiency Chart -->
                        <div class="efficiency-chart" id="efficiencyChart">
                            <div class="efficiency-chart__header">
                                <span class="efficiency-chart__label">Efficiency Cluster</span>
                                <span class="material-symbols-outlined text-secondary" style="font-size:14px; animation:spin-slow 3s linear infinite;">sync</span>
                            </div>
                            <div class="efficiency-chart__bars">
                                <div class="efficiency-chart__bar-wrap" data-label="TypeScript" data-pct="95">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--green-glow" data-height="95"></div>
                                    <span class="efficiency-chart__pct">95%</span>
                                </div>
                                <div class="efficiency-chart__bar-wrap" data-label="Node.js" data-pct="78">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--green-dim" data-height="78"></div>
                                    <span class="efficiency-chart__pct">78%</span>
                                </div>
                                <div class="efficiency-chart__bar-wrap" data-label="Python" data-pct="92">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--green-glow" data-height="92"></div>
                                    <span class="efficiency-chart__pct">92%</span>
                                </div>
                                <div class="efficiency-chart__bar-wrap" data-label="Rust" data-pct="85">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--orange" data-height="85"></div>
                                    <span class="efficiency-chart__pct">85%</span>
                                </div>
                                <div class="efficiency-chart__bar-wrap" data-label="PHP" data-pct="88">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--green-dim" data-height="88"></div>
                                    <span class="efficiency-chart__pct">88%</span>
                                </div>
                                <div class="efficiency-chart__bar-wrap" data-label="Docker" data-pct="96">
                                    <div class="efficiency-chart__bar efficiency-chart__bar--green-glow" data-height="96"></div>
                                    <span class="efficiency-chart__pct">96%</span>
                                </div>
                            </div>
                            <div class="efficiency-chart__footer">
                                <span title="TypeScript">TS</span><span title="Node.js">ND</span><span title="Python">PY</span><span title="Rust">RS</span><span title="PHP">PH</span><span title="Docker">DO</span>
                            </div>
                            <div class="efficiency-chart__tooltip" id="chartTooltip"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 3 — PROJECTS / PROJETS
         ================================ -->
    <section class="projects-section" id="projects">
        <div class="container">
            <!-- Section Header with Orange Left Border -->
            <div class="section-header-row">
                <div>
                    <h1>PROJECTS</h1>
                    <p class="subtitle code-sm">Mastery_Archive.v2</p>
                </div>
                <button class="filter-btn">
                    <span class="material-symbols-outlined">filter_list</span>
                    <span>FILTRE</span>
                </button>
            </div>

            <!-- Projects Grid -->
            <div class="projects-grid">

                <!-- Project 1: Nouchi Lexicon App -->
                <a href="projet_nouchi.php" class="project-article project-article--green project-article--link">
                    <div class="project-article__thumb">
                        <img src="Presentation/Capture1 apk.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon App">
                        <div class="project-article__badge project-article__badge--green">
                            <span>01_MO</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Nouchi Lexicon App</h2>
                        <p class="project-article__desc">Application mobile interactive servant de dictionnaire pour l'argot ivoirien (Nouchi). Développée avec Flutter pour offrir une expérience fluide hors ligne.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--green">#FLUTTER</span>
                            <span class="project-tag project-tag--orange">#DART</span>
                            <span class="project-tag project-tag--neutral">#MOBILE_UI</span>
                        </div>
                    </div>
                </a>

            </div>

            <!-- Bottom CTA Section -->
            <div class="projects-cta">
                <div class="projects-cta__bar"></div>
                <div class="projects-cta__inner">
                    <div>
                        <h3>Build your vision?</h3>
                        <p>Collaborate on high-performance projects that push technical and creative boundaries.</p>
                    </div>
                    <a href="portfolio.php" class="projects-cta__btn" style="background: transparent; color: var(--secondary-bright); border: 1px solid rgba(0,158,96,0.3);">PORTFOLIO</a>
                    <a href="#contact" class="projects-cta__btn">CONTACT</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 4 — SYNERGY: CRYPTO × GAMING
         ================================ -->
    <section class="synergy-section" id="synergy">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Cross-Domain Intelligence</div>
                <h1 class="section-header__title">SYNERGY_<span class="text-primary">MATRIX</span></h1>
                <p class="section-header__desc">
                    Des domaines isolés naissent les vulnérabilités. De leurs intersections naissent les opportunités. Explorez les ponts entre Crypto, Gaming et Hacking — les frontières où se joue l'avenir du numérique.
                </p>
            </div>

            <!-- Synergy Terminal Card -->
            <div class="synergy-terminal">
                <div class="synergy-terminal__scanline"></div>
                <div class="synergy-terminal__bar">
                    <div class="synergy-terminal__dots">
                        <span class="synergy-terminal__dot" style="background:rgba(255,180,171,0.4);"></span>
                        <span class="synergy-terminal__dot" style="background:rgba(255,183,133,0.4);"></span>
                        <span class="synergy-terminal__dot" style="background:rgba(97,221,152,0.4);"></span>
                    </div>
                    <span class="synergy-terminal__bar-title label-caps">SYNERGY_ENGINE_V1</span>
                    <span class="synergy-terminal__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">SYNC</span>
                    </span>
                </div>
                <div class="synergy-terminal__body">

                    <!-- ═══════ CLUSTER 1 : CRYPTO × GAMING ═══════ -->
                    <div class="synergy-cluster-label">
                        <span class="synergy-cluster-label__icon">
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">currency_bitcoin</span>
                            <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">close</span>
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">sports_esports</span>
                        </span>
                        <span class="synergy-cluster-label__text label-caps">CLUSTER_01 // CRYPTO × GAMING</span>
                        <span class="synergy-cluster-label__line"></span>
                    </div>

                    <!-- Bridge Visualization -->
                    <div class="synergy-bridge">
                        <div class="synergy-bridge__node synergy-bridge__node--crypto">
                            <span class="material-symbols-outlined" style="font-size:28px;">currency_bitcoin</span>
                            <span class="label-caps">CRYPTO</span>
                        </div>
                        <div class="synergy-bridge__link">
                            <div class="synergy-bridge__line"></div>
                            <div class="synergy-bridge__pulse"></div>
                            <div class="synergy-bridge__line"></div>
                        </div>
                        <div class="synergy-bridge__node synergy-bridge__node--gaming">
                            <span class="material-symbols-outlined" style="font-size:28px;">sports_esports</span>
                            <span class="label-caps">GAMING</span>
                        </div>
                    </div>

                    <!-- Synergy Topics Grid -->
                    <div class="synergy-grid">

                        <!-- Card 1: Play-to-Earn -->
                        <div class="synergy-card" data-synergy="p2e">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--orange">
                                    <span class="material-symbols-outlined">stadia_controller</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--hot">
                                    <span class="pulse-dot pulse-dot--orange" style="width:6px; height:6px;"></span>
                                    <span>TRENDING</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Play-to-Earn</h3>
                            <p class="synergy-card__desc">
                                Les jeux P2E redéfinissent le rapport entre gameplay et rémunération. Des titres comme Axie Infinity, Illuvium et Pixels prouvent que les joueurs peuvent être les premiers acteurs de leur économie numérique.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#GAMEFI</span>
                                <span class="synergy-tag">#TOKENOMICS</span>
                                <span class="synergy-tag">#WEB3</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Marché P2E</span>
                                    <span class="synergy-metric__value text-primary">$4.2B+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Joueurs actifs</span>
                                    <span class="synergy-metric__value text-secondary-bright">2.5M+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: NFT Gaming -->
                        <div class="synergy-card" data-synergy="nft">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--green">
                                    <span class="material-symbols-outlined">deployed_code</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--live">
                                    <span class="pulse-dot pulse-dot--green" style="width:6px; height:6px;"></span>
                                    <span>ACTIVE</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">NFT dans le Gaming</h3>
                            <p class="synergy-card__desc">
                                Les NFT gaming transforment les assets in-game en propriété numérique réelle. Godcards, armes legendary et terrains virtuels — chaque item possède une provenance vérifiable on-chain et une valeur marchande.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#DIGITAL_OWNERSHIP</span>
                                <span class="synergy-tag">#SMART_CONTRACTS</span>
                                <span class="synergy-tag">#METaverse</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Volume NFT Gaming</span>
                                    <span class="synergy-metric__value text-primary">$1.8B</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Collections actives</span>
                                    <span class="synergy-metric__value text-secondary-bright">850+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Skins Economy -->
                        <div class="synergy-card" data-synergy="skins">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--purple">
                                    <span class="material-symbols-outlined">palette</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--evolving">
                                    <span class="pulse-dot" style="width:6px; height:6px; background:#a78bfa;"></span>
                                    <span>EVOLVING</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Économie des Skins</h3>
                            <p class="synergy-card__desc">
                                L'économie des skins CS2, Valorant et Fortnite dépasse celle de nombreux pays. Des marchés secondaires aux plateformes d'échange, les cosmétiques numériques sont devenus une classe d'actifs à part entière.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#SKIN_TRADE</span>
                                <span class="synergy-tag">#DIGITAL_ECONOMY</span>
                                <span class="synergy-tag">#RARITY</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Marché CS2</span>
                                    <span class="synergy-metric__value text-primary">$1.5B+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Transactions/jour</span>
                                    <span class="synergy-metric__value text-secondary-bright">500K+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: GameFi Infrastructure -->
                        <div class="synergy-card" data-synergy="infra">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--orange">
                                    <span class="material-symbols-outlined">developer_board</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--dev">
                                    <span class="material-symbols-outlined" style="font-size:10px;">code</span>
                                    <span>DEV_FOCUSED</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">GameFi Infrastructure</h3>
                            <p class="synergy-card__desc">
                                Les chaînes gaming-spécifiques (Immutable X, Ronin, Polygon) et les SDK blockchain (Enjin, Unity Web3) créent les fondations techniques de la prochaine génération de jeux décentralisés.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#LAYER2</span>
                                <span class="synergy-tag">#GAME_SDK</span>
                                <span class="synergy-tag">#ON_CHAIN</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">TPS Gaming</span>
                                    <span class="synergy-metric__value text-primary">9 000+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Frais/gas</span>
                                    <span class="synergy-metric__value text-secondary-bright">~$0.01</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Synergy Cross-Links -->
                    <div class="synergy-crosslinks">
                        <div class="synergy-crosslinks__header">
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">hub</span>
                            <span class="label-caps">CROSS_DOMAIN_LINKS</span>
                        </div>
                        <div class="synergy-crosslinks__grid">
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">currency_bitcoin</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">security</span>
                                </div>
                                <span class="synergy-crosslink__label">Smart Contract Audits pour Jeux</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">token</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">code</span>
                                </div>
                                <span class="synergy-crosslink__label">Token Engineering & Game Design</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">paid</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">sports_esports</span>
                                </div>
                                <span class="synergy-crosslink__label">Esports & Crypto Betting</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">deployed_code</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">terminal</span>
                                </div>
                                <span class="synergy-crosslink__label">NFT Minting & Backend Architecture</span>
                            </div>
                        </div>
                    </div>

                    <!-- ═══════ CLUSTER DIVIDER ═══════ -->
                    <div class="synergy-divider">
                        <span class="synergy-divider__line"></span>
                        <span class="material-symbols-outlined" style="font-size:14px; color:rgba(255,255,255,0.15);">more_horiz</span>
                        <span class="synergy-divider__line"></span>
                    </div>

                    <!-- ═══════ CLUSTER 2 : HACKING × CRYPTO ═══════ -->
                    <div class="synergy-cluster-label">
                        <span class="synergy-cluster-label__icon">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">shield</span>
                            <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">close</span>
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">currency_bitcoin</span>
                        </span>
                        <span class="synergy-cluster-label__text label-caps">CLUSTER_02 // HACKING × CRYPTO</span>
                        <span class="synergy-cluster-label__line"></span>
                    </div>

                    <!-- Bridge Visualization: Hacking ⟷ Crypto -->
                    <div class="synergy-bridge">
                        <div class="synergy-bridge__node synergy-bridge__node--hacking">
                            <span class="material-symbols-outlined" style="font-size:28px;">shield</span>
                            <span class="label-caps">HACKING</span>
                        </div>
                        <div class="synergy-bridge__link">
                            <div class="synergy-bridge__line synergy-bridge__line--red"></div>
                            <div class="synergy-bridge__pulse synergy-bridge__pulse--red"></div>
                            <div class="synergy-bridge__line synergy-bridge__line--red"></div>
                        </div>
                        <div class="synergy-bridge__node synergy-bridge__node--crypto">
                            <span class="material-symbols-outlined" style="font-size:28px;">currency_bitcoin</span>
                            <span class="label-caps">CRYPTO</span>
                        </div>
                    </div>

                    <!-- Hacking × Crypto Cards Grid -->
                    <div class="synergy-grid">

                        <!-- Card 1: Wallet Security -->
                        <div class="synergy-card" data-synergy="wallet-sec">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--red">
                                    <span class="material-symbols-outlined">account_balance_wallet</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--critical">
                                    <span class="pulse-dot pulse-dot--red" style="width:6px; height:6px;"></span>
                                    <span>CRITICAL</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Sécurité des Wallets</h3>
                            <p class="synergy-card__desc">
                                Un wallet compromis = des fonds perdus à jamais. Des attaques par phishing aux exploits de clé privée, les vecteurs d'attaque évoluent constamment. Hardware wallets, multi-sig, seed phrase management — chaque couche compte.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#WALLET_SEC</span>
                                <span class="synergy-tag">#PRIVATE_KEY</span>
                                <span class="synergy-tag">#HARDWARE_WALLET</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Fonds volés 2024</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">$1.8B</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Attaques wallet/jour</span>
                                    <span class="synergy-metric__value text-primary">1 200+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: DeFi Vulnerabilities -->
                        <div class="synergy-card" data-synergy="defi-vuln">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--red">
                                    <span class="material-symbols-outlined">warning</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--hot">
                                    <span class="pulse-dot pulse-dot--orange" style="width:6px; height:6px;"></span>
                                    <span>TRENDING</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Failles DeFi</h3>
                            <p class="synergy-card__desc">
                                Les protocoles DeFi sont des cibles de choix : flash loan attacks, oracle manipulation, reentrancy exploits, rug pulls. L'analyse on-chain révèle des patterns d'attaque récurrents que tout hacker éthique doit maîtriser.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#DEFI_EXPLOIT</span>
                                <span class="synergy-tag">#FLASH_LOAN</span>
                                <span class="synergy-tag">#REENTRANCY</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Hacks DeFi 2024</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">$770M</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Protocoles touchés</span>
                                    <span class="synergy-metric__value text-primary">180+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Smart Contract Auditing -->
                        <div class="synergy-card" data-synergy="audit">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--green">
                                    <span class="material-symbols-outlined">fact_check</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--dev">
                                    <span class="material-symbols-outlined" style="font-size:10px;">code</span>
                                    <span>DEV_FOCUSED</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Smart Contract Auditing</h3>
                            <p class="synergy-card__desc">
                                Avant tout déploiement, un audit rigoureux est obligatoire. Slither, Mythril, Echidna pour le fuzzing. Vérification formelle pour les protocoles critiques. Un bug dans un contrat = des millions perdus en quelques secondes.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#SOLIDITY_AUDIT</span>
                                <span class="synergy-tag">#FUZZING</span>
                                <span class="synergy-tag">#FORMAL_VERIFY</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Bugs critiques/an</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">420+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Bounty moyen</span>
                                    <span class="synergy-metric__value text-secondary-bright">$50K</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Blockchain Forensics -->
                        <div class="synergy-card" data-synergy="forensics">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--orange">
                                    <span class="material-symbols-outlined">travel_explore</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--live">
                                    <span class="pulse-dot pulse-dot--green" style="width:6px; height:6px;"></span>
                                    <span>ACTIVE</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Forensique Blockchain</h3>
                            <p class="synergy-card__desc">
                                Le traçage on-chain permet de suivre les flux de fonds illicites. Chainalysis, Elliptic et des outils custom révèlent les liens entre wallets, mixers et exchanges. L'art de transformer la transparence de la blockchain en arme d'investigation.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#ON_CHAIN_ANALYSIS</span>
                                <span class="synergy-tag">#AML</span>
                                <span class="synergy-tag">#TRACE</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Fonds tracés 2024</span>
                                    <span class="synergy-metric__value text-primary">$3.4B</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Fonds récupérés</span>
                                    <span class="synergy-metric__value text-secondary-bright">18%</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Hacking × Crypto Cross-Links -->
                    <div class="synergy-crosslinks">
                        <div class="synergy-crosslinks__header">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">shield</span>
                            <span class="label-caps">OFFSEC × CRYPTO_LINKS</span>
                        </div>
                        <div class="synergy-crosslinks__grid">
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">shield</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">account_balance_wallet</span>
                                </div>
                                <span class="synergy-crosslink__label">Pentest d'Interfaces Wallet</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">bug_report</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">token</span>
                                </div>
                                <span class="synergy-crosslink__label">Exploit DeFi & Bug Bounty</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">travel_explore</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">currency_bitcoin</span>
                                </div>
                                <span class="synergy-crosslink__label">OSINT & Traçage On-Chain</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">policy</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">gavel</span>
                                </div>
                                <span class="synergy-crosslink__label">Conformité AML & KYC Crypto</span>
                            </div>
                        </div>
                    </div>

                    <!-- ═══════ CLUSTER DIVIDER ═══════ -->
                    <div class="synergy-divider">
                        <span class="synergy-divider__line"></span>
                        <span class="material-symbols-outlined" style="font-size:14px; color:rgba(255,255,255,0.15);">more_horiz</span>
                        <span class="synergy-divider__line"></span>
                    </div>

                    <!-- ═══════ CLUSTER 3 : HACKING × GAMING ═══════ -->
                    <div class="synergy-cluster-label">
                        <span class="synergy-cluster-label__icon">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">shield</span>
                            <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">close</span>
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">sports_esports</span>
                        </span>
                        <span class="synergy-cluster-label__text label-caps">CLUSTER_03 // HACKING × GAMING</span>
                        <span class="synergy-cluster-label__line"></span>
                    </div>

                    <!-- Bridge Visualization: Hacking ⟷ Gaming -->
                    <div class="synergy-bridge">
                        <div class="synergy-bridge__node synergy-bridge__node--hacking">
                            <span class="material-symbols-outlined" style="font-size:28px;">shield</span>
                            <span class="label-caps">HACKING</span>
                        </div>
                        <div class="synergy-bridge__link">
                            <div class="synergy-bridge__line synergy-bridge__line--redgreen"></div>
                            <div class="synergy-bridge__pulse synergy-bridge__pulse--redgreen"></div>
                            <div class="synergy-bridge__line synergy-bridge__line--redgreen"></div>
                        </div>
                        <div class="synergy-bridge__node synergy-bridge__node--gaming">
                            <span class="material-symbols-outlined" style="font-size:28px;">sports_esports</span>
                            <span class="label-caps">GAMING</span>
                        </div>
                    </div>

                    <!-- Hacking × Gaming Cards Grid -->
                    <div class="synergy-grid">

                        <!-- Card 1: Game Server Security -->
                        <div class="synergy-card" data-synergy="game-server">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--red">
                                    <span class="material-symbols-outlined">dns</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--critical">
                                    <span class="pulse-dot pulse-dot--red" style="width:6px; height:6px;"></span>
                                    <span>CRITICAL</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Sécurité des Serveurs de Jeux</h3>
                            <p class="synergy-card__desc">
                                Les serveurs de jeux sont des cibles de choix : DDoS massifs, RCE via des failles dans les moteurs (Source, Unreal), man-in-the-middle sur les communications client-serveur. Protéger l'infrastructure game est un défi d'ingénierie offensif permanent.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#DDOS</span>
                                <span class="synergy-tag">#RCE</span>
                                <span class="synergy-tag">#SERVER_HARDENING</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Attaques DDoS/jour</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">80K+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Coût downtime/h</span>
                                    <span class="synergy-metric__value text-primary">$150K</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Account Protection -->
                        <div class="synergy-card" data-synergy="account-sec">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--orange">
                                    <span class="material-symbols-outlined">person_off</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--hot">
                                    <span class="pulse-dot pulse-dot--orange" style="width:6px; height:6px;"></span>
                                    <span>TRENDING</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Protection des Comptes</h3>
                            <p class="synergy-card__desc">
                                Le vol de comptes Steam, Epic, PlayStation Network exploite credential stuffing, OAuth hijacking et SIM swapping. L'inventaire d'un joueur CS2 peut valoir des dizaines de milliers de dollars — un pactole pour les cybercriminels.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#CREDENTIAL_STUFFING</span>
                                <span class="synergy-tag">#2FA</span>
                                <span class="synergy-tag">#SIM_SWAP</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Comptes volés/an</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">12M+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Valeur moyenne/inventaire</span>
                                    <span class="synergy-metric__value text-primary">$340</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Anti-Cheat & EAC Bypass -->
                        <div class="synergy-card" data-synergy="anticheat">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--green">
                                    <span class="material-symbols-outlined">verified_user</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--live">
                                    <span class="pulse-dot pulse-dot--green" style="width:6px; height:6px;"></span>
                                    <span>ACTIVE</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Anti-Cheat vs Bypass</h3>
                            <p class="synergy-card__desc">
                                La guerre entre anti-cheats (EAC, BattlEye, Vanguard) et les développeurs de cheats est un bras de fer constant : kernel-level drivers, memory manipulation, DMA hardware cheats. Comprendre cette course aux armements, c'est comprendre la sécurité offensive moderne.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#KERNEL_DRIVER</span>
                                <span class="synergy-tag">#DMA_CHEAT</span>
                                <span class="synergy-tag">#MEMORY_SCAN</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Cheats détectés/an</span>
                                    <span class="synergy-metric__value text-secondary-bright">2.5M+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Marché des cheats</span>
                                    <span class="synergy-metric__value text-primary">$800M+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Esports Infrastructure -->
                        <div class="synergy-card" data-synergy="esports-infra">
                            <div class="synergy-card__scanline"></div>
                            <div class="synergy-card__header">
                                <div class="synergy-card__icon-wrap synergy-card__icon-wrap--purple">
                                    <span class="material-symbols-outlined">emoji_events</span>
                                </div>
                                <div class="synergy-card__badge synergy-card__badge--evolving">
                                    <span class="pulse-dot" style="width:6px; height:6px; background:#a78bfa;"></span>
                                    <span>EVOLVING</span>
                                </div>
                            </div>
                            <h3 class="synergy-card__title">Infrastructure Esports</h3>
                            <p class="synergy-card__desc">
                                Les tournois esports avec des prize pools millionnaires attirent les attaques sophistiquées : DDoS pendant les matchs live, compromission des réseaux LAN, vol de stratégies via spyware. La sécurité des événements compétitifs est un domaine à part entière.
                            </p>
                            <div class="synergy-card__tags">
                                <span class="synergy-tag">#LAN_SEC</span>
                                <span class="synergy-tag">#MATCH_FIXING</span>
                                <span class="synergy-tag">#STREAM_SEC</span>
                            </div>
                            <div class="synergy-card__metrics">
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Prize pools 2024</span>
                                    <span class="synergy-metric__value text-primary">$280M+</span>
                                </div>
                                <div class="synergy-metric">
                                    <span class="synergy-metric__label">Incidents majeurs</span>
                                    <span class="synergy-metric__value" style="color:#ff6b6b;">47</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Hacking × Gaming Cross-Links -->
                    <div class="synergy-crosslinks">
                        <div class="synergy-crosslinks__header">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">sports_esports</span>
                            <span class="label-caps">OFFSEC × GAMING_LINKS</span>
                        </div>
                        <div class="synergy-crosslinks__grid">
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">dns</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">sports_esports</span>
                                </div>
                                <span class="synergy-crosslink__label">Pentest d'Infrastructures de Jeux</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">bug_report</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">controller_gen</span>
                                </div>
                                <span class="synergy-crosslink__label">Reverse Engineering de Game Engines</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">person_off</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">shield</span>
                                </div>
                                <span class="synergy-crosslink__label">OSINT sur les Marchés de Comptes Volés</span>
                            </div>
                            <div class="synergy-crosslink">
                                <div class="synergy-crosslink__icons">
                                    <span class="material-symbols-outlined" style="font-size:16px; color:#ff6b6b;">network_check</span>
                                    <span class="material-symbols-outlined" style="font-size:12px; color:rgba(255,255,255,0.25);">add</span>
                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">emoji_events</span>
                                </div>
                                <span class="synergy-crosslink__label">Sécurité Réseaux LAN Tournois</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>



    <!-- ================================
         SECTION 4.5 — GAMING CALENDAR
         ================================ -->
    <section class="gc-section" id="gaming-calendar">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Esports Intelligence</div>
                <h1 class="section-header__title">GAMING_<span class="text-primary">CALENDAR</span></h1>
                <p class="section-header__desc">
                    Calendrier des tournois esports en temps réel. Suivez les compétitions majeures organisées par catégorie de jeu.
                </p>
            </div>

            <!-- Stats -->
            <div class="gc-stats" id="gc-stats"></div>

            <!-- Category Tabs -->
            <div class="gc-tabs-wrapper">
                <div class="gc-category-nav" id="gc-category-nav"></div>
                <div class="gc-status-nav" id="gc-status-nav"></div>
            </div>

            <!-- Tournaments Container -->
            <div class="gc-tournaments" id="gc-tournaments">
                <div class="gc-loading">
                    <span class="material-symbols-outlined gc-loading__icon">sync</span>
                    <span>Chargement des tournois...</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 5 — BLOG / FLUX RSS
         ================================ -->
    <section class="rss-section" id="blog">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Signal Feed</div>
                <h1 class="section-header__title">BLOG_FEED</h1>
                <p class="section-header__desc">
                    Dernières transmissions captées sur le réseau. Articles, veille technique et signaux cybers en temps réel.
                </p>
            </div>

            <!-- ================================
                 PUBLICATIONS
                 ================================ -->
            <div class="blog-pub">
                <div class="blog-pub__header">
                    <div class="blog-pub__header-left">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">edit_note</span>
                        <span class="label-caps">Publications</span>
                        <span class="pulse-dot pulse-dot--green" style="margin-left:6px;"></span>
                    </div>
                    <?php if ($isLoggedIn && isAdmin()): ?>
                    <a href="article_structurer.php" class="blog-pub__new-btn">
                        <span class="material-symbols-outlined" style="font-size:14px;">add</span>
                        <span>Nouvel article</span>
                    </a>
                    <?php endif; ?>
                </div>

                <?php if ($isLoggedIn && isAdmin()): ?>
                <!-- Formulaire de publication rapide -->
                <div class="blog-pub__quick-form" id="quickPublishForm">
                    <div class="blog-pub__quick-form-toggle">
                        <button class="blog-pub__quick-btn" onclick="toggleQuickPublish()">
                            <span class="material-symbols-outlined" style="font-size:16px;">edit_square</span>
                            <span>Publier un article</span>
                        </button>
                    </div>
                    <div class="blog-pub__quick-form-body" id="quickPublishBody" style="display:none;">
                        <form method="POST" action="article_structurer.php">
                            <input type="hidden" name="publish" value="1">
                            <div class="blog-pub__quick-field">
                                <label>Sujet / Thème</label>
                                <input type="text" name="topic" placeholder="Ex: Flutter 3.24, Docker best practices..." required>
                            </div>
                            <div class="blog-pub__quick-field">
                                <label>Texte / Notes (optionnel)</label>
                                <textarea name="raw_text" placeholder="Collez vos notes, un résumé, un lien vers un article..." rows="3"></textarea>
                            </div>
                            <div class="blog-pub__quick-field">
                                <label>Lien source (optionnel)</label>
                                <input type="url" name="url" placeholder="https://example.com/article">
                            </div>
                            <div class="blog-pub__quick-actions">
                                <button type="submit" class="blog-pub__quick-submit">
                                    <span class="material-symbols-outlined" style="font-size:16px;">publish</span>
                                    Générer & Publier
                                </button>
                                <button type="button" class="blog-pub__quick-cancel" onclick="toggleQuickPublish()">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($publishedArticles)): ?>
                <div class="blog-pub__grid">
                    <?php foreach ($publishedArticles as $art):

                        $artTags = json_decode($art["tags"], true) ?? [];
                        $artPlan = json_decode($art["plan"], true) ?? [];
                        ?>
                    <a href="article.php?id=<?= $art[
                        "id"
                    ] ?>" class="blog-pub__card-link">
                    <article class="blog-pub__card">
                        <div class="blog-pub__card-scanline"></div>
                        <!-- Barre terminal -->
                        <div class="blog-pub__card-bar">
                            <div class="blog-pub__card-dots">
                                <span class="blog-pub__card-dot" style="background:rgba(255,180,171,0.4);"></span>
                                <span class="blog-pub__card-dot" style="background:rgba(255,183,133,0.4);"></span>
                                <span class="blog-pub__card-dot" style="background:rgba(97,221,152,0.4);"></span>
                            </div>
                            <span class="blog-pub__card-id">#<?= str_pad(
                                $art["id"],
                                3,
                                "0",
                                STR_PAD_LEFT,
                            ) ?></span>
                            <span class="blog-pub__card-cat"><?= htmlspecialchars(
                                $art["category"],
                            ) ?></span>
                            <span class="blog-pub__card-date"><?= date(
                                "d.m.Y",
                                strtotime($art["created_at"]),
                            ) ?></span>
                        </div>
                        <!-- Corps -->
                        <div class="blog-pub__card-body">
                            <h3 class="blog-pub__card-title"><?= htmlspecialchars(
                                $art["seo_title"] ?: $art["title"],
                            ) ?></h3>
                            <?php if (!empty($art["meta_description"])): ?>
                            <p class="blog-pub__card-meta"><?= htmlspecialchars(
                                safeSubstr($art["meta_description"], 0, 160),
                            ) .
                                (safeStrlen($art["meta_description"]) > 160
                                    ? "..."
                                    : "") ?></p>
                            <?php endif; ?>
                            <?php if (!empty($art["introduction"])): ?>
                            <div class="blog-pub__card-intro"><?= nl2br(
                                htmlspecialchars(
                                    safeSubstr(
                                        strip_tags($art["introduction"]),
                                        0,
                                        220,
                                    ),
                                ),
                            ) .
                                (safeStrlen(strip_tags($art["introduction"])) >
                                220
                                    ? "..."
                                    : "") ?></div>
                            <?php endif; ?>
                            <?php if (!empty($artPlan)): ?>
                            <div class="blog-pub__card-plan">
                                <span class="blog-pub__card-plan-label">
                                    <span class="material-symbols-outlined" style="font-size:12px;">format_list_bulleted</span>
                                    Plan (<?= count($artPlan) ?> sections)
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($artTags)): ?>
                            <div class="blog-pub__card-tags">
                                <?php foreach (
                                    array_slice($artTags, 0, 5)
                                    as $tag
                                ): ?>
                                <span class="blog-pub__card-tag"><?= htmlspecialchars(
                                    $tag,
                                ) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="blog-pub__card-footer">
                                <span class="blog-pub__card-author">
                                    <span class="material-symbols-outlined" style="font-size:12px;">person</span>
                                    <?= htmlspecialchars($art["author_name"]) ?>
                                </span>
                                <span class="blog-pub__card-comments">
                                    <span class="material-symbols-outlined" style="font-size:12px;">chat_bubble</span>
                                    <?= getCommentCount($art["id"]) ?>
                                </span>
                                <span class="blog-pub__card-read">
                                    Lire l'article
                                    <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                        <!-- Delete button (admin only) -->
                        <?php if ($isLoggedIn && isAdmin()): ?>
                        <form method="POST" action="index.php#blog" class="blog-pub__card-delete-form" onclick="event.stopPropagation();" onsubmit="return confirm('Supprimer cet article ?');">
                            <input type="hidden" name="delete_article" value="<?= $art[
                                "id"
                            ] ?>">
                            <button type="submit" class="blog-pub__card-delete" title="Supprimer">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                        <?php endif; ?>
                    </article>
                    </a>
                    <?php
                    endforeach; ?>
                </div>
                <?php else: ?>
                <!-- État vide -->
                <div class="blog-pub__empty">
                    <div class="blog-pub__empty-icon">
                        <span class="material-symbols-outlined">article</span>
                    </div>
                    <p class="blog-pub__empty-text">Aucune publication pour le moment.</p>
                    <p class="blog-pub__empty-sub">Les articles générés via l'Article Structurer apparaîtront ici automatiquement.</p>
                    <?php if ($isLoggedIn && isAdmin()): ?>
                    <a href="article_structurer.php" class="blog-pub__empty-btn">
                        <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
                        Créer un article
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- RSS Magazine -->
            <div class="rss-card" style="margin-bottom: var(--sp-lg);">
                <div class="rss-card__scanline"></div>
                <div class="rss-card__bar">
                    <div class="rss-card__dots">
                        <span class="rss-card__dot rss-card__dot--red"></span>
                        <span class="rss-card__dot rss-card__dot--yellow"></span>
                        <span class="rss-card__dot rss-card__dot--green"></span>
                    </div>
                    <span class="rss-card__bar-title label-caps">INFO_MAGAZINE</span>
                    <span class="rss-card__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">LIVE</span>
                    </span>
                </div>
                <div class="rss-card__widget">
                    <rssapp-magazine id="zVn6TvX26blDCSPJ"></rssapp-magazine><script data-src="https://widget.rss.app/v1/magazine.js" type="text/javascript" async class="rss-lazy"></script>
                </div>
            </div>

            <!-- Separator -->
            <div class="rss-card__divider" style="margin: var(--sp-xl) 0;">
                <span class="rss-card__divider-line"></span>
                <span class="material-symbols-outlined" style="font-size:14px; color:rgba(255,255,255,0.15);">more_horiz</span>
                <span class="rss-card__divider-line"></span>
            </div>

            <!-- RSS Unified Card -->
            <div class="rss-card">
                <div class="rss-card__scanline"></div>
                <div class="rss-card__bar">
                    <div class="rss-card__dots">
                        <span class="rss-card__dot rss-card__dot--red"></span>
                        <span class="rss-card__dot rss-card__dot--yellow"></span>
                        <span class="rss-card__dot rss-card__dot--green"></span>
                    </div>
                    <span class="rss-card__bar-title label-caps">RSS_TERMINAL</span>
                    <span class="rss-card__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">STREAMING</span>
                    </span>
                </div>

                <!-- Widget 1: Carousel -->
                <div class="rss-card__widget">
                    <div class="rss-card__widget-label">
                        <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">view_carousel</span>
                        <span class="label-caps">Highlights</span>
                    </div>
                    <rssapp-carousel id="TVOvmVGK5J7yEQY4"></rssapp-carousel><script data-src="https://widget.rss.app/v1/carousel.js" type="text/javascript" async class="rss-lazy"></script>
                </div>

                <!-- Separator -->
                <div class="rss-card__divider">
                    <span class="rss-card__divider-line"></span>
                    <span class="material-symbols-outlined" style="font-size:14px; color:rgba(255,255,255,0.15);">more_horiz</span>
                    <span class="rss-card__divider-line"></span>
                </div>

                <!-- Widget 2: Wall -->
                <div class="rss-card__widget">
                    <div class="rss-card__widget-label">
                        <span class="material-symbols-outlined" style="font-size:16px; color:var(--secondary-bright);">grid_view</span>
                        <span class="label-caps">Archive</span>
                    </div>
                    <rssapp-wall id="S5zmzgmuJRPBcSVo"></rssapp-wall><script data-src="https://widget.rss.app/v1/wall.js" type="text/javascript" async class="rss-lazy"></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 6 — GALLERY / IMAGEBOARD
         ================================ -->
    <section class="gallery-section" id="gallery">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Visual Stream</div>
                <h1 class="section-header__title">GALLERY</h1>
                <p class="section-header__desc">
                    Captures visuelles du réseau. Retours d'expérience, infographies et instantanés du quotidien cybers.
                </p>
            </div>

            <!-- Imageboard Terminal Card -->
            <div class="gallery-card">
                <div class="gallery-card__scanline"></div>
                <div class="gallery-card__bar">
                    <div class="gallery-card__dots">
                        <span class="gallery-card__dot gallery-card__dot--red"></span>
                        <span class="gallery-card__dot gallery-card__dot--yellow"></span>
                        <span class="gallery-card__dot gallery-card__dot--green"></span>
                    </div>
                    <span class="gallery-card__bar-title label-caps">IMG_BOARD_V1</span>
                    <span class="gallery-card__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">SYNC</span>
                    </span>
                </div>
                <div class="gallery-card__body">
                    <rssapp-imageboard id="cc3dcW1cljMM3wlZ"></rssapp-imageboard><script data-src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async class="rss-lazy"></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 7 — MEDIA WALL
         ================================ -->
    <section class="media-section" id="media">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Media Stream</div>
                <h1 class="section-header__title">MEDIA_WALL</h1>
                <p class="section-header__desc">
                    Flux multimédia en temps réel. Veille visuelle, ressources et inspirations du monde tech.
                </p>
            </div>

            <!-- Media Terminal Card -->
            <div class="media-card">
                <div class="media-card__scanline"></div>
                <div class="media-card__bar">
                    <div class="media-card__dots">
                        <span class="media-card__dot media-card__dot--red"></span>
                        <span class="media-card__dot media-card__dot--yellow"></span>
                        <span class="media-card__dot media-card__dot--green"></span>
                    </div>
                    <span class="media-card__bar-title label-caps">MEDIA_STREAM_V1</span>
                    <span class="media-card__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">LIVE</span>
                    </span>
                </div>
                <div class="media-card__body">
                    <rssapp-imageboard id="lGxVnaoQxFBHUR53"></rssapp-imageboard><script data-src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async class="rss-lazy"></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 7.5 — SIGNAL_FEED
         ================================ -->
    <section class="gallery-section" id="signal-feed">
        <div class="container">
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Signal Feed</div>
                <h1 class="section-header__title">SIGNAL_FEED</h1>
                <p class="section-header__desc">
                    Source supplémentaire. Veille technologique, actualités et découvertes du moment.
                </p>
            </div>

            <div class="media-card">
                <div class="media-card__scanline"></div>
                <div class="media-card__bar">
                    <div class="media-card__dots">
                        <span class="media-card__dot media-card__dot--red"></span>
                        <span class="media-card__dot media-card__dot--yellow"></span>
                        <span class="media-card__dot media-card__dot--green"></span>
                    </div>
                    <span class="media-card__bar-title label-caps">SIGNAL_STREAM</span>
                    <span class="media-card__bar-status">
                        <span class="pulse-dot pulse-dot--green"></span>
                        <span class="label-caps" style="font-size:9px; color:rgba(255,255,255,0.35);">LIVE</span>
                    </span>
                </div>
                <div class="media-card__body">
                    <rssapp-imageboard id="Lt3ZLEhfxXAnAcuM"></rssapp-imageboard><script data-src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async class="rss-lazy"></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 8 — DONATIONS / SOUTIEN
         ================================ -->
    <section class="donate-section" id="donate">
        <div class="container">
            <div class="section-header">
                <div class="section-header__bar"></div>

                <p class="section-header__desc">
                    Soutenez le projet BLACK_PROTOCOL. Chaque contribution finance la recherche, les outils et le contenu libre.
                </p>
            </div>

            <div class="donate-grid">

                <!-- Crypto Cards -->
                <div class="donate-card">
                    <div class="donate-card__scanline"></div>
                    <div class="donate-card__bar">
                        <div class="donate-card__dots">
                            <span class="donate-card__dot" style="background:rgba(255,180,171,0.4);"></span>
                            <span class="donate-card__dot" style="background:rgba(255,183,133,0.4);"></span>
                            <span class="donate-card__dot" style="background:rgba(97,221,152,0.4);"></span>
                        </div>
                        <span class="donate-card__bar-title">CRYPTO_WALLETS</span>
                        <span class="material-symbols-outlined" style="font-size:14px; color:var(--primary); animation:spin-slow 3s linear infinite;">currency_bitcoin</span>
                    </div>
                    <div class="donate-card__body">

                        <!-- Bitcoin -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--btc">
                                <span class="material-symbols-outlined">currency_bitcoin</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">Bitcoin (BTC)</div>
                                <div class="crypto-item__network">Bitcoin Network</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-btc">12uHTihwfMokdsk11wDCSbUSDkGprAtDNj</code>
                                <button class="crypto-item__copy" onclick="copyAddr('btc')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- Ethereum -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--eth">
                                <span class="material-symbols-outlined">token</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">Ethereum (ETH)</div>
                                <div class="crypto-item__network">ERC-20</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-eth">0x19231e776113F0d4fCb389431724E8BAF06f995a</code>
                                <button class="crypto-item__copy" onclick="copyAddr('eth')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- USDT TRC20 -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--usdt">
                                <span class="material-symbols-outlined">paid</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">USDT (Tether)</div>
                                <div class="crypto-item__network">TRC-20 / TRON</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-usdt">TVSGR29RsQsXoRTH2kkLwJXhcaj9pPnvPX</code>
                                <button class="crypto-item__copy" onclick="copyAddr('usdt')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- Litecoin -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--ltc">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">Litecoin (LTC)</div>
                                <div class="crypto-item__network">Litecoin Network</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-ltc">ltc1qxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code>
                                <button class="crypto-item__copy" onclick="copyAddr('ltc')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- BNB -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--bnb">
                                <span class="material-symbols-outlined">diamond</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">BNB (Binance)</div>
                                <div class="crypto-item__network">BEP-20 / BSC</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-bnb">0x8dc0b23e268e8427e298cb8a2a7825cc50d8c8d3</code>
                                <button class="crypto-item__copy" onclick="copyAddr('bnb')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- Pi Network -->
                        <div class="crypto-item">
                            <div class="crypto-item__icon crypto-item__icon--pi">
                                <span class="material-symbols-outlined">auto_awesome</span>
                            </div>
                            <div class="crypto-item__info">
                                <div class="crypto-item__name">Pi Network (PI)</div>
                                <div class="crypto-item__network">Pi Blockchain</div>
                            </div>
                            <div class="crypto-item__addr-wrap">
                                <code class="crypto-item__addr" id="addr-pi">GA2KEDINJSR3C5GO3TZ7E5G5C5X4K7WVN2HOWARVTWHJWI3YIXI3QTDK</code>
                                <button class="crypto-item__copy" onclick="copyAddr('pi')">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Info Card -->
                <div class="donate-card donate-card--info">
                    <div class="donate-card__scanline"></div>
                    <div class="donate-card__bar">
                        <div class="donate-card__dots">
                            <span class="donate-card__dot" style="background:rgba(255,180,171,0.4);"></span>
                            <span class="donate-card__dot" style="background:rgba(255,183,133,0.4);"></span>
                            <span class="donate-card__dot" style="background:rgba(97,221,152,0.4);"></span>
                        </div>
                        <span class="donate-card__bar-title">SUPPORT_INFO</span>
                    </div>
                    <div class="donate-card__body">

                        <div class="donate-info">
                            <span class="material-symbols-outlined" style="font-size:32px; color:var(--primary); margin-bottom:var(--sp-sm);">volunteer_activism</span>
                            <h3 class="donate-info__title">Pourquoi donner ?</h3>
                            <p class="donate-info__text">BLACK_PROTOCOL est un projet open-source et communautaire. Vos dons financent directement :</p>
                        </div>

                        <div class="donate-features">
                            <div class="donate-feature">
                                <span class="material-symbols-outlined donate-feature__icon">code</span>
                                <span>Développement d'outils libres</span>
                            </div>
                            <div class="donate-feature">
                                <span class="material-symbols-outlined donate-feature__icon">school</span>
                                <span>Contenu éducatif gratuit</span>
                            </div>
                            <div class="donate-feature">
                                <span class="material-symbols-outlined donate-feature__icon">server</span>
                                <span>Hébergement & infrastructure</span>
                            </div>
                            <div class="donate-feature">
                                <span class="material-symbols-outlined donate-feature__icon">security</span>
                                <span>Recherche en cybersécurité</span>
                            </div>
                        </div>

                        <div class="donate-warning">
                            <span class="material-symbols-outlined" style="font-size:16px;">info</span>
                            <span>Vérifiez toujours l'adresse avant d'envoyer. Les transactions crypto sont irréversibles.</span>
                        </div>

                        <div class="donate-qr">
                            <span class="donate-qr__label">SCAN — Bitcoin (BTC)</span>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&color=6a58ff&bgcolor=0d0d0d&data=bitcoin:12uHTihwfMokdsk11wDCSbUSDkGprAtDNj" loading="lazy" decoding="async" alt="QR Code BTC" class="donate-qr__img">
                            <code class="donate-qr__addr">12uHTihwfMokdsk11wDCSbUSDkGprAtDNj</code>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 9 — NEWSLETTER
         ================================ -->
    <section class="newsletter-section" id="newsletter">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Stay Connected</div>
                <h1 class="section-header__title">NEWSLETTER</h1>
                <p class="section-header__desc">
                    Recevez les dernières transmissions directement dans votre boîte. Projets, articles techniques et signaux cybers — aucun spam, promesse d'opérateur.
                </p>
            </div>

            <!-- Newsletter Card -->
            <div class="newsletter-card">
                <div class="newsletter-card__scanline"></div>
                <div class="newsletter-card__bar">
                    <div class="newsletter-card__dots">
                        <span class="newsletter-card__dot newsletter-card__dot--red"></span>
                        <span class="newsletter-card__dot newsletter-card__dot--yellow"></span>
                        <span class="newsletter-card__dot newsletter-card__dot--green"></span>
                    </div>
                    <span class="newsletter-card__bar-title label-caps">SIGNAL_FEED_V1</span>
                </div>
                <div class="newsletter-card__body">
                    <div class="newsletter-card__info">
                        <span class="material-symbols-outlined newsletter-card__icon">campaign</span>
                        <div>
                            <h2 class="newsletter-card__title">RECEVEZ LES TRANSMISSIONS</h2>
                            <p class="newsletter-card__desc">
                                Abonnez-vous au flux BLACK_PROTOCOL. Projets exclusifs, veille cybersécurité et mises à jour — livrés avec précision.
                            </p>
                        </div>
                    </div>
                    <form class="newsletter-card__form" id="newsletter-form">
                        <div class="newsletter-card__input-wrap">
                            <span class="material-symbols-outlined newsletter-card__input-icon">mail</span>
                            <input class="newsletter-card__input" type="email" name="email" placeholder="votre@email.com" required autocomplete="email">
                        </div>
                        <button type="submit" class="newsletter-card__submit">
                            <span>S'ABONNER</span>
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                    <div class="newsletter-card__message" id="newsletter-message"></div>
                    <div class="newsletter-card__meta">
                        <span class="newsletter-card__meta-item">
                            <span class="material-symbols-outlined" style="font-size:14px;">shield</span>
                            <span>Zéro spam garanti</span>
                        </span>
                        <span class="newsletter-card__meta-item">
                            <span class="material-symbols-outlined" style="font-size:14px;">bolt</span>
                            <span>Désabonnement en un clic</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 10 — CONTACT
         ================================ -->
    <section class="contact-section" id="contact">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Establish Link</div>
                <h1 class="section-header__title">CONTACT</h1>
                <p class="section-header__desc">
                    Ready to deploy a new project or initiate a technical collaboration? Input your parameters below to establish a secure transmission.
                </p>
            </div>

            <!-- Contact Bento Layout -->
            <div class="contact-bento">

                <!-- Terminal Form Card -->
                <div class="contact-form-card">
                    <div class="contact-form-card__scanline"></div>
                    <div class="contact-form-card__bar">
                        <div class="contact-form-card__dots">
                            <span class="contact-form-card__dot contact-form-card__dot--red"></span>
                            <span class="contact-form-card__dot contact-form-card__dot--yellow"></span>
                            <span class="contact-form-card__dot contact-form-card__dot--green"></span>
                        </div>
                        <span class="contact-form-card__bar-title label-caps">STX_ENCRYPTED_V2</span>
                    </div>
                    <form class="contact-form-card__form" action="contact_action.php" method="POST">
                        <input type="text" name="website" style="display:none !important;" tabindex="-1" autocomplete="off">
                        <div>
                            <label class="form__label label-caps" for="user_name">USER_IDENTIFIER</label>
                            <input class="form__input" type="text" id="user_name" name="name" placeholder="Your Name" required>
                        </div>
                        <div>
                            <label class="form__label label-caps" for="user_email">RETURN_ADDRESS</label>
                            <input class="form__input" type="email" id="user_email" name="email" placeholder="email@domain.com" required>
                        </div>
                        <div>
                            <label class="form__label label-caps" for="user_message">DATA_PAYLOAD</label>
                            <textarea class="form__textarea" id="user_message" name="message" placeholder="System message..." rows="5" required></textarea>
                        </div>
                        <button type="submit" class="form__submit">
                            <span>SUBMISSION</span>
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                </div>

                <!-- Side Panel -->
                <div class="contact-side">

                    <!-- Status Card -->
                    <div class="status-card">
                        <div class="status-card__header">
                            <span class="status-card__title label-caps">LINK_STATUS</span>
                            <span class="status-card__pulse"></span>
                        </div>
                        <div class="status-card__rows">
                            <div class="status-card__row">
                                <span class="status-card__row-key">LATENCY</span>
                                <span class="status-card__row-value">24ms</span>
                            </div>
                            <div class="status-card__divider"></div>
                            <div class="status-card__row">
                                <span class="status-card__row-key">ENCRYPTION</span>
                                <span class="status-card__row-value">AES-256</span>
                            </div>
                            <div class="status-card__divider"></div>
                            <div class="status-card__row">
                                <span class="status-card__row-key">AVAILABILITY</span>
                                <span class="status-card__row-value">99.9%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Channels Card -->
                    <div class="channels-card">
                        <span class="channels-card__title label-caps">DIRECT_CHANNELS</span>
                        <div class="channels-card__list">
                            <!-- GitHub -->
                            <a href="https://github.com/vianson50" target="_blank" rel="noopener" class="channels-card__link">
                                <div class="channels-card__link-icon">
                                    <span class="material-symbols-outlined">terminal</span>
                                </div>
                                <div>
                                    <p class="channels-card__link-platform">GITHUB</p>
                                    <p class="channels-card__link-handle code-sm">/vianson50</p>
                                </div>
                            </a>
                            <!-- LinkedIn -->
                            <a href="#" class="channels-card__link">
                                <div class="channels-card__link-icon">
                                    <span class="material-symbols-outlined">account_circle</span>
                                </div>
                                <div>
                                    <p class="channels-card__link-platform">LINKEDIN</p>
                                    <p class="channels-card__link-handle code-sm">/in/creatorcore</p>
                                </div>
                            </a>
                            <!-- Discord -->
                            <a href="https://discord.com/users/yamiblack_kayden" target="_blank" rel="noopener" class="channels-card__link">
                                <div class="channels-card__link-icon">
                                    <span class="material-symbols-outlined">forum</span>
                                </div>
                                <div>
                                    <p class="channels-card__link-platform">DISCORD</p>
                                    <p class="channels-card__link-handle code-sm">@yamiblack_kayden</p>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Location Metadata Bar -->
            <div class="location-bar">
                <div class="location-bar__coord">
                    <span class="material-symbols-outlined">location_on</span>
                    <span class="code-sm">ABIDJAN, CÔTE D'IVOIRE // 5.3600° N, 4.0083° W</span>
                </div>
                <div class="location-bar__time code-sm">LOCAL_TIME: <span id="current-time"></span></div>
            </div>
        </div>
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
                <p class="footer__copy code-sm" style="margin-top:6px;">
                    <a href="#" onclick="document.getElementById('cookie-float').click(); return false;" style="color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s;">Cookie Settings</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- ================================
         FAB CHAT WIDGET
         ================================ -->
    <div class="fab" id="fab-chat">
        <button class="fab__btn" id="fab-toggle" aria-label="Chat">
            <span class="material-symbols-outlined" id="fab-icon">chat_bubble</span>
        </button>

        <!-- Chat Panel -->
        <div class="chat-panel" id="chat-panel">
            <div class="chat-panel__bar">
                <div class="chat-panel__dots">
                    <span class="chat-panel__dot chat-panel__dot--red"></span>
                    <span class="chat-panel__dot chat-panel__dot--yellow"></span>
                    <span class="chat-panel__dot chat-panel__dot--green"></span>
                </div>
                <span class="chat-panel__title label-caps">DARK_VADOR</span>
                <span class="chat-panel__status">
                    <span class="pulse-dot pulse-dot--green"></span>
                    <span class="label-caps" style="font-size:8px; color:rgba(255,255,255,0.3);">ONLINE</span>
                </span>
            </div>

            <div class="chat-panel__messages" id="chat-messages">
                <!-- Message d'accueil injecté en JS -->
            </div>

            <!-- Suggestions rapides -->
            <div class="chat-panel__suggestions" id="chat-suggestions">
                <button class="chat-suggestion" data-msg="Qui es-tu ?">Qui es-tu ?</button>
                <button class="chat-suggestion" data-msg="Compétences">Compétences</button>
                <button class="chat-suggestion" data-msg="Projets">Projets</button>
                <button class="chat-suggestion" data-msg="Contact">Contact</button>
                <button class="chat-suggestion" data-msg="Rédige l'intelligence artificielle" style="border-color:rgba(255,130,0,0.5); color:#ffb785;">✦ Rédactionnel</button>
            </div>

            <form class="chat-panel__input-wrap" id="chat-form">
                <input class="chat-panel__input" type="text" id="chat-input" placeholder="Tapez votre message..." autocomplete="off">
                <button type="submit" class="chat-panel__send">
                    <span class="material-symbols-outlined">send</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ================================
         BOTTOM NAVIGATION
         ================================ -->
    <nav class="nav-bottom" aria-label="Navigation principale">
        <ul class="nav-bottom__list">
            <li class="nav-bottom__item">
                <a href="#home" class="nav-bottom__link active" data-section="home">
                    <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' 1;">home</span>
                    <span class="nav-bottom__text">Home</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="#skills" class="nav-bottom__link" data-section="skills">
                    <span class="material-symbols-outlined nav-bottom__icon">bolt</span>
                    <span class="nav-bottom__text">Skills</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="#projects" class="nav-bottom__link" data-section="projects">
                    <span class="material-symbols-outlined nav-bottom__icon">grid_view</span>
                    <span class="nav-bottom__text">Projects</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="#contact" class="nav-bottom__link" data-section="contact">
                    <span class="material-symbols-outlined nav-bottom__icon">alternate_email</span>
                    <span class="nav-bottom__text">Contact</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="nav-bottom__link">
                        <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' <?= isAdmin()
                            ? "1"
                            : "0" ?>;">person</span>
                        <span class="nav-bottom__text"><?= isAdmin()
                            ? "Admin"
                            : "Profil" ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-bottom__link">
                        <span class="material-symbols-outlined nav-bottom__icon">login</span>
                        <span class="nav-bottom__text">Compte</span>
                    </a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

    <!-- ================================
         JAVASCRIPT
         ================================ -->
    <script>
        // — Active nav link on scroll —
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-bottom__link');

        // — Efficiency Chart Animation —
        const chartBars = document.querySelectorAll('.efficiency-chart__bar[data-height]');
        const tooltip = document.getElementById('chartTooltip');

        if (chartBars.length > 0) {
            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Animate bars with staggered delay
                        chartBars.forEach((bar, i) => {
                            const targetH = bar.getAttribute('data-height');
                            setTimeout(() => {
                                bar.style.height = targetH + '%';
                            }, i * 120);
                        });
                        chartObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            const chartEl = document.getElementById('efficiencyChart');
            if (chartEl) chartObserver.observe(chartEl);
        }

        // — Chart Hover Tooltip —
        document.querySelectorAll('.efficiency-chart__bar-wrap').forEach(wrap => {
            wrap.addEventListener('mouseenter', function(e) {
                if (!tooltip) return;
                const label = this.getAttribute('data-label');
                const pct = this.getAttribute('data-pct');
                tooltip.textContent = label + ' — ' + pct + '%';
                tooltip.classList.add('active');
            });
            wrap.addEventListener('mouseleave', function() {
                if (tooltip) tooltip.classList.remove('active');
            });
        });

        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute("id");
                    navLinks.forEach(link => {
                        const section = link.getAttribute("data-section");
                        // Ignorer les liens sans data-section (profil, login, etc.)
                        if (!section) return;
                        link.classList.toggle("active", section === id);
                        // Toggle FILL on the icon
                        const icon = link.querySelector(".material-symbols-outlined");
                        if (icon) {
                            icon.style.fontVariationSettings =
                                link.classList.contains("active")
                                    ? "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24"
                                    : "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                        }
                    });
                }
            });
        }, observerOptions);

        sections.forEach(section => observer.observe(section));

        // — Scroll-triggered animations —
        const animateElements = document.querySelectorAll(
            '.bento-card, .stat-mini, .featured-card__inner, ' +
            '.skills-bento__card, .project-article, .status-card, ' +
            '.channels-card, .contact-form-card, .synergy-card, ' +
            '.synergy-crosslink, .synergy-bridge__node'
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
            el.style.transform = 'translateY(24px)';
            el.style.transition =
                `opacity 0.5s ${i * 0.06}s cubic-bezier(0.16,1,0.3,1),` +
                `transform 0.5s ${i * 0.06}s cubic-bezier(0.16,1,0.3,1)`;
            fadeObserver.observe(el);
        });

        // ═══════════════════════════════════════
        //  LAZY LOAD RSS WIDGETS
        // ═══════════════════════════════════════
        const rssLoaded = new Set();
        const rssScripts = document.querySelectorAll('script.rss-lazy[data-src]');

        // Load a single RSS script
        function loadRssScript(script) {
            const src = script.dataset.src;
            if (rssLoaded.has(src)) return;
            rssLoaded.add(src);
            const s = document.createElement('script');
            s.src = src;
            s.type = 'text/javascript';
            s.async = true;
            script.parentNode.insertBefore(s, script);
            script.remove();
        }

        // Load all RSS scripts at once (used as fallback)
        function loadAllRss() {
            rssScripts.forEach(s => loadRssScript(s));
        }

        if ('IntersectionObserver' in window) {
            const rssObserver = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const script = entry.target.querySelector('script.rss-lazy[data-src]');
                        if (script) loadRssScript(script);
                        obs.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '200px' });

            rssScripts.forEach(script => {
                const parent = script.parentElement;
                if (parent) rssObserver.observe(parent);
            });
        } else {
            // No IntersectionObserver — load all immediately
            loadAllRss();
        }

        // Safety: load any remaining after 8s
        setTimeout(() => {
            document.querySelectorAll('script.rss-lazy[data-src]').forEach(s => loadRssScript(s));
        }, 8000);

        // — Smooth scroll for nav links (only for # anchor links)
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                // Only prevent default for same-page anchor links (#section)
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
                // For external links (profile.php, login.php, etc.), let the browser navigate normally
            });
        });

        // — Smooth scroll for CTA links —
        document.querySelectorAll('.hero__cta, .projects-cta__btn').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // — Live UTC time for contact section —
        function updateTime() {
            const now = new Date();
            const timeStr = now.toUTCString().split(' ')[4] + ' UTC';
            const el = document.getElementById('current-time');
            if (el) el.textContent = timeStr;
        }
        updateTime();
        setInterval(updateTime, 1000);

        // — Auto-refresh toutes les 60 secondes —
        const REFRESH_INTERVAL = 60;
        let countdown = REFRESH_INTERVAL;
        const countdownEl = document.getElementById('refresh-countdown');
        const progressBar = document.getElementById('refresh-bar');

        function doRefresh() {
            // Sauvegarder la position de scroll
            sessionStorage.setItem('bp_scroll', window.scrollY);
            sessionStorage.setItem('bp_hash', window.location.hash);
            location.reload();
        }

        // Restaurer le scroll si on revient d'un refresh
        const savedScroll = sessionStorage.getItem('bp_scroll');
        if (savedScroll) {
            const savedHash = sessionStorage.getItem('bp_hash');
            window.addEventListener('load', function() {
                window.scrollTo(0, parseInt(savedScroll));
                if (savedHash) history.replaceState(null, '', savedHash);
            });
            sessionStorage.removeItem('bp_scroll');
            sessionStorage.removeItem('bp_hash');
        }

        const refreshTimer = setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                clearInterval(refreshTimer);
                if (countdownEl) countdownEl.textContent = '⟳';
                doRefresh();
                return;
            }
            if (countdownEl) countdownEl.textContent = countdown + 's';
            if (progressBar) progressBar.style.width = ((REFRESH_INTERVAL - countdown) / REFRESH_INTERVAL * 100) + '%';
        }, 1000);

        // Annuler le refresh si l'utilisateur interagit
        let userActive = false;
        document.addEventListener('click', function() { userActive = true; });
        document.addEventListener('keydown', function() { userActive = true; });
        document.addEventListener('scroll', function() {
            userActive = true;
            // Repousser le refresh de 30s si l'utilisateur scroll
            if (countdown < 15) {
                countdown = Math.max(countdown, 15);
            }
        });

        // — Contact Form AJAX —
        const contactForm = document.querySelector('.contact-form-card__form');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const btn = contactForm.querySelector('.form__submit');
                const origHTML = btn.innerHTML;
                btn.innerHTML = '<span>TRANSMISSION_EN_COURS...</span><span class="material-symbols-outlined" style="animation:spin 1s linear infinite;">progress_activity</span>';
                btn.disabled = true;
                btn.style.opacity = '0.6';

                const formData = new FormData(contactForm);

                fetch('contact_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    let feedback = contactForm.querySelector('.contact-form-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'contact-form-feedback';
                        feedback.style.cssText = 'padding:12px 16px; border-radius:8px; margin-top:12px; font-family:var(--font-display); font-size:13px; letter-spacing:0.05em;';
                        contactForm.appendChild(feedback);
                    }
                    if (data.success) {
                        feedback.style.background = 'rgba(0,158,96,0.1)';
                        feedback.style.border = '1px solid rgba(0,158,96,0.3)';
                        feedback.style.color = 'var(--secondary-bright)';
                        feedback.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">check_circle</span>' + data.message;
                        contactForm.reset();
                    } else {
                        feedback.style.background = 'rgba(255,107,107,0.1)';
                        feedback.style.border = '1px solid rgba(255,107,107,0.3)';
                        feedback.style.color = '#ff6b6b';
                        feedback.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">error</span>' + data.message;
                    }
                    feedback.style.display = 'block';
                    setTimeout(() => { feedback.style.display = 'none'; }, 6000);
                })
                .catch(() => {
                    let feedback = contactForm.querySelector('.contact-form-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'contact-form-feedback';
                        feedback.style.cssText = 'padding:12px 16px; border-radius:8px; margin-top:12px; font-family:var(--font-display); font-size:13px; letter-spacing:0.05em;';
                        contactForm.appendChild(feedback);
                    }
                    feedback.style.background = 'rgba(255,107,107,0.1)';
                    feedback.style.border = '1px solid rgba(255,107,107,0.3)';
                    feedback.style.color = '#ff6b6b';
                    feedback.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:6px;">wifi_off</span>Erreur réseau. Veuillez réessayer.';
                    feedback.style.display = 'block';
                    setTimeout(() => { feedback.style.display = 'none'; }, 6000);
                })
                .finally(() => {
                    btn.innerHTML = origHTML;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            });
        }

        // — Newsletter AJAX —
        const nlForm = document.getElementById('newsletter-form');
        const nlMsg = document.getElementById('newsletter-message');
        if (nlForm) {
            nlForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const btn = nlForm.querySelector('.newsletter-card__submit');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span>ENVOI_EN_COURS...</span>';
                btn.disabled = true;

                const formData = new FormData(nlForm);
                formData.append('action', 'subscribe');

                fetch('newsletter_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    nlMsg.textContent = data.message;
                    nlMsg.className = 'newsletter-card__message ' + (data.success ? 'newsletter-card__message--success' : 'newsletter-card__message--error');
                    nlMsg.style.display = 'block';
                    if (data.success) {
                        nlForm.reset();
                    }
                })
                .catch(() => {
                    nlMsg.textContent = 'Erreur réseau. Veuillez réessayer.';
                    nlMsg.className = 'newsletter-card__message newsletter-card__message--error';
                    nlMsg.style.display = 'block';
                })
                .finally(() => {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                });
            });
        }

        // — CHAT WIDGET —
        const fabToggle = document.getElementById('fab-toggle');
        const chatPanel = document.getElementById('chat-panel');
        const fabIcon = document.getElementById('fab-icon');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const chatMessages = document.getElementById('chat-messages');
        let chatOpen = false;

        const botKnowledge = [
            { keywords: ['qui', 'es-tu', 'qui es', 'presente', 'presentation', 'bonjour', 'salut', 'hello', 'hey'],
              response: "Je suis DARK_VADOR, l'assistant de BLACK_PROTOCOL. Developpeur Full-Stack & Ethical Hacker base a Abidjan, Cote d'Ivoire. Cybersurite offensive, dev web, game dev et DevOps. Comment puis-je t'aider ?" },
            { keywords: ['competenc', 'skills', 'stack', 'techno', 'langage', 'technolog', 'tu sais'],
              response: "Mes competences :\n• Frontend : TypeScript, React, Next.js, Tailwind, Vue.js\n• Backend : Node.js, PHP, Python, Rust, Express\n• DevOps : Docker, Kubernetes, AWS, Terraform, Linux\n• Security : Kali Linux, Metasploit, Wireshark, Nmap\n• Database : MySQL, PostgreSQL, MongoDB, Redis\n• Game Dev : Unreal Engine 5, Unity, Godot" },
            { keywords: ['projet', 'realisation', 'portfolio', 'travaux', 'works'],
              response: "Mes projets recents :\n• Neural Breach Shield — Penetration testing autonome\n• Eburnie Chronicles — RPG narratif (Unreal 5)\n• Core Infra Automata — Orchestrateur multi-cloud\n• Nouchi Lexicon App — Dictionnaire Nouchi (Flutter)\n\nScrolle jusqu'a PROJECTS pour les decouvrir !" },
            { keywords: ['contact', 'contacter', 'email', 'message', 'joindre'],
              response: "Pour me contacter :\n• Formulaire en bas de page (section CONTACT)\n• Email : vianson50@gmail.com\n• GitHub, LinkedIn, Discord dans la section Contact\n\nReponse sous 24h !" },
            { keywords: ['cyber', 'securite', 'security', 'hacking', 'hacker', 'pentest'],
              response: "Cybersecurite = mon expertise principale :\n• Pentest (Kali Linux, Metasploit)\n• Analyse vulnabilites (Nmap, Wireshark)\n• Audit web (Burp Suite, OWASP)\n• Reverse engineering\n\nKali : 95% | Metasploit : 88% | Nmap : 94% | Wireshark : 90%" },
            { keywords: ['prix', 'tarif', 'cout', 'combien', 'devis', 'budget'],
              response: "Chaque projet est unique ! Les tarifs dependent de la complexite et des delais. Contacte-moi via le formulaire ou par email pour un devis personnalise." },
            { keywords: ['localisation', 'ou', 'ville', 'pays', 'abidjan', 'ivoire'],
              response: "Base a Abidjan, Cote d'Ivoire. Travail en remote mondial. Fuseau : GMT+0. Disponibilite : 99.9% uptime !" },
            { keywords: ['newsletter', 'abonner', 'news', 'mail'],
              response: "La newsletter BLACK_PROTOCOL : projets exclusifs, veille cyber et updates. Zero spam, desabonnement en 1 clic. Scrolle jusqu'a NEWSLETTER pour t'abonner !" },
            { keywords: ['game', 'jeu', 'gaming', 'unreal', 'unity', 'godot'],
              response: "Game dev = passion :\n• Unreal Engine 5 — C++, Niagara, Lumen\n• Unity — C#, Shader Graph\n• Godot — GDScript\n\nProjet phare : Eburnie Chronicles, RPG narratif avec generation procedurale." },
            { keywords: ['devops', 'cloud', 'docker', 'kubernetes', 'aws', 'deploy', 'deploie'],
              response: "Stack DevOps :\n• Docker & Kubernetes\n• AWS\n• Terraform\n• CI/CD GitHub Actions\n\nDeploiements zero-downtime !" },
            { keywords: ['synergie', 'synergy', 'crypto gaming', 'p2e', 'play-to-earn', 'play to earn', 'nft', 'skin', 'gamefi', 'token', 'blockchain gaming'],
              response: "Crypto × Gaming = la frontiere la plus excitante du web3 !\n\n• Play-to-Earn : jeux comme Axie Infinity, Illuvium, Pixels — les joueurs gagnent des tokens en jouant\n• NFT Gaming : assets in-game verifiables on-chain — armes, terrains, personnages\n• Skins Economy : le marche CS2 depasse $1.5B, les cosmestiques numeriques sont une classe d'actifs\n• GameFi Infra : Immutable X, Ronin, Polygon — des chaines optimisees gaming a 9000+ TPS\n\nDecouvre la section SYNERGY_MATRIX sur la page pour plus de details !" },
            { keywords: ['wallet', 'portefeuille', 'defi', 'smart contract audit', 'solidity', 'rug pull', 'flash loan', 'reentrancy', 'hack crypto', 'crypto hack', 'blockchain forens', 'on-chain', 'chainalysis', 'slither', 'mythril', 'bug bounty crypto', 'securite crypto', 'crypto securite', 'hacking crypto'],
              response: "Hacking × Crypto = le croisement le plus critique du web3 !\n\n• Securite des Wallets : hardware wallets, multi-sig, seed phrase management — $1.8B voles en 2024\n• Failles DeFi : flash loan attacks, oracle manipulation, reentrancy — $7770M perdus en 2024\n• Smart Contract Auditing : Slither, Mythril, Echidna — un bug = des millions perdus en secondes\n• Forensique Blockchain : traçage on-chain, AML/KYC — $3.4B traces en 2024\n\nCheck la section SYNERGY_MATRIX > CLUSTER_02 pour l'analyse complete !" },
            { keywords: ['serveur jeu', 'serveur game', 'ddos jeu', 'ddos game', 'vol compte', 'account steal', 'credential stuffing', 'sim swap', 'anti-cheat', 'cheat', 'bypass', 'eac', 'battleye', 'vanguard', 'esports securite', 'lan securite', 'game hack', 'hack game', 'game server security', 'steam vol', 'compte vole'],
              response: "Hacking × Gaming = la guerre invisible derriere ton ecran !\n\n• Securite des Serveurs : 80K+ attaques DDoS/jour, RCE dans les moteurs Source/Unreal, MITM client-serveur\n• Protection des Comptes : credential stuffing, OAuth hijacking, SIM swapping — 12M+ comptes voles/an\n• Anti-Cheat vs Bypass : EAC, BattlEye, Vanguard — kernel drivers, DMA cheats, memory manipulation\n• Infrastructure Esports : DDoS pendant les matchs live, LAN compromise, vol de strats\n\nExplore CLUSTER_03 dans SYNERGY_MATRIX pour l'analyse complete !" },
            { keywords: ['merci', 'thanks', 'super', 'cool', 'genial', 'parfait'],
              response: "Avec plaisir ! N'hesite pas si tu as d'autres questions. Tu peux aussi m'ecrire via le formulaire de contact en bas de page. A bientot !" },
        ];

        // ═══════════════════════════════════════
        //  OUTIL RÉDACTIONNEL v1.0
        //  Génère INTRODUCTION / DÉVELOPPEMENT / CONCLUSION (~200 mots)
        // ═══════════════════════════════════════
        const redacModeKeywords = ['redige', 'redaction', 'rédige', 'rédaction', 'rédiger', 'rediger', 'dissertation', 'composition', 'essai', 'trait le sujet', 'traite le sujet', 'traite', 'trait', 'parle de', 'fais un texte', 'fais une redaction', 'ecris sur', 'écris sur', 'article sur', 'texte sur', 'sujet :', 'sujet:'];

        function isRedacRequest(msg) {
            const lower = msg.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            for (const kw of redacModeKeywords) {
                if (lower.includes(kw)) return true;
            }
            // Detect pattern: "rédige [sujet]" or "traite [sujet]"
            if (/^(redige|rédige|traite|trait|parle|écris|ecris|fais)\s+(de|du|le|la|les|un|une|des|sur|d\'|l\')?\s*\w+/i.test(lower)) return true;
            return false;
        }

        function extractSujet(msg) {
            const lower = msg.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            // Remove trigger words to extract the subject
            let sujet = msg.replace(/^(redige|rédige|traite|trait|parle|écris|ecris|fais)\s+(moi\s+)?(de|du|le|la|les|un|une|des|sur|d'|l')?\s*(une\s+|un\s+)?(redaction|rédaction|dissertation|composition|essai|article|texte)\s*(sur|de|à\s+propos)?\s*/i, '').trim();
            sujet = sujet.replace(/^(sur|de|du|le|la|les|un|une|des|d'|l')\s+/i, '').trim();
            sujet = sujet.replace(/^sujet\s*[:\-]\s*/i, '').trim();
            sujet = sujet.replace(/["']/g, '').trim();
            if (!sujet || sujet.length < 2) sujet = msg;
            // Capitalize first letter
            return sujet.charAt(0).toUpperCase() + sujet.slice(1);
        }

        function generateRedaction(sujet) {
            const s = sujet.toLowerCase();
            const sMaj = sujet;

            // Templates dynamiques pour chaque section
            const intros = [
                `${sMaj} est un sujet qui suscite un interet croissant dans notre societe actuelle. Que ce soit dans le domaine technologique, social ou culturel, cette thematique occupe une place centrale dans les debats contemporains. Il est donc pertinent de s'interroger sur les enjeux et les perspectives qu'offre ${s}.`,
                `De tout temps, ${s} a constitue un element fondamental de notre environnement. Aujourd'hui plus que jamais, cette problematique merite une attention particuliere car elle touche a la fois aux aspects economiques, sociaux et culturels de notre vie quotidienne.`,
                `Dans un monde en perpetuelle evolution, ${s} represente un defi majeur mais aussi une opportunite considerable. Comprendre cette dynamique necessite une analyse approfondie des facteurs qui influencent ce domaine et des consequences qui en decoulent.`,
                `L'importance de ${s} ne cesse de croitre dans notre societe moderne. Face aux transformations rapides que connait notre epoque, il devient essentiel d'examiner ce sujet sous differents angles pour en saisir toute la portee et les implications.`,
                `${sMaj} constitue l'un des sujets les plus debattus de notre epoque. Entre progres et incertitudes, cette thematique soulève des questions fondamentales auxquelles il est indispensable d'apporter des reponses eclairees et reflechies.`
            ];

            const devParts = [
                `Sur le plan pratique, ${s} engendre des consequences directes sur le quotidien des individus. Les avancees recentes dans ce domaine ont demontre a la fois des benefices significatifs et des limites qu'il convient de ne pas ignorer. Les experts s'accordent a dire que une approche equilibree est necessaire pour tirer le meilleur parti de cette dynamique.`,
                `D'un point de vue analytique, ${s} presente plusieurs dimensions complementaires. D'une part, il offre des possibilites innovantes qui transforment les pratiques existantes. D'autre part, il pose des defis ethiques et techniques qui demandent une reflexion approfondie. Cette dualite est au coeur des discussions entre specialistes et grand public.`,
                `En examinant ${s} de plus pres, on constate que les impacts se manifestent a plusieurs niveaux. Au niveau individuel, les habitudes et les comportements evoluent en reponse aux changements apportes. Au niveau collectif, les structures sociales et economiques s'adaptent progressivement a cette nouvelle realite.`,
                `L'analyse de ${s} revele une realite complexe et multiforme. Les acteurs impliques dans ce domaine font face a des choix strategiques qui determineront l'avenir de cette thematique. La collaboration entre les differents parties prenantes apparait comme un facteur cle pour garantir un developpement harmonieux et durable.`,
                `Plusieurs etudes et temoignages illustrent l'impact reel de ${s} sur notre environnement. Les chiffres revelent une tendance a la hausse qui ne semble pas prete de s'inverser. Face a cette situation, il revient a chacun de s'informer et d'agir de maniere responsable et eclairee.`
            ];

            const conclusions = [
                `En definitive, ${s} est une realite que l'on ne peut ignorer. Entre opportunites et defis, l'essentiel est d'adopter une approche reflechie et proactive. L'avenir de ${s} depend de notre capacite collective a innover tout en preservant les valeurs fondamentales qui nous unissent.`,
                `Pour conclure, ${s} occupe une place incontournable dans notre monde moderne. Les enjeux identifies tout au long de cette reflexion montrent qu'il est imperative de rester vigilant et adaptable. C'est a cette condition que nous pourrons saisir les opportunites qu'offre ${s} tout en maitrisant les risques associes.`,
                `En somme, ${s} nous rappelle que le progres est un processus continu qui exige lucidite et engagement. Les defis sont nombreux mais les perspectives sont prometteuses. A nous de faire les choix qui faconneront l'avenir de ${s} de maniere positive et constructive.`,
                `En conclusion, l'etude de ${s} nous conduit a une prise de conscience essentielle : chaque avancee comporte sa part d'opportunites et de responsabilites. L'important n'est pas de tout maitriser mais de comprendre les enjeux pour agir de maniere eclairee et responsable.`,
                `Pour resumer, ${s} est bien plus qu'une simple thematique : c'est un levier de transformation qui faconne notre avenir. En cultivant la curiosite et l'esprit critique, nous serons mieux prepares a affronter les defis et a exploiter le potentiel qu'offre ce domaine.`
            ];

            // Pick random sections
            const intro = intros[Math.floor(Math.random() * intros.length)];
            const dev = devParts[Math.floor(Math.random() * devParts.length)];
            const concl = conclusions[Math.floor(Math.random() * conclusions.length)];

            // Count words
            const fullText = intro + ' ' + dev + ' ' + concl;
            const wordCount = fullText.split(/\s+/).filter(w => w.length > 0).length;

            return `✦ OUTIL RÉDACTIONNEL v1.0 ✦
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Sujet : ${sMaj} (${wordCount} mots)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━

▸ INTRODUCTION
${intro}

▸ DÉVELOPPEMENT
${dev}

▸ CONCLUSION
${concl}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Outil Rédactionnel v1.0 — BLACK_PROTOCOL`;
        }

        function getBotResponse(msg) {
            // Check if it's a redaction request first
            if (isRedacRequest(msg)) {
                const sujet = extractSujet(msg);
                return generateRedaction(sujet);
            }

            const lower = msg.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            for (const entry of botKnowledge) {
                for (const kw of entry.keywords) {
                    if (lower.includes(kw)) return entry.response;
                }
            }
            return "Interessant ! Pas de reponse specifique pour ca. Essaie de me demander : mes competences, mes projets, la cybersecurite, le DevOps, le game dev, ou comment me contacter.\n\n💡 Astuce : Ecrivez \"Rédige [votre sujet]\" pour utiliser l'Outil Rédactionnel v1.0 !";
        }

        function addMessage(text, type) {
            const div = document.createElement('div');
            div.className = 'chat-msg chat-msg--' + type;
            const time = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
            div.innerHTML = '<div class="chat-msg__bubble">' + text.replace(/\n/g, '<br>') + '</div><span class="chat-msg__time">' + time + '</span>';
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            const div = document.createElement('div');
            div.className = 'chat-msg chat-msg--bot';
            div.id = 'typing-indicator';
            div.innerHTML = '<div class="chat-msg__bubble chat-msg__typing"><span></span><span></span><span></span></div>';
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTyping() {
            const el = document.getElementById('typing-indicator');
            if (el) el.remove();
        }

        fabToggle.addEventListener('click', () => {
            chatOpen = !chatOpen;
            chatPanel.classList.toggle('active', chatOpen);
            fabIcon.textContent = chatOpen ? 'close' : 'chat_bubble';
            if (chatOpen && chatMessages.children.length === 0) {
                setTimeout(() => {
                    addMessage("Bienvenue sur BLACK_PROTOCOL ! Je suis DARK_VADOR, ton assistant. Pose-moi une question ou utilise les suggestions ci-dessous.", 'bot');
                }, 400);
            }
        });

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if (!msg) return;
            addMessage(msg, 'user');
            chatInput.value = '';
            showTyping();
            setTimeout(() => {
                removeTyping();
                addMessage(getBotResponse(msg), 'bot');
            }, 600 + Math.random() * 800);
        });

        document.querySelectorAll('.chat-suggestion').forEach(btn => {
            btn.addEventListener('click', () => {
                chatInput.value = btn.getAttribute('data-msg');
                chatForm.dispatchEvent(new Event('submit'));
            });
        });
    </script>

    <script>
        function toggleQuickPublish() {
            const body = document.getElementById('quickPublishBody');
            const btn = document.querySelector('.blog-pub__quick-btn');
            if (!body) return;
            if (body.style.display === 'none') {
                body.style.display = 'block';
                btn.style.display = 'none';
                body.querySelector('input[name="topic"]').focus();
            } else {
                body.style.display = 'none';
                btn.style.display = 'inline-flex';
            }
        }

        // ═══════════════════════════════════════
        //  CRYPTO MARKET WIDGET — CoinGecko API
        // ═══════════════════════════════════════
        /* DISABLED — API calls removed for performance
        (function() {
            const API = 'includes/crypto_api.php';
            let marketData = null;
            let chartData = null;
            let activeCoin = 'bitcoin';
            let activeDays = 1;

            // ── Helpers ──
            function fmt$(n) {
                if (n == null) return '—';
                if (n >= 1e12) return '$' + (n/1e12).toFixed(2) + 'T';
                if (n >= 1e9) return '$' + (n/1e9).toFixed(2) + 'B';
                if (n >= 1e6) return '$' + (n/1e6).toFixed(2) + 'M';
                if (n >= 1e3) return '$' + (n/1e3).toFixed(2) + 'K';
                return '$' + n.toFixed(2);
            }
            function fmtPrice(n) {
                if (n == null) return '—';
                if (n >= 1) return '$' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                if (n >= 0.01) return '$' + n.toFixed(4);
                return '$' + n.toFixed(6);
            }
            function fmtPct(n) {
                if (n == null) return '—';
                const sign = n >= 0 ? '+' : '';
                return sign + n.toFixed(2) + '%';
            }
            function pctColor(n) {
                if (n == null) return 'var(--on-surface-variant)';
                return n >= 0 ? 'var(--secondary-bright)' : '#ff6b6b';
            }

            // ── Build sparkline SVG path ──
            function sparklinePath(data, w, h) {
                if (!data || data.length < 2) return '';
                const min = Math.min(...data);
                const max = Math.max(...data);
                const range = max - min || 1;
                const step = w / (data.length - 1);
                let d = '';
                data.forEach((v, i) => {
                    const x = i * step;
                    const y = h - ((v - min) / range) * h;
                    d += (i === 0 ? 'M' : 'L') + x.toFixed(1) + ',' + y.toFixed(1);
                });
                return d;
            }

            // ── Build chart SVG ──
            function renderChart(prices) {
                if (!prices || prices.length < 2) return;
                const svgW = 800, svgH = 260, padY = 10;
                const vals = prices.map(p => p[1]);
                const minV = Math.min(...vals);
                const maxV = Math.max(...vals);
                const range = maxV - minV || 1;
                const step = svgW / (vals.length - 1);

                let lineD = '', areaD = '';
                const pts = vals.map((v, i) => {
                    const x = i * step;
                    const y = padY + (svgH - 2*padY) * (1 - (v - minV) / range);
                    return {x, y, v, t: prices[i][0]};
                });

                pts.forEach((p, i) => {
                    lineD += (i === 0 ? 'M' : 'L') + p.x.toFixed(1) + ',' + p.y.toFixed(1);
                });

                // Area fill
                areaD = lineD + ` L${svgW},${svgH} L0,${svgH} Z`;

                const isUp = vals[vals.length - 1] >= vals[0];
                const lineColor = isUp ? '#61dd98' : '#ff6b6b';
                const gradId = isUp ? 'chartGradUp' : 'chartGradDown';

                document.getElementById('chart-line').setAttribute('d', lineD);
                document.getElementById('chart-line').setAttribute('stroke', lineColor);
                document.getElementById('chart-area').setAttribute('d', areaD);
                document.getElementById('chart-area').setAttribute('fill', `url(#${gradId})`);

                // Store points for tooltip
                window.__chartPts = pts;
            }

            // ── Render ticker table ──
            function renderTicker(coins) {
                const body = document.getElementById('crypto-ticker-body');
                if (!body || !coins) return;

                let html = '';
                coins.forEach((c, idx) => {
                    const sparkSvg = c.sparkline && c.sparkline.length > 2
                        ? `<svg viewBox="0 0 80 28" class="crypto-ticker__spark"><path d="${sparklinePath(c.sparkline, 80, 24)}" fill="none" stroke="${pctColor(c.change_24h)}" stroke-width="1.5"/></svg>`
                        : '<span style="color:rgba(255,255,255,0.2);">—</span>';

                    html += `<div class="crypto-ticker__row" data-coin="${c.id}">
                        <span class="crypto-ticker__col crypto-ticker__col--rank">${idx+1}</span>
                        <span class="crypto-ticker__col crypto-ticker__col--name">
                            <img src="${c.image}" alt="${c.symbol}" class="crypto-ticker__icon" loading="lazy" onerror="this.style.display='none'">
                            <span>${c.symbol}</span>
                        </span>
                        <span class="crypto-ticker__col crypto-ticker__col--price">${fmtPrice(c.price)}</span>
                        <span class="crypto-ticker__col crypto-ticker__col--change" style="color:${pctColor(c.change_24h)}">${fmtPct(c.change_24h)}</span>
                        <span class="crypto-ticker__col crypto-ticker__col--spark">${sparkSvg}</span>
                        <span class="crypto-ticker__col crypto-ticker__col--mcap">${fmt$(c.market_cap)}</span>
                    </div>`;
                });

                body.innerHTML = html;

                // Click rows to load chart
                body.querySelectorAll('.crypto-ticker__row').forEach(row => {
                    row.style.cursor = 'pointer';
                    row.addEventListener('click', () => {
                        activeCoin = row.dataset.coin;
                        loadChart();
                        updateCoinTabs();
                    });
                });
            }

            // ── Render coin tabs ──
            function renderCoinTabs(coins) {
                const container = document.getElementById('crypto-coin-tabs');
                if (!container) return;
                const top5 = coins.slice(0, 5);
                let html = '';
                top5.forEach(c => {
                    html += `<button class="crypto-chart__coin-tab ${c.id === activeCoin ? 'active' : ''}" data-coin="${c.id}">
                        <img src="${c.image}" alt="${c.symbol}" class="crypto-chart__coin-icon" loading="lazy" onerror="this.style.display='none'">
                        <span>${c.symbol}</span>
                    </button>`;
                });
                container.innerHTML = html;

                container.querySelectorAll('.crypto-chart__coin-tab').forEach(btn => {
                    btn.addEventListener('click', () => {
                        activeCoin = btn.dataset.coin;
                        loadChart();
                        updateCoinTabs();
                    });
                });
            }

            function updateCoinTabs() {
                document.querySelectorAll('.crypto-chart__coin-tab').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.coin === activeCoin);
                });
            }

            // ── Load market data ──
            async function loadMarket() {
                try {
                    const res = await fetch(API + '?action=market');
                    const data = await res.json();
                    if (data.error) throw new Error(data.error);
                    marketData = data.coins;

                    renderTicker(marketData);
                    renderCoinTabs(marketData);

                    document.getElementById('crypto-updated').textContent =
                        'MAJ: ' + new Date(data.updated).toLocaleTimeString('fr-FR');
                    document.getElementById('crypto-status').textContent = 'LIVE';
                    document.getElementById('crypto-loading')?.remove();

                    // Load chart for active coin
                    loadChart();
                } catch(e) {
                    console.warn('CoinGecko market error:', e);
                    const loading = document.getElementById('crypto-loading');
                    if (loading) loading.innerHTML = '<span style="color:#ff6b6b;">API indisponible — données en cache</span>';
                    document.getElementById('crypto-status').textContent = 'OFFLINE';
                }
            }

            // ── Load chart data ──
            async function loadChart() {
                try {
                    const res = await fetch(API + '?action=chart&id=' + activeCoin + '&days=' + activeDays);
                    const data = await res.json();
                    if (data.error) throw new Error(data.error);
                    chartData = data.prices;
                    renderChart(chartData);
                } catch(e) {
                    console.warn('CoinGecko chart error:', e);
                }
            }

            // ── Load global stats ──
            async function loadGlobal() {
                try {
                    const res = await fetch(API + '?action=global');
                    const data = await res.json();
                    if (data.error) throw new Error(data.error);
                    document.getElementById('cg-marketcap').textContent = fmt$(data.total_market_cap_usd);
                    document.getElementById('cg-volume').textContent = fmt$(data.total_volume_usd);
                    document.getElementById('cg-btcdom').textContent = data.btc_dominance.toFixed(1) + '%';
                    const ch = document.getElementById('cg-change');
                    ch.textContent = fmtPct(data.market_cap_change_24h);
                    ch.style.color = pctColor(data.market_cap_change_24h);
                } catch(e) {
                    console.warn('CoinGecko global error:', e);
                }
            }

            // ── Time range buttons ──
            document.querySelectorAll('.crypto-chart__time-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.crypto-chart__time-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    activeDays = parseInt(btn.dataset.days);
                    loadChart();
                });
            });

            // ── Chart hover tooltip ──
            const svgEl = document.getElementById('crypto-chart-svg');
            const tooltipEl = document.getElementById('crypto-tooltip');
            const crosshair = document.getElementById('chart-crosshair');

            if (svgEl && tooltipEl) {
                svgEl.addEventListener('mousemove', function(e) {
                    const rect = svgEl.getBoundingClientRect();
                    const relX = (e.clientX - rect.left) / rect.width;
                    const pts = window.__chartPts;
                    if (!pts || pts.length < 2) return;

                    const idx = Math.min(Math.floor(relX * pts.length), pts.length - 1);
                    const p = pts[idx];
                    if (!p) return;

                    crosshair.setAttribute('x1', p.x);
                    crosshair.setAttribute('x2', p.x);
                    crosshair.style.display = '';

                    document.getElementById('tooltip-price').textContent = fmtPrice(p.v);
                    document.getElementById('tooltip-date').textContent = new Date(p.t).toLocaleDateString('fr-FR', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'});
                    tooltipEl.style.display = 'block';

                    const px = (p.x / 800) * 100;
                    tooltipEl.style.left = px > 70 ? (px - 15) + '%' : (px + 2) + '%';
                    tooltipEl.style.top = Math.min(p.y / 260 * 100, 80) + '%';
                });

                svgEl.addEventListener('mouseleave', function() {
                    tooltipEl.style.display = 'none';
                    crosshair.style.display = 'none';
                });
            }

            // ── Init ──
            loadGlobal();
            loadMarket();
            // Auto-refresh every 5 min
            setInterval(() => { loadMarket(); loadGlobal(); }, 300000);
        })();
        */

        function copyAddr(type) {
            const el = document.getElementById('addr-' + type);
            if (!el) return;
            const addr = el.textContent;
            navigator.clipboard.writeText(addr).then(() => {
                const btn = el.parentElement.querySelector('.crypto-item__copy');
                const orig = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px;">check</span>';
                btn.style.color = 'var(--secondary-bright)';
                btn.style.borderColor = 'rgba(0,158,96,0.3)';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.style.color = '';
                    btn.style.borderColor = '';
                }, 2000);
            });
        }
    </script>

    <!-- Preload critical images -->
    <link rel="preload" as="image" href="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c">

    <!-- ================================
         SYNERGY DETAIL MODAL
         ================================ -->
    <div class="synergy-detail-overlay" id="synergy-detail-overlay">
        <div class="synergy-detail" id="synergy-detail">
            <div class="synergy-detail__header">
                <div class="synergy-detail__header-left">
                    <span class="synergy-detail__cluster" id="sd-cluster"></span>
                    <span class="synergy-detail__title" id="sd-title"></span>
                </div>
                <button class="synergy-detail__close" id="synergy-detail-close">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>
            <div class="synergy-detail__body" id="synergy-detail-body"></div>
        </div>
    </div>

    <script>
    (function() {
        const overlay = document.getElementById('synergy-detail-overlay');
        const detail = document.getElementById('synergy-detail');
        const sdCluster = document.getElementById('sd-cluster');
        const sdTitle = document.getElementById('sd-title');
        const sdBody = document.getElementById('synergy-detail-body');
        const sdClose = document.getElementById('synergy-detail-close');

        // ═══════ SYNERGY DATA ═══════
        const synergyData = {
            // ── CLUSTER 1: CRYPTO × GAMING ──
            'p2e': {
                cluster: 'CLUSTER_01 // CRYPTO × GAMING',
                title: 'PLAY-TO-EARN',
                desc: "L'écosystème Play-to-Earn (P2E) redéfinit la relation entre gameplay et économie numérique. Des titres pionniers comme Axie Infinity ont prouvé que les joueurs pouvaient générer des revenus réels, tandis que la génération actuelle — Illuvium, Pixels, Pirate Nation — intègre des mécaniques tokenomiques plus durables. Le modèle évolue du 'play-to-earn' pur vers le 'play-and-earn', où la qualité du gameplay prime sur la simple rentabilité.\n\nLes défis restent majeurs : inflation tokenique, dépendance aux nouveaux entrants pour soutenir l'économie, et réglementation croissante. Mais les infrastructure progressent — Ronin, Immutable X et Beam permettent des transactions à moins de $0.01 avec des TPS gaming-optimized de 9 000+.",
                metrics: [
                    { label: 'Marché P2E', value: '$4.2B+', color: 'var(--primary)' },
                    { label: 'Joueurs actifs', value: '2.5M+', color: 'var(--secondary-bright)' },
                    { label: 'DAU moyen', value: '850K', color: 'var(--primary)' },
                    { label: 'Croissance YoY', value: '+38%', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'code', name: 'Unity SDK' },
                    { icon: 'code', name: 'Unreal SDK' },
                    { icon: 'developer_board', name: 'Immutable X' },
                    { icon: 'token', name: 'Ronin Chain' },
                    { icon: 'developer_board', name: 'Beam Network' },
                    { icon: 'analytics', name: 'Footprint Analytics' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'DappRadar P2E Rankings', url: 'https://dappradar.com/rankings/category/games' },
                    { icon: 'open_in_new', label: 'PlaytoEarn.net', url: 'https://playtoearn.net' }
                ]
            },
            'nft': {
                cluster: 'CLUSTER_01 // CRYPTO × GAMING',
                title: 'NFT DANS LE GAMING',
                desc: "Les NFT gaming transforment les items in-game en propriété numérique réelle et vérifiable. Contrairement aux cosmétiques traditionnels verrouillés dans un écosystème fermé, les NFT gaming permettent le trade cross-game, la vérification de rarete on-chain, et la composition composable d'assets.\n\nDes plateformes comme Gods Unchained et Parallel démontrent que les TCG (Trading Card Games) sont un cas d'usage idéal : chaque carte est un NFT unique avec une historique de propriété transparente. Les standards ERC-1155 permettent des économies de gas significatives pour les collections massives. L'enjeu majeur reste l'interopérabilité — le rêve d'utiliser un item dans plusieurs jeux reste techniquement complexe mais progresse via des standards comme ERC-6551 (Token Bound Accounts).",
                metrics: [
                    { label: 'Volume NFT Gaming', value: '$1.8B', color: 'var(--primary)' },
                    { label: 'Collections actives', value: '850+', color: 'var(--secondary-bright)' },
                    { label: 'Floor avg', value: '$45', color: 'var(--primary)' },
                    { label: 'Joueurs NFT', value: '1.2M+', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'code', name: 'ERC-721' },
                    { icon: 'code', name: 'ERC-1155' },
                    { icon: 'code', name: 'ERC-6551' },
                    { icon: 'token', name: 'OpenSea' },
                    { icon: 'token', name: 'Magic Eden' },
                    { icon: 'developer_board', name: 'Enjin SDK' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'NFT Gaming Market', url: 'https://nftgaming.com' },
                    { icon: 'open_in_new', label: 'OpenSea Gaming', url: 'https://opensea.io/category/gaming' }
                ]
            },
            'skins': {
                cluster: 'CLUSTER_01 // CRYPTO × GAMING',
                title: 'ÉCONOMIE DES SKINS',
                desc: "L'économie des skins dépasse celle de nombreux pays. Le marché CS2 seul représente plus de $1.5 milliard en volume de transactions annuelles. Des items comme le 'Dragon Lore' AWP se vendent à plus de $1.5M. Les skins sont devenus une classe d'actifs numérique à part entière, avec des plateformes de trading spécialisées (Buff163, Skinport, DMarket).\n\nValorant et Fortnite ont adapté le modèle avec des stores propriétaires, mais le principe reste identique : la rareté artificielle et le statut social créent une demande structurelle. Les marchés secondaires non-officiels posent des défis réglementaires et de sécurité — les scams par trade et les sites de gambling illégal restent un problème majeur pour l'industrie.",
                metrics: [
                    { label: 'Marché CS2', value: '$1.5B+', color: 'var(--primary)' },
                    { label: 'Transactions/jour', value: '500K+', color: 'var(--secondary-bright)' },
                    { label: 'Skin le plus cher', value: '$1.5M', color: '#a78bfa' },
                    { label: 'Marchés actifs', value: '25+', color: 'var(--primary)' }
                ],
                tools: [
                    { icon: 'storefront', name: 'Buff163' },
                    { icon: 'storefront', name: 'Skinport' },
                    { icon: 'storefront', name: 'DMarket' },
                    { icon: 'analytics', name: 'CSFloat' },
                    { icon: 'analytics', name: 'SteamAnalyst' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'CS2 Market Stats', url: 'https://csfloat.com' },
                    { icon: 'open_in_new', label: 'Skinport', url: 'https://skinport.com' }
                ]
            },
            'infra': {
                cluster: 'CLUSTER_01 // CRYPTO × GAMING',
                title: 'GAMEFI INFRASTRUCTURE',
                desc: "Les chaînes gaming-spécifiques construisent les fondations techniques de la prochaine génération de jeux décentralisés. Immutable X offre des transactions gratuites avec la sécurité d'Ethereum L2. Ronin, développé par Sky Mavis (Axie Infinity), atteint 9 000+ TPS optimisés gaming. Polygon CDK permet de lancer des chaînes gaming custom.\n\nLes SDK Web3 s'intègrent directement dans les moteurs de jeu : Unity Web3 SDK, Unreal Marketplace plugins, Godot blockchain libraries. Les développeurs peuvent mint des NFTs, gérer des wallets intégrés et implémenter des économies tokenomiques sans quitter leur engine. Le défi reste l'UX — les gamers ne veulent pas gérer des seed phrases.",
                metrics: [
                    { label: 'TPS Gaming', value: '9 000+', color: 'var(--primary)' },
                    { label: 'Frais/gas', value: '~$0.01', color: 'var(--secondary-bright)' },
                    { label: 'L2 Chains gaming', value: '12+', color: 'var(--primary)' },
                    { label: 'SDKs actifs', value: '30+', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'developer_board', name: 'Immutable X' },
                    { icon: 'developer_board', name: 'Ronin' },
                    { icon: 'developer_board', name: 'Polygon CDK' },
                    { icon: 'developer_board', name: 'Beam' },
                    { icon: 'code', name: 'Unity Web3 SDK' },
                    { icon: 'code', name: 'Thirdweb' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Immutable', url: 'https://immutable.com' },
                    { icon: 'open_in_new', label: 'Ronin Chain', url: 'https://roninchain.com' }
                ]
            },

            // ── CLUSTER 2: HACKING × CRYPTO ──
            'wallet-sec': {
                cluster: 'CLUSTER_02 // HACKING × CRYPTO',
                title: 'SÉCURITÉ DES WALLETS',
                desc: "Un wallet compromis = des fonds perdus à jamais. Les vecteurs d'attaque sont multiples : phishing calqué sur des interfaces légitimes (MetaMask, Phantom), keyloggers ciblant les seed phrases, supply chain attacks sur les bibliothèques crypto, et exploits zero-day dans les logiciels de wallet.\n\n$1.8 milliard volé en 2024 via des attaques de wallets. La défense en profondeur est obligatoire : hardware wallets (Ledger, Trezor) pour le stockage cold, multi-signature (Gnosis Safe) pour les treasury, et air-gapped devices pour les opérations critiques. Les seed phrases doivent être stockées offline, jamais dans un clipboard ou un gestionnaire de mots de passe cloud.",
                metrics: [
                    { label: 'Fonds volés 2024', value: '$1.8B', color: '#ff6b6b' },
                    { label: 'Attaques wallet/jour', value: '1 200+', color: 'var(--primary)' },
                    { label: 'Phishing domains', value: '15K+/mois', color: '#ff6b6b' },
                    { label: 'Récupération', value: '~4%', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'lock', name: 'Ledger' },
                    { icon: 'lock', name: 'Trezor' },
                    { icon: 'shield', name: 'Gnosis Safe' },
                    { icon: 'bug_report', name: 'Revoke.cash' },
                    { icon: 'shield', name: 'Wallet Guard' },
                    { icon: 'search', name: 'Token Sniffer' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Revoke.cash', url: 'https://revoke.cash' },
                    { icon: 'open_in_new', label: 'Scam Sniffer', url: 'https://scamsniffer.io' }
                ]
            },
            'defi-vuln': {
                cluster: 'CLUSTER_02 // HACKING × CRYPTO',
                title: 'FAILLES DEFI',
                desc: "Les protocoles DeFi accumulent $770M en hacks en 2024, via des vecteurs d'attaque bien identifiés : flash loan attacks manipulant les oracles de prix, reentrancy exploits profitant de l'exécution asynchrone des smart contracts, et rug pulls où les équipes malveillantes drainent la liquidité.\n\nLes attaques les plus sophistiquées combinent plusieurs vecteurs : flash loan + oracle manipulation + governance exploit en une seule transaction. L'analyse post-mortem révèle que 80%+ des hacks auraient pu être évités par un audit rigoureux. Les bug bounties DeFi sur Immunefi atteignent en moyenne $50K pour une vulnerabilité critique, avec des primes exceptionnelles dépassant $10M.",
                metrics: [
                    { label: 'Hacks DeFi 2024', value: '$770M', color: '#ff6b6b' },
                    { label: 'Protocoles touchés', value: '180+', color: 'var(--primary)' },
                    { label: 'Flash loan attacks', value: '43%', color: '#ff6b6b' },
                    { label: 'Bug bounty avg', value: '$50K', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'bug_report', name: 'Slither' },
                    { icon: 'bug_report', name: 'Mythril' },
                    { icon: 'bug_report', name: 'Echidna' },
                    { icon: 'bug_report', name: 'Foundry' },
                    { icon: 'shield', name: 'Immunefi' },
                    { icon: 'analytics', name: 'DeFiLlama' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Immunefi Bug Bounty', url: 'https://immunefi.com' },
                    { icon: 'open_in_new', label: 'DeFi Hack Labs', url: 'https://github.com/SunWeb3Sec/DeFiHackLabs' }
                ]
            },
            'audit': {
                cluster: 'CLUSTER_02 // HACKING × CRYPTO',
                title: 'SMART CONTRACT AUDITING',
                desc: "Avant tout déploiement critique, un audit rigoureux est obligatoire. La stack d'audit moderne combine analyse statique (Slither), symbolic execution (Mythril), fuzzing (Echidna, Medusa) et vérification formelle (Certora Prover). 420+ bugs critiques détectés en 2024 avant déploiement.\n\nLes audits se déroulent en phases : review du code et de l'architecture, identification des attack surfaces, tests de propriétés invariantes, et rapport détaillé avec sévérité (Critical/High/Medium/Low/Informational). Les top firms (Trail of Bits, OpenZeppelin, Spearbit) facturent $50K-$500K par audit. Le marché des audits crypto représente $200M+ annuellement.",
                metrics: [
                    { label: 'Bugs critiques/an', value: '420+', color: '#ff6b6b' },
                    { label: 'Bounty moyen', value: '$50K', color: 'var(--secondary-bright)' },
                    { label: 'Marché audits', value: '$200M+', color: 'var(--primary)' },
                    { label: 'Firms actives', value: '60+', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'bug_report', name: 'Slither' },
                    { icon: 'bug_report', name: 'Mythril' },
                    { icon: 'bug_report', name: 'Echidna' },
                    { icon: 'bug_report', name: 'Certora' },
                    { icon: 'bug_report', name: 'Foundry' },
                    { icon: 'code', name: '4naly3er' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Secureum Bootcamp', url: 'https://secureum.xyz' },
                    { icon: 'open_in_new', label: 'Damn Vulnerable DeFi', url: 'https://damnvulnerabledefi.xyz' }
                ]
            },
            'forensics': {
                cluster: 'CLUSTER_02 // HACKING × CRYPTO',
                title: 'FORENSIQUE BLOCKCHAIN',
                desc: "La blockchain est un registre public immuable — parfait pour le traçage. Les outils de forensique on-chain (Chainalysis, Elliptic, TRM Labs) permettent de suivre les flux de fonds illicites à travers des mixers, des DEX aggregators et des cross-chain bridges.\n\nEn 2024, $3.4 milliard de fonds illicites ont été tracés, avec un taux de récupération de 18% — en progression constante grâce à la coopération entre firmes de forensique, exchanges centralisés et autorités. Les techniques avancées incluent : clustering heuristique d'adresses, analyse temporelle des transactions, et machine learning pour détecter les patterns de blanchiment.",
                metrics: [
                    { label: 'Fonds tracés 2024', value: '$3.4B', color: 'var(--primary)' },
                    { label: 'Récupérés', value: '18%', color: 'var(--secondary-bright)' },
                    { label: 'Mixers sanctionnés', value: '12', color: '#ff6b6b' },
                    { label: 'Exchanges cooperants', value: '150+', color: 'var(--primary)' }
                ],
                tools: [
                    { icon: 'travel_explore', name: 'Chainalysis' },
                    { icon: 'travel_explore', name: 'Elliptic' },
                    { icon: 'travel_explore', name: 'TRM Labs' },
                    { icon: 'analytics', name: 'Dune Analytics' },
                    { icon: 'analytics', name: 'Nansen' },
                    { icon: 'search', name: 'Etherscan' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Chainalysis Reports', url: 'https://chainalysis.com/reports' },
                    { icon: 'open_in_new', label: 'Etherscan', url: 'https://etherscan.io' }
                ]
            },

            // ── CLUSTER 3: HACKING × GAMING ──
            'game-server': {
                cluster: 'CLUSTER_03 // HACKING × GAMING',
                title: 'SÉCURITÉ DES SERVEURS DE JEUX',
                desc: "Les serveurs de jeux subissent plus de 80 000 attaques DDoS par jour — un chiffre qui ne cesse de croître avec l'explosion de l'esport et du streaming. Les attaques vont du volumétrique classique (UDP flood, amplification) aux attaques layer 7 sophistiquées qui ciblent directement les game servers.\n\nAu-delà du DDoS, les failles RCE (Remote Code Execution) dans les moteurs Source et Unreal permettent la compromission totale des serveurs. Les attaques MITM sur les communications client-serveur permettent le wallhack et l'espionnage de données sensibles. Le coût d'une heure de downtime pour un jeu AAA est estimé à $150K.",
                metrics: [
                    { label: 'DDoS/jour', value: '80K+', color: '#ff6b6b' },
                    { label: 'Coût downtime/h', value: '$150K', color: 'var(--primary)' },
                    { label: 'RCE découvertes', value: '23/2024', color: '#ff6b6b' },
                    { label: 'Serveurs hardenés', value: '45%', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'shield', name: 'Cloudflare Spectrum' },
                    { icon: 'shield', name: 'Akamai Prolexic' },
                    { icon: 'dns', name: 'AWS Shield' },
                    { icon: 'bug_report', name: 'Nmap' },
                    { icon: 'bug_report', name: 'Burp Suite' },
                    { icon: 'shield', name: 'Valve VAC' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Cloudflare Gaming', url: 'https://cloudflare.com/gaming' },
                    { icon: 'open_in_new', label: 'GameSec Research', url: 'https://gamesec.xyz' }
                ]
            },
            'account-sec': {
                cluster: 'CLUSTER_03 // HACKING × GAMING',
                title: 'PROTECTION DES COMPTES',
                desc: "12 millions de comptes gaming volés par an — un pactole pour les cybercriminels. Le credential stuffing exploite les bases de données leaked (Have I Been Pwned recense 12+ billion de credentials) pour tester automatiquement des combinaisons sur Steam, Epic Games, PlayStation Network.\n\nL'OAuth hijacking cible les flux d'authentification sociale (Login with Google/Steam/Discord). Le SIM swapping permet de bypasser la 2FA SMS. Un inventaire CS2 moyen vaut $340, mais les comptes premium dépassent largement les $10K. La protection multicouche est essentielle : 2FA hardware (YubiKey), OAuth sécure, et monitoring des sessions actives.",
                metrics: [
                    { label: 'Comptes volés/an', value: '12M+', color: '#ff6b6b' },
                    { label: 'Valeur avg/inventaire', value: '$340', color: 'var(--primary)' },
                    { label: 'Leak databases', value: '12B+ creds', color: '#ff6b6b' },
                    { label: 'SIM swap coût', value: '$1.2B/an', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'lock', name: 'YubiKey' },
                    { icon: 'shield', name: 'Steam Guard' },
                    { icon: 'shield', name: 'Epic 2FA' },
                    { icon: 'search', name: 'HIBP API' },
                    { icon: 'bug_report', name: 'Have I Been Pwned' },
                    { icon: 'lock', name: 'Authy' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'Have I Been Pwned', url: 'https://haveibeenpwned.com' },
                    { icon: 'open_in_new', label: 'Steam Security', url: 'https://help.steampowered.com/wizard/HelpWithLogin' }
                ]
            },
            'anticheat': {
                cluster: 'CLUSTER_03 // HACKING × GAMING',
                title: 'ANTI-CHEAT VS BYPASS',
                desc: "La guerre entre anti-cheats kernel-level et les développeurs de cheats est un bras de fer technique constant. EAC (Easy Anti-Cheat), BattlEye et Vanguard (Riot) opèrent au niveau ring 0 du kernel Windows pour détecter les memory manipulation et code injection.\n\nLes cheats DMA (Direct Memory Access) utilisent du hardware dédié (PCIe cards) pour lire la mémoire du PC cible sans que le kernel anti-cheat puisse le détecter — un marché underground estimé à $800M+. La réponse : machine learning behavioral analysis, hardware fingerprinting avancé, et trusted execution environments (Intel SGX). Le marché des cheats légitimes et les bannissements dépassent 2.5 millions par an.",
                metrics: [
                    { label: 'Bans/an', value: '2.5M+', color: 'var(--secondary-bright)' },
                    { label: 'Marché cheats', value: '$800M+', color: 'var(--primary)' },
                    { label: 'DMA cheats prix', value: '$200-$2K', color: '#ff6b6b' },
                    { label: 'Kernel AC', value: '4 actifs', color: 'var(--secondary-bright)' }
                ],
                tools: [
                    { icon: 'verified_user', name: 'EAC' },
                    { icon: 'verified_user', name: 'BattlEye' },
                    { icon: 'verified_user', name: 'Vanguard' },
                    { icon: 'verified_user', name: 'Valve VAC Net' },
                    { icon: 'bug_report', name: 'Intel SGX' },
                    { icon: 'analytics', name: 'ML Detection' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'BattlEye', url: 'https://battleye.com' },
                    { icon: 'open_in_new', label: 'EAC Developer', url: 'https://easy.ac/en-US/developer' }
                ]
            },
            'esports-infra': {
                cluster: 'CLUSTER_03 // HACKING × GAMING',
                title: 'INFRASTRUCTURE ESPORTS',
                desc: "Les tournois esports avec des prize pools de $280M+ attirent des attaques sophistiquées. Les DDoS pendant les matchs live peuvent changer l'issue d'un tournoi. La compromission des réseaux LAN permet l'espionnage de stratégies. Le match fixing, facilité par l'accès à des données en temps réel, menace l'intégrité compétitive.\n\nLa sécurisation d'un événement esports majeur implique : réseaux air-gapped pour les joueurs, monitoring réseau en temps réel, anti-tampering sur les configurations hardware, et équipes de response dédiées. 47 incidents majeurs de sécurité ont été recensés en 2024 lors d'événements compétitifs.",
                metrics: [
                    { label: 'Prize pools 2024', value: '$280M+', color: 'var(--primary)' },
                    { label: 'Incidents majeurs', value: '47', color: '#ff6b6b' },
                    { label: 'Viewers peak', value: '5.4M', color: 'var(--secondary-bright)' },
                    { label: 'LAN events', value: '350+/an', color: 'var(--primary)' }
                ],
                tools: [
                    { icon: 'shield', name: 'Cisco Secure' },
                    { icon: 'dns', name: 'Fortinet' },
                    { icon: 'verified_user', name: 'ESIC' },
                    { icon: 'analytics', name: 'Grafana' },
                    { icon: 'shield', name: 'Palo Alto' },
                    { icon: 'lock', name: 'WireGuard VPN' }
                ],
                links: [
                    { icon: 'open_in_new', label: 'ESIC Integrity', url: 'https://esic.gg' },
                    { icon: 'open_in_new', label: 'Liquipedia Events', url: 'https://liquipedia.net' }
                ]
            }
        };

        // ═══════ CROSSLINK DATA ═══════
        const crosslinkData = {
            'Smart Contract Audits pour Jeux': {
                cluster: 'CROSS_DOMAIN_LINK',
                title: 'SMART CONTRACT AUDITS POUR JEUX',
                desc: "L'intersection la plus critique du Web3 Gaming : auditer les smart contracts qui gèrent les économies in-game. Un bug dans un contrat de minting NFT ou un système de reward token peut drainer des millions en quelques heures. Les audits gaming-specific doivent vérifier la logique de gameplay on-chain, les mécanismes anti-cheat, et les guardrails économiques.",
                tools: [{ icon: 'bug_report', name: 'Slither' }, { icon: 'bug_report', name: 'Mythril' }, { icon: 'code', name: 'Foundry' }, { icon: 'shield', name: 'Immunefi' }],
                links: [{ icon: 'open_in_new', label: 'DeFiHackLabs', url: 'https://github.com/SunWeb3Sec/DeFiHackLabs' }]
            },
            'Token Engineering & Game Design': {
                cluster: 'CROSS_DOMAIN_LINK',
                title: 'TOKEN ENGINEERING & GAME DESIGN',
                desc: "La tokenomics d'un jeu doit être conçue en tandem avec le game design pour éviter l'inflation, la spéculation excessive et le death spiral. Les modèles bonding curves, les sink mechanisms et les emission schedules doivent être simulés (cadCAD) avant déploiement.",
                tools: [{ icon: 'analytics', name: 'cadCAD' }, { icon: 'analytics', name: 'Token Engineering Commons' }, { icon: 'code', name: 'Dune Analytics' }],
                links: [{ icon: 'open_in_new', label: 'Token Engineering', url: 'https://tokenengineeringcommunity.github.io/website' }]
            },
            'Esports & Crypto Betting': {
                cluster: 'CROSS_DOMAIN_LINK',
                title: 'ESPORTS & CRYPTO BETTING',
                desc: "Les plateformes de paris esports décentralisées (Polymarket, Augur) permettent de miser sur les résultats de tournois avec des smart contracts. Les défis : l'intégrité des matchs (match fixing), la manipulation d'oracles pour les résultats, et la réglementation cross-border des paris en ligne.",
                tools: [{ icon: 'token', name: 'Polymarket' }, { icon: 'shield', name: 'Chainlink Oracles' }, { icon: 'analytics', name: 'ESIC' }],
                links: [{ icon: 'open_in_new', label: 'ESIC Anti-Corruption', url: 'https://esic.gg' }]
            },
            'NFT Minting & Backend Architecture': {
                cluster: 'CROSS_DOMAIN_LINK',
                title: 'NFT MINTING & BACKEND',
                desc: "L'architecture backend pour le minting NFT gaming à l'échelle : gestion des queues de minting, metadata storage (IPFS/Arweave), lazy minting patterns, et gas optimization par batch processing. Le défi est de supporter des drops de 10K+ NFTs sans congestion ni failed transactions.",
                tools: [{ icon: 'developer_board', name: 'IPFS' }, { icon: 'developer_board', name: 'Arweave' }, { icon: 'code', name: 'Thirdweb' }, { icon: 'developer_board', name: 'Pinata' }],
                links: [{ icon: 'open_in_new', label: 'Thirdweb Docs', url: 'https://thirdweb.com/docs' }]
            },
            'Pentest d\'Interfaces Wallet': {
                cluster: 'OFFSEC × CRYPTO_LINK',
                title: 'PENTEST D\'INTERFACES WALLET',
                desc: "Tester la sécurité des interfaces utilisateur de wallets (browser extensions, web apps, mobile apps) : XSS stocké via des transactions crafted, injection de paramètres dans les deeplinks, et manipulation de la UI pour tromper l'utilisateur sur le destinataire réel d'une transaction.",
                tools: [{ icon: 'bug_report', name: 'Burp Suite' }, { icon: 'bug_report', name: 'OWASP ZAP' }, { icon: 'search', name: 'Frida' }, { icon: 'bug_report', name: 'Nmap' }],
                links: [{ icon: 'open_in_new', label: 'OWASP Testing Guide', url: 'https://owasp.org/www-project-web-security-testing-guide/' }]
            },
            'Exploit DeFi & Bug Bounty': {
                cluster: 'OFFSEC × CRYPTO_LINK',
                title: 'EXPLOIT DEFI & BUG BOUNTY',
                desc: "Participer aux programmes de bug bounty DeFi est l'un des revenus les plus lucratifs en cybersécurité. Immunefi héberge des bounties allant jusqu'à $10M pour des critical findings. La méthodologie : code review complet, fuzzing des fonctions exposées, et test des propriétés invariantes.",
                tools: [{ icon: 'bug_report', name: 'Immunefi' }, { icon: 'bug_report', name: 'Code4rena' }, { icon: 'bug_report', name: 'Sherlock' }, { icon: 'bug_report', name: 'Cantina' }],
                links: [{ icon: 'open_in_new', label: 'Immunefi', url: 'https://immunefi.com' }]
            },
            'OSINT & Traçage On-Chain': {
                cluster: 'OFFSEC × CRYPTO_LINK',
                title: 'OSINT & TRAÇAGE ON-CHAIN',
                desc: "Combiner les techniques OSINT traditionnelles avec l'analyse on-chain pour tracer les acteurs malveillants : clustering d'adresses, corrélation temporelle avec des events off-chain (social media, exchange KYC), et identification de patterns de blanchiment à travers des mixers et des tumblers.",
                tools: [{ icon: 'travel_explore', name: 'Chainalysis' }, { icon: 'analytics', name: 'Dune Analytics' }, { icon: 'search', name: 'Etherscan' }, { icon: 'travel_explore', name: 'Maltego' }],
                links: [{ icon: 'open_in_new', label: 'Dune Analytics', url: 'https://dune.com' }]
            },
            'Conformité AML & KYC Crypto': {
                cluster: 'OFFSEC × CRYPTO_LINK',
                title: 'CONFORMITÉ AML & KYC CRYPTO',
                desc: "Le cadre réglementaire crypto s'intensifie globalement : MiCA en Europe, FATF Travel Rule, et les sanctions OFAC contre les mixers (Tornado Cash). Les solutions de compliance combinent KYC/AML automatisé, transaction monitoring en temps réel, et screening contre les listes de sanctions.",
                tools: [{ icon: 'shield', name: 'Chainalysis KYT' }, { icon: 'shield', name: 'Elliptic' }, { icon: 'shield', name: 'TRM Labs' }, { icon: 'policy', name: 'Sumsub' }],
                links: [{ icon: 'open_in_new', label: 'FATF Crypto Guidance', url: 'https://fatf-gafi.org/en/topics/virtual-assets' }]
            },
            'Pentest d\'Infrastructures de Jeux': {
                cluster: 'OFFSEC × GAMING_LINK',
                title: 'PENTEST INFRA JEUX',
                desc: "Les infrastructures gaming (matchmaking servers, game databases, CDN pour les assets, API backend) présentent des surfaces d'attaque spécifiques : injection dans les API de matchmaking, éscalade de privilèges via les game panels admin, et exfiltration de données joueurs via les endpoints de leaderboards.",
                tools: [{ icon: 'bug_report', name: 'Nmap' }, { icon: 'bug_report', name: 'Burp Suite' }, { icon: 'bug_report', name: 'Metasploit' }, { icon: 'dns', name: 'Nuclei' }],
                links: [{ icon: 'open_in_new', label: 'OWASP API Security', url: 'https://owasp.org/www-project-api-security/' }]
            },
            'Reverse Engineering de Game Engines': {
                cluster: 'OFFSEC × GAMING_LINK',
                title: 'REVERSE ENGINEERING GAME ENGINES',
                desc: "Le reverse engineering des moteurs de jeux (Unity IL2CPP, Unreal Engine, Source 2) permet de comprendre les mécaniques anti-cheat, de développer des outils de modding légitimes, et de découvrir des vulnérabilités dans les protocols réseau. IDA Pro, Ghidra et x64dbg sont les outils de base du game RE.",
                tools: [{ icon: 'code', name: 'IDA Pro' }, { icon: 'code', name: 'Ghidra' }, { icon: 'bug_report', name: 'x64dbg' }, { icon: 'code', name: 'dnSpy' }],
                links: [{ icon: 'open_in_new', label: 'Ghidra', url: 'https://ghidra-sre.org' }]
            },
            'OSINT sur les Marchés de Comptes Volés': {
                cluster: 'OFFSEC × GAMING_LINK',
                title: 'OSINT MARCHÉS COMPTES VOLÉS',
                desc: "Traquer les marchés underground de comptes gaming volés sur Telegram, forums russes et marketplace dark web. Les techniques OSINT permettent d'identifier les réseaux de credential stuffing, de cartographier les opérateurs, et de collaborer avec les éditeurs pour les takedowns.",
                tools: [{ icon: 'travel_explore', name: 'Maltego' }, { icon: 'travel_explore', name: 'SpiderFoot' }, { icon: 'search', name: 'Shodan' }, { icon: 'analytics', name: 'GreyNoise' }],
                links: [{ icon: 'open_in_new', label: 'OSINT Framework', url: 'https://osintframework.com' }]
            },
            'Sécurité Réseaux LAN Tournois': {
                cluster: 'OFFSEC × GAMING_LINK',
                title: 'SÉCURITÉ LAN TOURNOIS',
                desc: "Les réseaux LAN des tournois esports sont des environnements critiques : isolation des segments joueurs/organisateurs/spectateurs, détection d'intrusion en temps réel, anti-tampering des configurations hardware, et monitoring des connexions USB. Un seul incident peut compromettre l'intégrité d'un tournoi millionnaire.",
                tools: [{ icon: 'dns', name: 'Wireshark' }, { icon: 'shield', name: 'Snort/Suricata' }, { icon: 'analytics', name: 'Zeek' }, { icon: 'lock', name: '802.1X' }],
                links: [{ icon: 'open_in_new', label: 'ESIC Guidelines', url: 'https://esic.gg' }]
            }
        };

        // ═══════ RENDER FUNCTIONS ═══════
        function renderDetail(data) {
            sdCluster.textContent = data.cluster;
            sdTitle.textContent = data.title;

            let html = '<p class="synergy-detail__desc">' + data.desc.replace(/\n/g, '<br>') + '</p>';

            if (data.metrics && data.metrics.length) {
                html += '<div class="synergy-detail__metrics">';
                data.metrics.forEach(function(m) {
                    html += '<div class="synergy-detail__metric"><div class="synergy-detail__metric-label">' + m.label + '</div><div class="synergy-detail__metric-value" style="color:' + m.color + ';">' + m.value + '</div></div>';
                });
                html += '</div>';
            }

            if (data.tools && data.tools.length) {
                html += '<div class="synergy-detail__section-title"><span class="material-symbols-outlined" style="font-size:14px;">build</span>OUTILS & TECHNOLOGIES</div>';
                html += '<div class="synergy-detail__tools">';
                data.tools.forEach(function(t) {
                    html += '<span class="synergy-detail__tool"><span class="material-symbols-outlined">' + t.icon + '</span>' + t.name + '</span>';
                });
                html += '</div>';
            }

            if (data.links && data.links.length) {
                html += '<div class="synergy-detail__section-title"><span class="material-symbols-outlined" style="font-size:14px;">link</span>RESSOURCES</div>';
                html += '<div class="synergy-detail__links">';
                data.links.forEach(function(l) {
                    html += '<a class="synergy-detail__link" href="' + l.url + '" target="_blank" rel="noopener"><span class="material-symbols-outlined">' + l.icon + '</span>' + l.label + '</a>';
                });
                html += '</div>';
            }

            sdBody.innerHTML = html;
            overlay.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }

        function closeDetail() {
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }

        // ═══════ EVENT LISTENERS ═══════

        // Cards
        document.querySelectorAll('.synergy-card').forEach(function(card) {
            card.addEventListener('click', function() {
                var key = card.getAttribute('data-synergy');
                if (synergyData[key]) {
                    renderDetail(synergyData[key]);
                }
            });
        });

        // Crosslinks
        document.querySelectorAll('.synergy-crosslink').forEach(function(link) {
            link.addEventListener('click', function() {
                var label = link.querySelector('.synergy-crosslink__label').textContent.trim();
                if (crosslinkData[label]) {
                    renderDetail(crosslinkData[label]);
                }
            });
        });

        // Bridge nodes — scroll to cluster
        document.querySelectorAll('.synergy-bridge__node').forEach(function(node) {
            node.addEventListener('click', function() {
                var text = node.textContent.trim().toUpperCase();
                var target = null;
                if (text.includes('CRYPTO') && text.includes('GAMING')) {
                    // crypto node in cluster 1
                    target = document.querySelector('[data-synergy="p2e"]');
                } else if (text.includes('HACKING')) {
                    target = document.querySelector('[data-synergy="wallet-sec"]');
                } else if (text.includes('GAMING')) {
                    target = document.querySelector('[data-synergy="game-server"]');
                } else if (text.includes('CRYPTO')) {
                    target = document.querySelector('[data-synergy="wallet-sec"]');
                }
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    target.style.boxShadow = '0 0 0 2px var(--primary), 0 8px 32px rgba(255,130,0,0.2)';
                    setTimeout(function() { target.style.boxShadow = ''; }, 2000);
                }
            });
        });

        // Close modal
        sdClose.addEventListener('click', closeDetail);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeDetail();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('visible')) closeDetail();
        });
    })();
    </script>



    <!-- ================================
         COOKIE CONSENT SYSTEM
         ================================ -->

    <!-- Floating Cookie Button (visible after banner dismissed) -->
    <button class="cookie-float" id="cookie-float" title="Paramètres des cookies">
        <span class="material-symbols-outlined" style="font-size:20px;">cookie</span>
    </button>

    <!-- Cookie Banner -->
    <div class="cookie-banner" id="cookie-banner">
        <div class="cookie-banner__inner">
            <span class="material-symbols-outlined cookie-banner__icon">policy</span>
            <div class="cookie-banner__text">
                <p>Ce site utilise des cookies pour assurer le fonctionnement, l'analyse du trafic et l'amélioration de l'expérience. Vous pouvez personnaliser vos préférences ou tout accepter.</p>
            </div>
            <div class="cookie-banner__actions">
                <button class="cookie-btn cookie-btn--settings" id="cookie-settings-btn">Paramétrer</button>
                <button class="cookie-btn cookie-btn--reject" id="cookie-reject-btn">Refuser</button>
                <button class="cookie-btn cookie-btn--accept" id="cookie-accept-btn">Tout accepter</button>
            </div>
        </div>
    </div>

    <!-- Cookie Settings Modal -->
    <div class="cookie-modal-overlay" id="cookie-modal">
        <div class="cookie-modal">
            <div class="cookie-modal__header">
                <span class="cookie-modal__title">COOKIE_PREFERENCES</span>
                <button class="cookie-modal__close" id="cookie-modal-close">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>
            <div class="cookie-modal__body">

                <!-- Essential -->
                <div class="cookie-category">
                    <div class="cookie-category__header">
                        <span class="cookie-category__name">ESSENTIELS <span>(toujours actifs)</span></span>
                        <label class="cookie-toggle">
                            <input type="checkbox" checked disabled>
                            <span class="cookie-toggle__slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category__desc">Cookies nécessaires au fonctionnement du site : session PHP, sécurité CSRF, consentement cookie. Ne peuvent pas être désactivés.</p>
                </div>

                <!-- Analytics -->
                <div class="cookie-category">
                    <div class="cookie-category__header">
                        <span class="cookie-category__name">ANALYTIQUES</span>
                        <label class="cookie-toggle">
                            <input type="checkbox" id="cookie-analytics">
                            <span class="cookie-toggle__slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category__desc">Aident à comprendre comment les visiteurs interagissent avec le site. Données agrégées et anonymisées uniquement.</p>
                </div>

                <!-- Functional -->
                <div class="cookie-category">
                    <div class="cookie-category__header">
                        <span class="cookie-category__name">FONCTIONNELS</span>
                        <label class="cookie-toggle">
                            <input type="checkbox" id="cookie-functional">
                            <span class="cookie-toggle__slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category__desc">Permettent de mémoriser vos préférences (thème, langue, chat history) pour une expérience personnalisée.</p>
                </div>

                <!-- Marketing -->
                <div class="cookie-category">
                    <div class="cookie-category__header">
                        <span class="cookie-category__name">MARKETING</span>
                        <label class="cookie-toggle">
                            <input type="checkbox" id="cookie-marketing">
                            <span class="cookie-toggle__slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category__desc">Utilisés pour afficher du contenu pertinent et mesurer l'efficacité des campagnes de communication.</p>
                </div>

            </div>
            <div class="cookie-modal__footer">
                <button class="cookie-btn cookie-btn--reject" id="cookie-modal-reject">Tout refuser</button>
                <button class="cookie-btn cookie-btn--accept" id="cookie-modal-save">Enregistrer</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // ── Cookie helpers ──
        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + days * 86400000);
            document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
        }

        function getCookie(name) {
            const match = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return match ? decodeURIComponent(match[2]) : null;
        }

        // ── Elements ──
        const banner = document.getElementById('cookie-banner');
        const modal = document.getElementById('cookie-modal');
        const floatBtn = document.getElementById('cookie-float');
        const analyticsToggle = document.getElementById('cookie-analytics');
        const functionalToggle = document.getElementById('cookie-functional');
        const marketingToggle = document.getElementById('cookie-marketing');

        // ── Load saved preferences ──
        function loadPreferences() {
            const saved = getCookie('bp_cookie_consent');
            if (saved) {
                try {
                    const prefs = JSON.parse(saved);
                    analyticsToggle.checked = !!prefs.analytics;
                    functionalToggle.checked = !!prefs.functional;
                    marketingToggle.checked = !!prefs.marketing;
                } catch(e) {}
            }
        }

        // ── Save preferences ──
        function savePreferences(acceptAll) {
            const prefs = {
                essential: true,
                analytics: acceptAll ? true : analyticsToggle.checked,
                functional: acceptAll ? true : functionalToggle.checked,
                marketing: acceptAll ? true : marketingToggle.checked,
                timestamp: new Date().toISOString()
            };
            setCookie('bp_cookie_consent', JSON.stringify(prefs), 365);
            applyPreferences(prefs);
        }

        function rejectAll() {
            const prefs = {
                essential: true,
                analytics: false,
                functional: false,
                marketing: false,
                timestamp: new Date().toISOString()
            };
            setCookie('bp_cookie_consent', JSON.stringify(prefs), 365);
            analyticsToggle.checked = false;
            functionalToggle.checked = false;
            marketingToggle.checked = false;
            applyPreferences(prefs);
        }

        // ── Apply preferences ──
        function applyPreferences(prefs) {
            // Store locally for other scripts to check
            window.bpCookies = prefs;

            // Functional: remember theme / chat history
            if (prefs.functional) {
                setCookie('bp_functional_enabled', '1', 365);
            } else {
                document.cookie = 'bp_functional_enabled=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
                document.cookie = 'bp_chat_history=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
            }

            // Analytics placeholder (ready for Google Analytics, etc.)
            if (prefs.analytics) {
                setCookie('bp_analytics_enabled', '1', 365);
                // TODO: Load analytics script here when ready
            } else {
                document.cookie = 'bp_analytics_enabled=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
            }

            // Marketing placeholder
            if (prefs.marketing) {
                setCookie('bp_marketing_enabled', '1', 365);
            } else {
                document.cookie = 'bp_marketing_enabled=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
            }
        }

        // ── Show / Hide ──
        function showBanner() {
            setTimeout(function() { banner.classList.add('visible'); }, 1000);
        }

        function hideBanner() {
            banner.classList.remove('visible');
            floatBtn.classList.add('visible');
        }

        function showModal() {
            modal.classList.add('visible');
        }

        function hideModal() {
            modal.classList.remove('visible');
        }

        // ── Init ──
        loadPreferences();

        const consent = getCookie('bp_cookie_consent');
        if (consent) {
            // Already consented — show float button only
            floatBtn.classList.add('visible');
            try { applyPreferences(JSON.parse(consent)); } catch(e) {}
        } else {
            // No consent yet — show banner
            showBanner();
        }

        // ── Event listeners ──
        document.getElementById('cookie-accept-btn').addEventListener('click', function() {
            savePreferences(true);
            hideBanner();
        });

        document.getElementById('cookie-reject-btn').addEventListener('click', function() {
            rejectAll();
            hideBanner();
        });

        document.getElementById('cookie-settings-btn').addEventListener('click', function() {
            showModal();
        });

        document.getElementById('cookie-modal-close').addEventListener('click', function() {
            hideModal();
        });

        document.getElementById('cookie-modal-save').addEventListener('click', function() {
            savePreferences(false);
            hideModal();
            hideBanner();
        });

        document.getElementById('cookie-modal-reject').addEventListener('click', function() {
            rejectAll();
            hideModal();
            hideBanner();
        });

        floatBtn.addEventListener('click', function() {
            showModal();
        });

        // Close modal on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) hideModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('visible')) {
                hideModal();
            }
        });
    })();
    </script>

    <!-- Gaming Calendar -->
    <script src="assets/js/gaming-calendar.js" defer></script>

</body>
</html>
