<?php
/**
 * BLACK_PROTOCOL — Portfolio Page
 * Section 3 — Projects / Projets
 * Design System: Minimalist Cyberpunk × Professional Brutalism
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
require_once __DIR__ . "/includes/articles.php";
require_once __DIR__ . "/includes/comments.php";

$lang = "fr";
$charset = "UTF-8";
$seoTitle = "Projets — Portfolio BLACK_PROTOCOL";
$seoDesc =
    "Découvrez l'ensemble des projets de BLACK_PROTOCOL : cybersécurité, développement web, game dev, DevOps et intelligence artificielle.";
$title = $seoTitle . " | BLACK_PROTOCOL";
$desc = $seoDesc;
$seoKeywords = [
    "portfolio projets",
    "cybersécurité",
    "développement web",
    "game dev",
];
$year = date("Y");

$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

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

$publishedArticles = getPublishedArticles(6);
$articleDeleted = false;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_article"])) {
    if ($isLoggedIn && isAdmin()) {
        $delId = (int) ($_POST["delete_article"] ?? 0);
        if ($delId > 0) {
            $articleDeleted = deleteAnyArticle($delId);
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

    <!-- Fonts: reduced weight set for faster load -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap">

    <!-- Material Symbols: load async -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"></noscript>

    <!-- Critical CSS inline -->
    <style>
        :root{--void:#0a0a0b;--surface:#161618;--primary:#ff8200;--primary-dim:#ffb785;--primary-glow:rgba(255,130,0,0.35);--secondary:#009e60;--secondary-bright:#61dd98;--secondary-glow:rgba(0,158,96,0.3);--white:#ffffff;--on-surface:#f4ded2;--on-surface-variant:#dec1af;--outline:#a68b7b;--font-display:"Space Grotesk",sans-serif;--font-body:"Inter",sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font-body);background:var(--void);color:var(--on-surface);overflow-x:hidden;padding-bottom:80px;padding-top:64px}
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
    </style>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/portfolio.css">
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
         PORTFOLIO HERO BANNER
         ================================ -->
    <section class="portfolio-hero" id="portfolio-top">
        <div class="container">
            <div class="portfolio-hero__breadcrumb">
                <a href="index.php" class="portfolio-hero__breadcrumb-link">
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                    <span>INDEX</span>
                </a>
                <span class="portfolio-hero__breadcrumb-sep">/</span>
                <span class="label-caps" style="color: var(--primary);">PORTFOLIO</span>
            </div>
            <div class="portfolio-hero__content">
                <h1 class="portfolio-hero__title">
                    PROJECTS <span style="color: var(--on-surface-variant);">/</span> <span style="color: var(--secondary-bright);">PROJETS</span>
                </h1>
                <p class="portfolio-hero__subtitle code-sm">Mastery_Archive.v2 — Full Collection</p>
                <div class="portfolio-hero__line"></div>
                <p class="portfolio-hero__desc">Complete catalogue of builds, experiments, and production-grade systems across cybersecurity, web design, game development, DevOps, and artificial intelligence.</p>
            </div>
        </div>
    </section>

    <!-- ================================
         FILTER BAR
         ================================ -->
    <section class="portfolio-filters" id="portfolio-filters">
        <div class="container">
            <div class="portfolio-filters__bar">
                <button class="portfolio-filter-btn active" data-filter="all">
                    <span class="material-symbols-outlined" style="font-size:16px;">apps</span>
                    <span>ALL</span>
                </button>
                <button class="portfolio-filter-btn" data-filter="cybersecurity">
                    <span class="material-symbols-outlined" style="font-size:16px;">shield</span>
                    <span>CYBERSECURITY</span>
                </button>
                <button class="portfolio-filter-btn" data-filter="webdesign">
                    <span class="material-symbols-outlined" style="font-size:16px;">palette</span>
                    <span>WEB/DESIGN</span>
                </button>
                <button class="portfolio-filter-btn" data-filter="gamedev">
                    <span class="material-symbols-outlined" style="font-size:16px;">sports_esports</span>
                    <span>GAMEDEV</span>
                </button>
                <button class="portfolio-filter-btn" data-filter="devops">
                    <span class="material-symbols-outlined" style="font-size:16px;">cloud</span>
                    <span>DEVOPS</span>
                </button>
                <button class="portfolio-filter-btn" data-filter="ai">
                    <span class="material-symbols-outlined" style="font-size:16px;">psychology</span>
                    <span>AI/ML</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ================================
         PROJECTS GRID
         ================================ -->
    <section class="projects-section" id="projects">
        <div class="container">
            <!-- Section Header with Orange Left Border -->
            <div class="section-header-row">
                <div>
                    <h1>PROJECTS</h1>
                    <p class="subtitle code-sm">Mastery_Archive.v2 — Full Collection</p>
                </div>
                <button class="filter-btn" id="toggle-filter-btn">
                    <span class="material-symbols-outlined">filter_list</span>
                    <span>FILTRE</span>
                </button>
            </div>

            <!-- Projects Grid -->
            <div class="projects-grid portfolio-grid" id="portfolio-grid">

                <!-- Project 1: Neural Breach Shield -->
                <a href="javascript:void(0)" class="project-article project-article--orange project-article--link portfolio-card"
                   data-category="cybersecurity"
                   data-title="Neural Breach Shield"
                   data-desc="Suite avancée de test de pénétration autonome conçue pour les infrastructures décentralisées à grande échelle. Utilise des modèles d'apprentissage automatique adversariaux pour identifier et exploiter les vulnérabilités en temps réel. Intègre un moteur de reporting automatisé compatible avec les frameworks MITRE ATT&amp;CK."
                   data-tech="Rust, Python, TensorFlow, Docker, MITRE ATT&amp;CK"
                   data-features="Scanning autonome | Rapports MITRE ATT&amp;CK | ML adversarial | API REST"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuCjMXRxXBt-XR5y0YktP9I6F5Q8YVdeJhXJ4mpma3LJT1_WxMn1aZ8FakK5Jg_FubkbwlgjLORhWqxjtE8FcARevecKWrZEcoQl7jgQO0mIPcr_TvjC36c8q7OaR6pBRB_QTqjggaF1hc2LCj2pDk8l1QEjvWReADRt4ClMsM8Cuyi0pVvxJ_lTVgk1QZyzZAuZB1lLUTnX7SzpHKpuPzebNJNVLQV3bBihJfvy9Fxp9UFnX8XKYcVrlVz08GTryVO4GnNBQtgpLZI">
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
                <a href="javascript:void(0)" class="project-article project-article--green project-article--link portfolio-card"
                   data-category="webdesign"
                   data-title="Abidjan Terminal UI"
                   data-desc="Tableau de bord logistique haute performance pour le suivi en temps réel des cargaisons maritimes across les ports d'Afrique de l'Ouest. Interface intuitive avec visualisation de données en temps réel, gestion des conteneurs et alertes prédictives basées sur l'IA."
                   data-tech="Next.js, TypeScript, D3.js, PostgreSQL, WebSocket"
                   data-features="Cartes temps réel | Prédictions IA | Dashboard responsive | Notifications push"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuCJCsWrr7XhXHdayMqdlbcfL0YrEkYgLjc_3Ptjmzt9iXZb-jzEC8_mI9BGyyNbkb-juaUqne6bJNkF-mb9xkU-qP63w5rJSSGVS9gnQOw5-tW2DNam3C_WTqPCQeJgOTBfqdPsAHenyxkocEoGG6KLDtB9pVvABRC9WquKRoak3GK1TIzp130biGkS1-0mh_b-9227k5K7oIpiHLvTHZjeWhCR4_HSi-A16DcdY6KfZ7qFnwHbSiJW-VjNnSurAS-h9zmgDKtkDD8">
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
                <a href="javascript:void(0)" class="project-article project-article--orange project-article--link portfolio-card"
                   data-category="gamedev"
                   data-title="Eburnie Chronicles"
                   data-desc="Moteur RPG narratif développé sur Unreal Engine 5, mettant en œuvre une génération procédurale de monde et des comportements IA avancés pour les PNJ. Système de quêtes dynamiques et système de combat innovant avec shaders personnalisés."
                   data-tech="Unreal Engine 5, C++, HLSL, Niagara VFX"
                   data-features="Monde procédural | IA PNJ avancée | Shaders custom | Système de quêtes dynamique"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuBNif-pzq2fiHIwVxQQzrozHe6zGWw5UIahi6OQ_tIlnSe0wNM8WgND2E4MfwWYOSyxAwavmgDsuI9IcwCHg3-EWAsg8oU_k-OViPgfjWJ9S3glUkrdkPmWoa19Q7hc9yI3lOIYHInt_MP5tmFJEhTp_dadtd9gkojAXj78NYoV1CpVVOQomLRpvO-Lw35ChOmdgp370NnBnr1NMkxeiwZZ4lEwhe6CGLIMFPX9n4hsU0GEyDdjt6N8rMvF1via6Q5v1krUzKByDiE">
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
                <a href="javascript:void(0)" class="project-article project-article--green project-article--link portfolio-card"
                   data-category="devops"
                   data-title="Core Infra Automata"
                   data-desc="Orchestrateur multi-cloud piloté par Terraform, conçu pour les déploiements sans interruption dans des environnements critiques. Architecture GitOps avec monitoring intégré et auto-réparation des services défaillants."
                   data-tech="Terraform, Kubernetes, AWS, GitHub Actions, Prometheus"
                   data-features="Zero-downtime deploy | Auto-healing | GitOps pipeline | Monitoring Grafana"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuAozYEJo5Gr3ZIHjhPNlP88xDNQTOoHaGko5CYayBS3NkeYp6WGv_ZEF2weyxOCpSBesYcbf_oMGCB4i4HN8yQ6KZvsM9gP6Lin3Vd8HHgFQMEf1pN6huilxoKhzEBFoIxAYO9VnuXKRnEmern9bjlbPVXUsC00CECyUzGPVlrZUAjTmUaQMYGJJPcZNwTOUI8ZH9p0-os16MjRh_xJbDfTakyKjmJjPrlS0o-UxyET7XlSTK_g1ivU4G9_8Q7lvJNHdX5GqPe7W7o">
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

                <!-- Project 5: Phantom Recon Engine -->
                <a href="javascript:void(0)" class="project-article project-article--green project-article--link portfolio-card"
                   data-category="cybersecurity"
                   data-title="Phantom Recon Engine"
                   data-desc="Plateforme OSINT de renseignement en temps réel pour le threat hunting et la cartographie de surface d'attaque. Agrège des données de multiples sources et utilise le ML pour détecter les menaces émergentes avant qu'elles ne se matérialisent."
                   data-tech="Python, Scikit-learn, Elasticsearch, Shodan API, Docker"
                   data-features="Threat hunting | OSINT multi-sources | Alertes temps réel | Cartographie attaque"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c" loading="lazy" decoding="async" alt="Phantom Recon Engine">
                        <div class="project-article__badge project-article__badge--green">
                            <span>05_SEC</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Phantom Recon Engine</h2>
                        <p class="project-article__desc">Real-time OSINT intelligence platform for threat hunting and attack surface mapping across distributed networks.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--green">#CYBERSECURITY</span>
                            <span class="project-tag project-tag--orange">#PYTHON</span>
                            <span class="project-tag project-tag--neutral">#ML</span>
                        </div>
                    </div>
                </a>

                <!-- Project 6: Lagune Commerce -->
                <a href="javascript:void(0)" class="project-article project-article--orange project-article--link portfolio-card"
                   data-category="webdesign"
                   data-title="Lagune Commerce"
                   data-desc="Plateforme e-commerce full-stack avec gestion d'inventaire en temps réel et traitement de paiements intégré pour les marchés ouest-africains. Support multi-devises, gestion des taxes locales et interface d'administration complète."
                   data-tech="React, Node.js, Stripe, PostgreSQL, Redis"
                   data-features="Multi-devises | Paiement mobile | Gestion stock temps réel | Admin dashboard"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuBNif-pzq2fiHIwVxQQzrozHe6zGWw5UIahi6OQ_tIlnSe0wNM8WgND2E4MfwWYOSyxAwavmgDsuI9IcwCHg3-EWAsg8oU_k-OViPgfjWJ9S3glUkrdkPmWoa19Q7hc9yI3lOIYHInt_MP5tmFJEhTp_dadtd9gkojAXj78NYoV1CpVVOQomLRpvO-Lw35ChOmdgp370NnBnr1NMkxeiwZZ4lEwhe6CGLIMFPX9n4hsU0GEyDdjt6N8rMvF1via6Q5v1krUzKByDiE">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBNif-pzq2fiHIwVxQQzrozHe6zGWw5UIahi6OQ_tIlnSe0wNM8WgND2E4MfwWYOSyxAwavmgDsuI9IcwCHg3-EWAsg8oU_k-OViPgfjWJ9S3glUkrdkPmWoa19Q7hc9yI3lOIYHInt_MP5tmFJEhTp_dadtd9gkojAXj78NYoV1CpVVOQomLRpvO-Lw35ChOmdgp370NnBnr1NMkxeiwZZ4lEwhe6CGLIMFPX9n4hsU0GEyDdjt6N8rMvF1via6Q5v1krUzKByDiE" loading="lazy" decoding="async" alt="Lagune Commerce">
                        <div class="project-article__badge project-article__badge--orange">
                            <span>06_UI</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Lagune Commerce</h2>
                        <p class="project-article__desc">Full-stack e-commerce platform with real-time inventory management and integrated payment processing for West African markets.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--orange">#ECOMMERCE</span>
                            <span class="project-tag project-tag--green">#REACT</span>
                            <span class="project-tag project-tag--neutral">#STRIPE</span>
                        </div>
                    </div>
                </a>

                <!-- Project 7: Shadow Protocol AI -->
                <a href="javascript:void(0)" class="project-article project-article--green project-article--link portfolio-card"
                   data-category="ai"
                   data-title="Shadow Protocol AI"
                   data-desc="Assistant LLM personnalisé avec capacités RAG pour la gestion des connaissances d'entreprise et la revue de code automatisée. Fine-tuné sur des datasets spécialisés en cybersécurité et développement logiciel."
                   data-tech="Python, LangChain, ChromaDB, FastAPI, HuggingFace"
                   data-features="RAG avancé | Fine-tuning | Code review auto | Base connaissances"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuCJCsWrr7XhXHdayMqdlbcfL0YrEkYgLjc_3Ptjmzt9iXZb-jzEC8_mI9BGyyNbkb-juaUqne6bJNkF-mb9xkU-qP63w5rJSSGVS9gnQOw5-tW2DNam3C_WTqPCQeJgOTBfqdPsAHenyxkocEoGG6KLDtB9pVvABRC9WquKRoak3GK1TIzp130biGkS1-0mh_b-9227k5K7oIpiHLvTHZjeWhCR4_HSi-A16DcdY6KfZ7qFnwHbSiJW-VjNnSurAS-h9zmgDKtkDD8">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCJCsWrr7XhXHdayMqdlbcfL0YrEkYgLjc_3Ptjmzt9iXZb-jzEC8_mI9BGyyNbkb-juaUqne6bJNkF-mb9xkU-qP63w5rJSSGVS9gnQOw5-tW2DNam3C_WTqPCQeJgOTBfqdPsAHenyxkocEoGG6KLDtB9pVvABRC9WquKRoak3GK1TIzp130biGkS1-0mh_b-9227k5K7oIpiHLvTHZjeWhCR4_HSi-A16DcdY6KfZ7qFnwHbSiJW-VjNnSurAS-h9zmgDKtkDD8" loading="lazy" decoding="async" alt="Shadow Protocol AI">
                        <div class="project-article__badge project-article__badge--green">
                            <span>07_AI</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Shadow Protocol AI</h2>
                        <p class="project-article__desc">Custom LLM-powered assistant with RAG capabilities for enterprise knowledge management and automated code review.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--green">#AI</span>
                            <span class="project-tag project-tag--orange">#LLM</span>
                            <span class="project-tag project-tag--neutral">#RAG</span>
                        </div>
                    </div>
                </a>

                <!-- Project 8: Grid Runner -->
                <a href="javascript:void(0)" class="project-article project-article--orange project-article--link portfolio-card"
                   data-category="gamedev"
                   data-title="Grid Runner"
                   data-desc="Jeu d'arène multijoueur cyberpunk au rythme effréné développé avec Godot Engine. Intègre le netcode rollback pour une expérience réseau fluide et la génération procédurale de cartes pour une rejouabilité infinie."
                   data-tech="Godot 4, GDScript, GDNative, ENet"
                   data-features="Netcode rollback | Cartes procédurales | Mode battle royale | Spectateur live"
                   data-img="https://lh3.googleusercontent.com/aida-public/AB6AXuAozYEJo5Gr3ZIHjhPNlP88xDNQTOoHaGko5CYayBS3NkeYp6WGv_ZEF2weyxOCpSBesYcbf_oMGCB4i4HN8yQ6KZvsM9gP6Lin3Vd8HHgFQMEf1pN6huilxoKhzEBFoIxAYO9VnuXKRnEmern9bjlbPVXUsC00CECyUzGPVlrZUAjTmUaQMYGJJPcZNwTOUI8ZH9p0-os16MjRh_xJbDfTakyKjmJjPrlS0o-UxyET7XlSTK_g1ivU4G9_8Q7lvJNHdX5GqPe7W7o">
                    <div class="project-article__thumb">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAozYEJo5Gr3ZIHjhPNlP88xDNQTOoHaGko5CYayBS3NkeYp6WGv_ZEF2weyxOCpSBesYcbf_oMGCB4i4HN8yQ6KZvsM9gP6Lin3Vd8HHgFQMEf1pN6huilxoKhzEBFoIxAYO9VnuXKRnEmern9bjlbPVXUsC00CECyUzGPVlrZUAjTmUaQMYGJJPcZNwTOUI8ZH9p0-os16MjRh_xJbDfTakyKjmJjPrlS0o-UxyET7XlSTK_g1ivU4G9_8Q7lvJNHdX5GqPe7W7o" loading="lazy" decoding="async" alt="Grid Runner">
                        <div class="project-article__badge project-article__badge--orange">
                            <span>08_GM</span>
                        </div>
                        <div class="project-article__hover-overlay">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </div>
                    </div>
                    <div class="project-article__body">
                        <h2 class="project-article__title">Grid Runner</h2>
                        <p class="project-article__desc">Fast-paced cyberpunk multiplayer arena game built with Godot Engine, featuring netcode rollback and procedural map generation.</p>
                        <div class="project-article__tags">
                            <span class="project-tag project-tag--orange">#GAMEDEV</span>
                            <span class="project-tag project-tag--green">#GODOT</span>
                            <span class="project-tag project-tag--neutral">#MULTIPLAYER</span>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </section>

    <!-- ================================
         PROJECT DETAIL MODAL
         ================================ -->
    <div class="portfolio-modal" id="portfolio-modal">
        <div class="portfolio-modal__overlay"></div>
        <div class="portfolio-modal__content">
            <button class="portfolio-modal__close" id="modal-close">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="portfolio-modal__image-wrap">
                <img class="portfolio-modal__image" id="modal-image" src="" loading="lazy" decoding="async" alt="">
            </div>
            <div class="portfolio-modal__body">
                <h2 class="portfolio-modal__title" id="modal-title"></h2>
                <p class="portfolio-modal__desc" id="modal-desc"></p>

                <div class="portfolio-modal__section">
                    <h3 class="portfolio-modal__label label-caps">
                        <span class="material-symbols-outlined" style="font-size:16px;">code</span>
                        Tech Stack
                    </h3>
                    <div class="portfolio-modal__tech" id="modal-tech"></div>
                </div>

                <div class="portfolio-modal__section">
                    <h3 class="portfolio-modal__label label-caps">
                        <span class="material-symbols-outlined" style="font-size:16px;">star</span>
                        Features
                    </h3>
                    <ul class="portfolio-modal__features" id="modal-features"></ul>
                </div>

                <div class="portfolio-modal__actions">
                    <a href="#" class="portfolio-modal__btn portfolio-modal__btn--primary" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
                        Live Demo
                    </a>
                    <a href="#" class="portfolio-modal__btn portfolio-modal__btn--secondary" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="font-size:16px;">github</span>
                        Source Code
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================
         STATS SECTION
         ================================ -->
    <section class="portfolio-stats" id="portfolio-stats">
        <div class="container">
            <div class="portfolio-stats__grid">
                <div class="portfolio-stat-card" style="border-top-color: var(--primary);">
                    <span class="portfolio-stat-card__number">12+</span>
                    <span class="portfolio-stat-card__label label-caps">Projects</span>
                    <span class="portfolio-stat-card__sub code-sm">Production &amp; Research</span>
                </div>
                <div class="portfolio-stat-card" style="border-top-color: var(--secondary-bright);">
                    <span class="portfolio-stat-card__number">6</span>
                    <span class="portfolio-stat-card__label label-caps">Domains</span>
                    <span class="portfolio-stat-card__sub code-sm">Full-Spectrum Expertise</span>
                </div>
                <div class="portfolio-stat-card" style="border-top-color: var(--primary);">
                    <span class="portfolio-stat-card__number">3+</span>
                    <span class="portfolio-stat-card__label label-caps">Years XP</span>
                    <span class="portfolio-stat-card__sub code-sm">Continuous Growth</span>
                </div>
                <div class="portfolio-stat-card" style="border-top-color: var(--secondary-bright);">
                    <span class="portfolio-stat-card__number">100%</span>
                    <span class="portfolio-stat-card__label label-caps">Passion</span>
                    <span class="portfolio-stat-card__sub code-sm">No Cap. Just Code.</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         CTA SECTION
         ================================ -->
    <section class="projects-section">
        <div class="container">
            <div class="projects-cta">
                <div class="projects-cta__bar"></div>
                <div class="projects-cta__inner">
                    <div>
                        <h3>Got a project in mind?</h3>
                        <p>From pentest rigs to full-stack platforms — let's build something that pushes boundaries.</p>
                    </div>
                    <a href="index.php#contact" class="projects-cta__btn">INITIATE_CONTACT</a>
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
                <a href="#projects" class="nav-bottom__link active" data-section="projects">
                    <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' 1;">grid_view</span>
                    <span class="nav-bottom__text">Projects</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#contact" class="nav-bottom__link">
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
        // — Portfolio Filter —
        const filterButtons = document.querySelectorAll('.portfolio-filter-btn');
        const projectCards = document.querySelectorAll('.portfolio-card');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active state on buttons
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.getAttribute('data-filter');

                projectCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    if (filter === 'all' || category === filter) {
                        card.style.display = '';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(24px)';
                        requestAnimationFrame(() => {
                            card.style.transition = 'opacity 0.4s cubic-bezier(0.16,1,0.3,1), transform 0.4s cubic-bezier(0.16,1,0.3,1)';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        });
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(16px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // — Toggle filter bar on mobile —
        const toggleFilterBtn = document.getElementById('toggle-filter-btn');
        const filtersSection = document.getElementById('portfolio-filters');
        if (toggleFilterBtn && filtersSection) {
            toggleFilterBtn.addEventListener('click', () => {
                filtersSection.scrollIntoView({ behavior: 'smooth' });
            });
        }

        // — Project Detail Modal —
        const modal = document.getElementById('portfolio-modal');
        const modalOverlay = modal.querySelector('.portfolio-modal__overlay');
        const modalClose = document.getElementById('modal-close');
        const modalImage = document.getElementById('modal-image');
        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-desc');
        const modalTech = document.getElementById('modal-tech');
        const modalFeatures = document.getElementById('modal-features');

        function openModal(card) {
            const title = card.getAttribute('data-title');
            const desc = card.getAttribute('data-desc');
            const tech = card.getAttribute('data-tech');
            const features = card.getAttribute('data-features');
            const img = card.getAttribute('data-img');

            modalImage.src = img;
            modalImage.alt = title;
            modalTitle.textContent = title;
            modalDesc.textContent = desc;

            // Tech stack pills
            modalTech.innerHTML = '';
            if (tech) {
                tech.split(',').forEach(t => {
                    const pill = document.createElement('span');
                    pill.className = 'portfolio-modal__pill';
                    pill.textContent = t.trim();
                    modalTech.appendChild(pill);
                });
            }

            // Features list
            modalFeatures.innerHTML = '';
            if (features) {
                features.split('|').forEach(f => {
                    const li = document.createElement('li');
                    li.className = 'portfolio-modal__feature';
                    li.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px;color:var(--primary);">check_circle</span> ' + f.trim();
                    modalFeatures.appendChild(li);
                });
            }

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        projectCards.forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                openModal(card);
            });
        });

        modalClose.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // — Scroll-triggered animations —
        const animateElements = document.querySelectorAll(
            '.portfolio-card, .portfolio-stat-card, .projects-cta'
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

        // — Smooth scroll for anchor links —
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href && href.startsWith('#') && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // — Active nav link on scroll —
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-bottom__link');

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
                        if (!section) return;
                        link.classList.toggle("active", section === id);
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
              response: "Mes projets recents :\n• Neural Breach Shield — Penetration testing autonome\n• Abidjan Terminal UI — Dashboard logistique\n• Eburnie Chronicles — RPG narratif (Unreal 5)\n• Core Infra Automata — Orchestrateur multi-cloud\n• Phantom Recon Engine — OSINT threat hunting\n• Lagune Commerce — E-commerce full-stack\n• Shadow Protocol AI — LLM & RAG\n• Grid Runner — Multiplayer cyberpunk arena\n\nScrolle jusqu'a PROJECTS pour les decouvrir !" },
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

    <link rel="preload" as="image" href="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c">

</body>
</html>
