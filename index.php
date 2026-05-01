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
            <rssapp-ticker id="TVOvmVGK5J7yEQY4"></rssapp-ticker><script src="https://widget.rss.app/v1/ticker.js" type="text/javascript" async></script>
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

                <!-- Project 1: Neural Breach Shield -->
                <a href="#project-1" class="project-article project-article--orange project-article--link">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCjMXRxXBt-XR5y0YktP9I6F5Q8YVdeJhXJ4mpma3LJT1_WxMn1aZ8FakK5Jg_FubkbwlgjLORhWqxjtE8FcARevecKWrZEcoQl7jgQO0mIPcr_TvjC36c8q7OaR6pBRB_QTqjggaF1hc2LCj2pDk8l1QEjvWReADRt4ClMsM8Cuyi0pVvxJ_lTVgk1QZyzZAuZB1lLUTnX7SzpHKpuPzebNJNVLQV3bBihJfvy9Fxp9UFnX8XKYcVrlVz08GTryVO4GnNBQtgpLZI" loading="lazy" decoding="async" alt="Neural Breach Shield">
                        <div class="project-article__badge project-article__badge--orange">
                            <span>01_SEC</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Neural Breach Shield</h2>
                        <p class="project-article__desc">Autonomous penetration testing suite for large-scale decentralized infrastructures using adversarial ML models.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--orange">#CYBERSECURITY</span>
                            <span class="project-tag project-tag--green">#RUST</span>
                            <span class="project-tag project-tag--neutral">#AI</span>
                        </div>
                    </div>
                </a>

                <!-- Project 2: Abidjan Terminal UI -->
                <a href="#project-2" class="project-article project-article--green project-article--link">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCJCsWrr7XhXHdayMqdlbcfL0YrEkYgLjc_3Ptjmzt9iXZb-jzEC8_mI9BGyyNbkb-juaUqne6bJNkF-mb9xkU-qP63w5rJSSGVS9gnQOw5-tW2DNam3C_WTqPCQeJgOTBfqdPsAHenyxkocEoGG6KLDtB9pVvABRC9WquKRoak3GK1TIzp130biGkS1-0mh_b-9227k5K7oIpiHLvTHZjeWhCR4_HSi-A16DcdY6KfZ7qFnwHbSiJW-VjNnSurAS-h9zmgDKtkDD8" loading="lazy" decoding="async" alt="Abidjan Terminal UI">
                        <div class="project-article__badge project-article__badge--green">
                            <span>02_UI</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Abidjan Terminal UI</h2>
                        <p class="project-article__desc">High-performance logistics dashboard designed for real-time tracking of maritime cargo across West African ports.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--green">#WEBDESIGN</span>
                            <span class="project-tag project-tag--orange">#NEXTJS</span>
                            <span class="project-tag project-tag--neutral">#LOGISTICS</span>
                        </div>
                    </div>
                </a>

                <!-- Project 3: Eburnie Chronicles -->
                <a href="#project-3" class="project-article project-article--orange project-article--link">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBNif-pzq2fiHIwVxQQzrozHe6zGWw5UIahi6OQ_tIlnSe0wNM8WgND2E4MfwWYOSyxAwavmgDsuI9IcwCHg3-EWAsg8oU_k-OViPgfjWJ9S3glUkrdkPmWoa19Q7hc9yI3lOIYHInt_MP5tmFJEhTp_dadtd9gkojAXj78NYoV1CpVVOQomLRpvO-Lw35ChOmdgp370NnBnr1NMkxeiwZZ4lEwhe6CGLIMFPX9n4hsU0GEyDdjt6N8rMvF1via6Q5v1krUzKByDiE" loading="lazy" decoding="async" alt="Eburnie Chronicles">
                        <div class="project-article__badge project-article__badge--orange">
                            <span>03_GM</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Eburnie Chronicles</h2>
                        <p class="project-article__desc">A narrative-driven RPG engine built on Unreal 5, featuring procedural world generation and advanced AI NPC behaviors.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--orange">#GAMEDEV</span>
                            <span class="project-tag project-tag--green">#UE5</span>
                            <span class="project-tag project-tag--neutral">#SHADERS</span>
                        </div>
                    </div>
                </a>

                <!-- Project 4: Core Infra Automata -->
                <a href="#project-4" class="project-article project-article--green project-article--link">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAozYEJo5Gr3ZIHjhPNlP88xDNQTOoHaGko5CYayBS3NkeYp6WGv_ZEF2weyxOCpSBesYcbf_oMGCB4i4HN8yQ6KZvsM9gP6Lin3Vd8HHgFQMEf1pN6huilxoKhzEBFoIxAYO9VnuXKRnEmern9bjlbPVXUsC00CECyUzGPVlrZUAjTmUaQMYGJJPcZNwTOUI8ZH9p0-os16MjRh_xJbDfTakyKjmJjPrlS0o-UxyET7XlSTK_g1ivU4G9_8Q7lvJNHdX5GqPe7W7o" loading="lazy" decoding="async" alt="Core Infra Automata">
                        <div class="project-article__badge project-article__badge--green">
                            <span>04_OP</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Core Infra Automata</h2>
                        <p class="project-article__desc">Terraform-driven multi-cloud orchestrator designed for zero-downtime deployments in mission-critical environments.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--green">#DEVOPS</span>
                            <span class="project-tag project-tag--orange">#K8S</span>
                            <span class="project-tag project-tag--neutral">#AWS</span>
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
         SECTION 4 — BLOG / FLUX RSS
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
                    <rssapp-magazine id="zVn6TvX26blDCSPJ"></rssapp-magazine><script src="https://widget.rss.app/v1/magazine.js" type="text/javascript" async></script>
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
                    <rssapp-carousel id="TVOvmVGK5J7yEQY4"></rssapp-carousel><script src="https://widget.rss.app/v1/carousel.js" type="text/javascript" async></script>
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
                    <rssapp-wall id="S5zmzgmuJRPBcSVo"></rssapp-wall><script src="https://widget.rss.app/v1/wall.js" type="text/javascript" async></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 5 — NEWSLETTER
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
         SECTION 6 — GALLERY / IMAGEBOARD
         ================================ -->
    <section class="gallery-section" id="gallery">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Visual Stream</div>
                <h1 class="section-header__title">GALLERY_BOARD</h1>
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
                    <rssapp-imageboard id="cc3dcW1cljMM3wlZ"></rssapp-imageboard><script src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async></script>
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
                    <rssapp-imageboard id="lGxVnaoQxFBHUR53"></rssapp-imageboard><script src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async></script>
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
                    <rssapp-imageboard id="Lt3ZLEhfxXAnAcuM"></rssapp-imageboard><script src="https://widget.rss.app/v1/imageboard.js" type="text/javascript" async></script>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SECTION 8 — CONTACT
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
                    <form class="contact-form-card__form" action="#" method="POST">
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
                            <span>EXECUTE_SUBMISSION</span>
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
                            <a href="#" class="channels-card__link">
                                <div class="channels-card__link-icon">
                                    <span class="material-symbols-outlined">forum</span>
                                </div>
                                <div>
                                    <p class="channels-card__link-platform">DISCORD</p>
                                    <p class="channels-card__link-handle code-sm">@creator_node</p>
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
         SECTION 9 — DONATIONS / SOUTIEN
         ================================ -->
    <section class="donate-section" id="donate">
        <div class="container">
            <div class="section-header">
                <div class="section-header__bar"></div>
                <div class="section-header__label">Support</div>
                <h1 class="section-header__title">DONATE_PORTAL</h1>
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
            '.channels-card, .contact-form-card'
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

        // — Form handling (basic) —
        const form = document.querySelector('.contact-form-card__form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                alert('Merci pour votre message ! (Démo — connectez un backend PHP pour le traitement)');
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
              response: "Mes projets recents :\n• Neural Breach Shield — Penetration testing autonome\n• Abidjan Terminal UI — Dashboard logistique\n• Eburnie Chronicles — RPG narratif (Unreal 5)\n• Core Infra Automata — Orchestrateur multi-cloud\n\nScrolle jusqu'a PROJECTS pour les decouvrir !" },
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
            { keywords: ['merci', 'thanks', 'super', 'cool', 'genial', 'parfait'],
              response: "Avec plaisir ! N'hesite pas si tu as d'autres questions. Tu peux aussi m'ecrire via le formulaire de contact en bas de page. A bientot !" },
        ];

        function getBotResponse(msg) {
            const lower = msg.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            for (const entry of botKnowledge) {
                for (const kw of entry.keywords) {
                    if (lower.includes(kw)) return entry.response;
                }
            }
            return "Interessant ! Pas de reponse specifique pour ca. Essaie de me demander : mes competences, mes projets, la cybersecurite, le DevOps, le game dev, ou comment me contacter.";
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

</body>
</html>
