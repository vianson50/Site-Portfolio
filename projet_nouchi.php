<?php
/**
 * BLACK_PROTOCOL — Case Study: Nouchi Lexicon App
 * Page dédiée au projet phare : Dictionnaire de Nouchi (Mobile App)
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";

$lang = "fr";
$charset = "UTF-8";
$seoTitle = "Nouchi Lexicon App — Case Study | BLACK_PROTOCOL";
$seoDesc =
    "Découvrez le projet Nouchi Lexicon App : une application mobile interactive servant de dictionnaire pour l'argot ivoirien (Nouchi), développée avec Flutter.";
$seoKeywords = [
    "nouchi",
    "flutter",
    "mobile app",
    "dictionnaire",
    "argot ivoirien",
    "case study",
];
$title = $seoTitle;
$year = date("Y");

$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

// ─── APK Metadata ───
$apkMeta = null;
$apkMetaFile = __DIR__ . "/uploads/apk/apk_meta.json";
if (file_exists($apkMetaFile)) {
    $tmp = json_decode(file_get_contents($apkMetaFile), true);
    if (
        $tmp &&
        file_exists(__DIR__ . "/uploads/apk/" . ($tmp["filename"] ?? ""))
    ) {
        $apkMeta = $tmp;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="<?= $charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <?= renderMeta($seoTitle, $seoDesc, "article", null, $seoKeywords) ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"></noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="Favicon/android-chrome-512x512.png">

    <style>
        :root{--void:#0a0a0b;--surface:#161618;--primary:#ff8200;--primary-dim:#ffb785;--primary-glow:rgba(255,130,0,0.35);--secondary:#009e60;--secondary-bright:#61dd98;--secondary-glow:rgba(0,158,96,0.3);--white:#ffffff;--on-surface:#f4ded2;--on-surface-variant:#dec1af;--outline:#a68b7b;--font-display:"Space Grotesk",sans-serif;--font-body:"Inter",sans-serif}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font-body);background:var(--void);color:var(--on-surface);overflow-x:hidden;line-height:1.6}
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}

        /* ── Top Header ── */
        .top-header{position:fixed;top:0;left:0;right:0;z-index:1000;background:rgba(10,10,11,0.92);backdrop-filter:blur(12px);border-bottom:1px solid rgba(166,139,123,0.1);height:64px;display:flex;align-items:center}
        .top-header__inner{max-width:1200px;width:100%;margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:space-between}
        .top-header__brand{display:flex;align-items:center;gap:10px;text-decoration:none}
        .top-header__avatar{width:36px;height:36px;border-radius:50%;overflow:hidden;border:2px solid var(--primary)}
        .top-header__avatar img{width:100%;height:100%;object-fit:cover}
        .top-header__name{font-family:var(--font-display);font-weight:700;font-size:14px;color:var(--primary);letter-spacing:1px}
        .top-header__back{display:flex;align-items:center;gap:6px;color:var(--on-surface-variant);text-decoration:none;font-size:13px;font-family:var(--font-display);font-weight:500;letter-spacing:0.5px;transition:color .2s}
        .top-header__back:hover{color:var(--primary)}

        /* ── Container ── */
        .container{max-width:960px;margin:0 auto;padding:0 20px}

        /* ── Hero Section ── */
        .case-hero{padding:100px 0 60px;position:relative;overflow:hidden}
        .case-hero::before{content:'';position:absolute;top:0;left:0;right:0;height:400px;background:radial-gradient(ellipse at 50% 0%,var(--secondary-glow) 0%,transparent 60%);pointer-events:none}
        .case-hero__badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid rgba(0,158,96,0.3);border-radius:20px;font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:1.5px;color:var(--secondary-bright);margin-bottom:20px;background:rgba(0,158,96,0.06)}
        .case-hero__badge .dot{width:6px;height:6px;border-radius:50%;background:var(--secondary-bright);animation:pulse-dot 2s ease-in-out infinite}
        @keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.3}}
        .case-hero__title{font-family:var(--font-display);font-size:clamp(32px,6vw,56px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
        .case-hero__subtitle{font-size:18px;color:var(--on-surface-variant);max-width:640px;line-height:1.6;margin-bottom:32px}
        .case-hero__meta{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:32px}
        .case-hero__tag{padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;font-family:var(--font-display);letter-spacing:0.5px}
        .case-hero__tag--green{background:rgba(0,158,96,0.12);color:var(--secondary-bright);border:1px solid rgba(0,158,96,0.25)}
        .case-hero__tag--orange{background:rgba(255,130,0,0.12);color:var(--primary-dim);border:1px solid rgba(255,130,0,0.25)}
        .case-hero__tag--neutral{background:rgba(166,139,123,0.1);color:var(--outline);border:1px solid rgba(166,139,123,0.2)}

        /* ── Section Shared ── */
        .case-section{padding:60px 0;border-top:1px solid rgba(166,139,123,0.08)}
        .case-section__label{display:flex;align-items:center;gap:8px;margin-bottom:12px}
        .case-section__label span:first-child{font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:2px;color:var(--primary)}
        .case-section__label .bar{width:32px;height:2px;background:var(--primary)}
        .case-section__title{font-family:var(--font-display);font-size:28px;font-weight:700;color:var(--white);margin-bottom:24px}

        /* ── Overview ── */
        .case-overview{display:grid;grid-template-columns:1fr 1fr;gap:40px}
        .case-overview__text{font-size:15px;color:var(--on-surface-variant);line-height:1.8}
        .case-overview__text p{margin-bottom:16px}
        .case-overview__stats{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .case-stat{padding:20px;background:var(--surface);border:1px solid rgba(166,139,123,0.08);border-radius:10px;text-align:center}
        .case-stat__value{font-family:var(--font-display);font-size:28px;font-weight:700;color:var(--secondary-bright)}
        .case-stat__label{font-size:11px;font-family:var(--font-display);letter-spacing:1px;color:var(--outline);margin-top:4px}

        /* ── Tech Stack ── */
        .tech-grid{display:flex;flex-wrap:wrap;gap:12px}
        .tech-pill{display:flex;align-items:center;gap:8px;padding:10px 18px;background:var(--surface);border:1px solid rgba(166,139,123,0.1);border-radius:8px;font-family:var(--font-display);font-size:13px;font-weight:500;color:var(--on-surface-variant);transition:border-color .2s}
        .tech-pill:hover{border-color:var(--secondary)}
        .tech-pill .material-symbols-outlined{font-size:18px;color:var(--secondary-bright)}

        /* ── Video Section ── */
        .case-video{max-width:480px;margin:0 auto;border-radius:16px;overflow:hidden;border:1px solid rgba(166,139,123,0.1);background:var(--surface);position:relative;box-shadow:0 8px 40px rgba(0,0,0,0.5)}
        .case-video video{width:100%;display:block;max-height:480px;object-fit:cover}
        .case-video__label{padding:12px 16px;display:flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:12px;letter-spacing:1px;color:var(--outline);border-top:1px solid rgba(166,139,123,0.08)}
        .case-video__label .material-symbols-outlined{font-size:16px;color:var(--primary)}

        /* ── Screenshots Gallery ── */
        .screenshots-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .screenshot-card{position:relative;border-radius:10px;overflow:hidden;border:1px solid rgba(166,139,123,0.1);background:var(--surface);cursor:pointer;transition:transform .3s,border-color .3s}
        .screenshot-card:hover{transform:translateY(-4px);border-color:var(--secondary)}
        .screenshot-card img{width:100%;display:block;aspect-ratio:9/16;object-fit:cover}
        .screenshot-card__overlay{position:absolute;inset:0;background:rgba(10,10,11,0.6);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s}
        .screenshot-card:hover .screenshot-card__overlay{opacity:1}
        .screenshot-card__overlay .material-symbols-outlined{font-size:32px;color:var(--white)}

        /* ── Features ── */
        .features-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .feature-card{padding:24px;background:var(--surface);border:1px solid rgba(166,139,123,0.08);border-radius:10px;transition:border-color .2s}
        .feature-card:hover{border-color:var(--secondary)}
        .feature-card__icon{width:40px;height:40px;border-radius:8px;background:rgba(0,158,96,0.1);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
        .feature-card__icon .material-symbols-outlined{font-size:20px;color:var(--secondary-bright)}
        .feature-card__title{font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--white);margin-bottom:8px}
        .feature-card__desc{font-size:13px;color:var(--outline);line-height:1.6}

        /* ── Timeline / Process ── */
        .process-timeline{display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px}
        .process-timeline::before{content:'';position:absolute;left:8px;top:0;bottom:0;width:2px;background:rgba(0,158,96,0.2)}
        .process-step{position:relative;padding:20px 0 20px 24px}
        .process-step::before{content:'';position:absolute;left:-24px;top:26px;width:12px;height:12px;border-radius:50%;background:var(--secondary);border:3px solid var(--void);z-index:1}
        .process-step__num{font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:2px;color:var(--secondary-bright);margin-bottom:6px}
        .process-step__title{font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--white);margin-bottom:6px}
        .process-step__desc{font-size:13px;color:var(--outline);line-height:1.6}

        /* ── CTA Back ── */
        .case-cta{padding:60px 0;text-align:center}
        .case-cta__btn{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:var(--secondary);color:var(--void);font-family:var(--font-display);font-size:14px;font-weight:600;letter-spacing:0.5px;border-radius:8px;text-decoration:none;transition:all .2s;border:none;cursor:pointer}
        .case-cta__btn:hover{background:var(--secondary-bright);transform:translateY(-2px);box-shadow:0 8px 30px var(--secondary-glow)}
        .case-cta__btn--outline{background:transparent;color:var(--primary);border:1px solid rgba(255,130,0,0.3)}
        .case-cta__btn--outline:hover{background:rgba(255,130,0,0.1);box-shadow:0 8px 30px var(--primary-glow)}
        .case-cta__buttons{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}

        /* ── Footer ── */
        .case-footer{padding:32px 0;border-top:1px solid rgba(166,139,123,0.08);text-align:center;font-size:12px;color:var(--outline);font-family:var(--font-display);letter-spacing:0.5px}

        /* ── Lightbox ── */
        .lightbox{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.92);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;cursor:zoom-out}
        .lightbox.active{opacity:1;pointer-events:all}
        .lightbox img{max-width:90vw;max-height:90vh;border-radius:8px;object-fit:contain}
        .lightbox__close{position:absolute;top:20px;right:24px;color:var(--white);cursor:pointer;font-size:28px;background:none;border:none}
        .lightbox__nav{position:absolute;top:50%;transform:translateY(-50%);color:var(--white);cursor:pointer;font-size:36px;background:rgba(255,255,255,0.1);border:none;border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;transition:background .2s}
        .lightbox__nav:hover{background:rgba(255,255,255,0.2)}
        .lightbox__prev{left:16px}
        .lightbox__next{right:16px}

        /* ── Responsive ── */
        @media(max-width:768px){
            .case-overview{grid-template-columns:1fr}
            .screenshots-grid{grid-template-columns:repeat(2,1fr)}
            .features-grid{grid-template-columns:1fr}
            .case-hero__title{font-size:28px}
            .case-hero__subtitle{font-size:15px}
            .case-overview__stats{grid-template-columns:1fr 1fr}
        }
        @media(max-width:480px){
            .screenshots-grid{grid-template-columns:1fr}
            .case-cta__buttons{flex-direction:column;align-items:center}
        }

        /* ── Animations ── */
        .fade-up{opacity:0;transform:translateY(30px);transition:opacity .6s ease,transform .6s ease}
        .fade-up.visible{opacity:1;transform:translateY(0)}

        /* ── APK Download Section ── */
        .apk-section{padding:60px 0}
        .apk-card{background:var(--surface);border:1px solid rgba(255,130,0,0.1);border-radius:16px;padding:40px;text-align:center;max-width:600px;margin:0 auto;position:relative;overflow:hidden}
        .apk-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--secondary),var(--primary));opacity:.6}
        .apk-card__icon{width:64px;height:64px;margin:0 auto 20px;background:linear-gradient(135deg,rgba(0,158,96,0.15),rgba(0,158,96,0.05));border:1px solid rgba(0,158,96,0.2);border-radius:16px;display:flex;align-items:center;justify-content:center}
        .apk-card__icon .material-symbols-outlined{font-size:32px;color:var(--secondary-bright)}
        .apk-card__title{font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--white);margin-bottom:8px}
        .apk-card__desc{font-size:14px;color:var(--on-surface-variant);margin-bottom:24px;line-height:1.6}
        .apk-info{display:flex;justify-content:center;gap:24px;margin-bottom:28px;flex-wrap:wrap}
        .apk-info__item{display:flex;align-items:center;gap:6px;font-family:var(--font-display);font-size:12px;color:var(--outline);letter-spacing:.3px}
        .apk-info__item .material-symbols-outlined{font-size:16px;color:var(--primary)}
        .apk-download-btn{display:inline-flex;align-items:center;gap:10px;padding:16px 36px;background:var(--secondary);color:var(--void);font-family:var(--font-display);font-size:14px;font-weight:700;letter-spacing:.5px;border-radius:12px;text-decoration:none;transition:all .25s;border:none;cursor:pointer}
        .apk-download-btn:hover{background:var(--secondary-bright);transform:translateY(-2px);box-shadow:0 8px 30px var(--secondary-glow)}
        .apk-download-btn .material-symbols-outlined{font-size:20px}
        .apk-download-btn--disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
        .apk-no-file{padding:16px 24px;background:rgba(255,130,0,0.06);border:1px dashed rgba(255,130,0,0.2);border-radius:10px;display:flex;align-items:center;gap:10px;justify-content:center;font-family:var(--font-display);font-size:13px;color:var(--outline)}
        .apk-no-file .material-symbols-outlined{font-size:18px;color:var(--primary);opacity:.5}
        .apk-admin-bar{margin-top:20px;display:flex;justify-content:center;gap:12px}
        .apk-admin-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:.5px;border-radius:8px;cursor:pointer;transition:all .2s;border:1px solid rgba(255,130,0,0.2);background:rgba(255,130,0,0.06);color:var(--primary)}
        .apk-admin-btn:hover{background:rgba(255,130,0,0.15)}
        .apk-admin-btn--danger{border-color:rgba(255,95,87,0.3);background:rgba(255,95,87,0.06);color:#ff5f57}
        .apk-admin-btn--danger:hover{background:rgba(255,95,87,0.15)}
        .apk-admin-btn .material-symbols-outlined{font-size:14px}

        /* ── APK Upload Modal ── */
        .apk-modal{position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.85);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(4px)}
        .apk-modal.active{opacity:1;pointer-events:all}
        .apk-modal__box{background:var(--surface);border:1px solid rgba(255,130,0,0.12);border-radius:16px;padding:32px;width:90%;max-width:480px;position:relative}
        .apk-modal__close{position:absolute;top:12px;right:12px;background:none;border:none;color:var(--outline);cursor:pointer;font-size:20px;transition:color .2s}
        .apk-modal__close:hover{color:var(--primary)}
        .apk-modal__title{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--white);margin-bottom:20px;display:flex;align-items:center;gap:8px}
        .apk-modal__title .material-symbols-outlined{font-size:20px;color:var(--primary)}
        .apk-modal__field{margin-bottom:16px}
        .apk-modal__field label{display:block;font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:.5px;color:var(--outline);margin-bottom:6px;text-transform:uppercase}
        .apk-modal__field input[type="text"],.apk-modal__field textarea{width:100%;padding:10px 14px;background:var(--void);border:1px solid rgba(166,139,123,0.15);border-radius:8px;color:var(--on-surface);font-family:var(--font-body);font-size:13px;transition:border-color .2s}
        .apk-modal__field input[type="text"]:focus,.apk-modal__field textarea:focus{outline:none;border-color:rgba(255,130,0,0.4)}
        .apk-modal__field textarea{resize:vertical;min-height:70px}
        .apk-dropzone{border:2px dashed rgba(255,130,0,0.2);border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;transition:all .25s;background:rgba(255,130,0,0.02);margin-bottom:16px}
        .apk-dropzone:hover,.apk-dropzone.dragover{border-color:rgba(255,130,0,0.4);background:rgba(255,130,0,0.06)}
        .apk-dropzone__icon{font-size:36px;color:var(--primary);opacity:.5;margin-bottom:8px}
        .apk-dropzone__text{font-family:var(--font-display);font-size:12px;color:var(--outline);letter-spacing:.3px}
        .apk-dropzone__text strong{color:var(--primary)}
        .apk-dropzone input[type="file"]{display:none}
        .apk-modal__submit{width:100%;padding:14px;background:var(--secondary);color:var(--void);font-family:var(--font-display);font-size:13px;font-weight:700;letter-spacing:.5px;border:none;border-radius:10px;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
        .apk-modal__submit:hover:not(:disabled){background:var(--secondary-bright);transform:translateY(-1px)}
        .apk-modal__submit:disabled{opacity:.5;cursor:not-allowed}
        .apk-modal__msg{margin-top:12px;padding:10px 14px;border-radius:8px;font-family:var(--font-display);font-size:12px;letter-spacing:.3px;display:none}
        .apk-modal__msg--success{display:block;background:rgba(0,158,96,0.1);color:var(--secondary-bright);border:1px solid rgba(0,158,96,0.2)}
        .apk-modal__msg--error{display:block;background:rgba(255,95,87,0.1);color:#ff5f57;border:1px solid rgba(255,95,87,0.2)}
    </style>
</head>
<body>

    <!-- ================================
         TOP HEADER BAR
         ================================ -->
    <header class="top-header">
        <div class="top-header__inner">
            <a href="index.php" class="top-header__brand">
                <div class="top-header__avatar">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEAvPC3mMvcv2gWWyc_wuirzRXkpGPlYEfhwZVU0E84KY-H0KN8coRKlNeMU3UTBZ6Jivc1MYRC1E_HQolj0_NDo9m9L_FT4jBnEiurMB46NTlQ4M7hXVZmjU9pF8gHqkXUA9rXVlSzNcPogw5bzZIcp_fiaWtZ18PLVBzSPbO9_W_L09rvE4mXPoBL05pP9s9E4jVtuQwplRDudUdd1ZCCFpwv1Jg8jZX9_BLXwgNOb_waq_L6LcSOImqsrC-DkmyAGJHz7Gi1c" alt="BLACK_PROTOCOL">
                </div>
                <span class="top-header__name">BLACK_PROTOCOL</span>
            </a>
            <a href="index.php#projects" class="top-header__back">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                RETOUR AUX PROJETS
            </a>
        </div>
    </header>

    <!-- ================================
         HERO
         ================================ -->
    <section class="case-hero">
        <div class="container">
            <div class="case-hero__badge">
                <span class="dot"></span>
                CASE STUDY // MOBILE APP
            </div>
            <h1 class="case-hero__title">Nouchi Lexicon App</h1>
            <p class="case-hero__subtitle">
                Une application mobile interactive servant de dictionnaire pour l'argot ivoirien (Nouchi).
                Développée avec Flutter pour offrir une expérience fluide hors ligne. Un projet phare qui démontre
                la capacité à résoudre un problème culturel local avec de la technologie moderne.
            </p>
            <div class="case-hero__meta">
                <span class="case-hero__tag case-hero__tag--green">#FLUTTER</span>
                <span class="case-hero__tag case-hero__tag--orange">#DART</span>
                <span class="case-hero__tag case-hero__tag--neutral">#MOBILE_UI</span>
            </div>
        </div>
    </section>

    <!-- ================================
         VIDEO DEMO
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>VIDEO_DEMO</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Présentation en action</h2>
            <div class="case-video fade-up">
                <video controls preload="metadata" poster="Presentation/Capture1 apk.jpg">
                    <source src="Presentation/Video presatif de mo APK.mp4" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture vidéo.
                </video>
                <div class="case-video__label">
                    <span class="material-symbols-outlined">play_circle</span>
                    VIDEO_PREVIEW — Nouchi Lexicon App APK
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         OVERVIEW
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>OVERVIEW</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Le Problème</h2>
            <div class="case-overview fade-up">
                <div class="case-overview__text">
                    <p>
                        Le <strong style="color:var(--secondary-bright)">Nouchi</strong> est l'argot ivoirien, une langue vivante et en constante évolution parlée par des millions de personnes en Côte d'Ivoire et dans la diaspora africaine. Pourtant, il n'existait aucun outil numérique moderne et accessible pour le documenter, l'apprendre et le préserver.
                    </p>
                    <p>
                        <strong style="color:var(--primary-dim)">Nouchi Lexicon App</strong> comble ce vide en offrant un dictionnaire mobile interactif, rapide et entièrement fonctionnel hors ligne — conçu pour les réalités de connectivité en Afrique de l'Ouest.
                    </p>
                </div>
                <div class="case-overview__stats">
                    <div class="case-stat">
                        <div class="case-stat__value">100%</div>
                        <div class="case-stat__label">OFFLINE</div>
                    </div>
                    <div class="case-stat">
                        <div class="case-stat__value">Flutter</div>
                        <div class="case-stat__label">FRAMEWORK</div>
                    </div>
                    <div class="case-stat">
                        <div class="case-stat__value">Dart</div>
                        <div class="case-stat__label">LANGUAGE</div>
                    </div>
                    <div class="case-stat">
                        <div class="case-stat__value">UI/UX</div>
                        <div class="case-stat__label">DESIGN</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         FEATURES
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>FEATURES</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Fonctionnalités clés</h2>
            <div class="features-grid fade-up">
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                    <div class="feature-card__title">Recherche instantanée</div>
                    <div class="feature-card__desc">Trouvez n'importe quel mot Nouchi en quelques millisecondes grâce à l'indexation locale SQLite.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <span class="material-symbols-outlined">cloud_off</span>
                    </div>
                    <div class="feature-card__title">Mode hors ligne</div>
                    <div class="feature-card__desc">Fonctionne entièrement sans connexion internet — essentiel pour les zones à faible connectivité.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <span class="material-symbols-outlined">volume_up</span>
                    </div>
                    <div class="feature-card__title">Audio prononciation</div>
                    <div class="feature-card__desc">Écoutez la prononciation correcte de chaque mot pour apprendre naturellement.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <span class="material-symbols-outlined">bookmark</span>
                    </div>
                    <div class="feature-card__title">Favoris & Historique</div>
                    <div class="feature-card__desc">Sauvegardez vos mots préférés et retrouvez votre historique de recherches.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         TECH STACK
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>TECH_STACK</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Stack technique</h2>
            <div class="tech-grid fade-up">
                <div class="tech-pill">
                    <span class="material-symbols-outlined">phone_android</span>
                    Flutter
                </div>
                <div class="tech-pill">
                    <span class="material-symbols-outlined">code</span>
                    Dart
                </div>
                <div class="tech-pill">
                    <span class="material-symbols-outlined">storage</span>
                    SQLite
                </div>
                <div class="tech-pill">
                    <span class="material-symbols-outlined">cloud</span>
                    Firebase
                </div>
                <div class="tech-pill">
                    <span class="material-symbols-outlined">palette</span>
                    Material Design 3
                </div>
                <div class="tech-pill">
                    <span class="material-symbols-outlined">api</span>
                    REST API
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         PROCESS
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>PROCESS</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Approche de développement</h2>
            <div class="process-timeline fade-up">
                <div class="process-step">
                    <div class="process-step__num">PHASE_01</div>
                    <div class="process-step__title">Recherche & Collecte</div>
                    <div class="process-step__desc">Collecte exhaustive de mots et expressions Nouchi auprès de locuteurs natifs, réseaux sociaux et documents existants.</div>
                </div>
                <div class="process-step">
                    <div class="process-step__num">PHASE_02</div>
                    <div class="process-step__title">Architecture & Design</div>
                    <div class="process-step__desc">Conception de l'architecture offline-first avec SQLite, wireframes UI/UX adaptés au public ivoirien, et prototypage sous Figma.</div>
                </div>
                <div class="process-step">
                    <div class="process-step__num">PHASE_03</div>
                    <div class="process-step__title">Développement Flutter</div>
                    <div class="process-step__desc">Implémentation du dictionnaire avec Flutter/Dart, intégration de la base de données locale, et animations fluides pour une UX premium.</div>
                </div>
                <div class="process-step">
                    <div class="process-step__num">PHASE_04</div>
                    <div class="process-step__title">Test & Déploiement</div>
                    <div class="process-step__desc">Tests sur appareils Android réels, optimisation des performances, et préparation du build APK pour distribution.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         SCREENSHOTS
         ================================ -->
    <section class="case-section">
        <div class="container">
            <div class="case-section__label">
                <span>SCREENSHOTS</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title">Captures d'écran</h2>
            <div class="screenshots-grid fade-up">
                <div class="screenshot-card" data-img="Presentation/Capture1 apk.jpg">
                    <img src="Presentation/Capture1 apk.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 1">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
                <div class="screenshot-card" data-img="Presentation/Capture2 apk.jpg">
                    <img src="Presentation/Capture2 apk.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 2">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
                <div class="screenshot-card" data-img="Presentation/capture3.jpg">
                    <img src="Presentation/capture3.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 3">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
                <div class="screenshot-card" data-img="Presentation/capture4.jpg">
                    <img src="Presentation/capture4.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 4">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
                <div class="screenshot-card" data-img="Presentation/capture5.jpg">
                    <img src="Presentation/capture5.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 5">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
                <div class="screenshot-card" data-img="Presentation/capture6.jpg">
                    <img src="Presentation/capture6.jpg" loading="lazy" decoding="async" alt="Nouchi Lexicon - Écran 6">
                    <div class="screenshot-card__overlay">
                        <span class="material-symbols-outlined">zoom_in</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         APK DOWNLOAD
         ================================ -->
    <section class="apk-section" id="apk-download">
        <div class="container">
            <div class="case-section__label fade-up">
                <span>TÉLÉCHARGEMENT</span>
                <span class="bar"></span>
            </div>
            <h2 class="case-section__title fade-up" style="margin-bottom:32px;">Télécharger l'APK</h2>

            <div class="apk-card fade-up">
                <div class="apk-card__icon">
                    <span class="material-symbols-outlined">android</span>
                </div>
                <h3 class="apk-card__title">Nouchi Lexicon App</h3>
                <p class="apk-card__desc">Téléchargez l'application directement sur votre appareil Android. Activez \&quot;Sources inconnues\&quot; dans vos paramètres pour installer l'APK.</p>

<?php if ($apkMeta): ?>
                <div class="apk-info">
                    <div class="apk-info__item">
                        <span class="material-symbols-outlined">new_releases</span>
                        v<?= htmlspecialchars($apkMeta["version"] ?? "1.0.0") ?>
                    </div>
                    <div class="apk-info__item">
                        <span class="material-symbols-outlined">sd_card</span>
                        <?= htmlspecialchars(
                            $apkMeta["size_formatted"] ?? "?",
                        ) ?>
                    </div>
                    <div class="apk-info__item">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <?= htmlspecialchars(
                            date(
                                "d/m/Y",
                                strtotime($apkMeta["upload_date"] ?? "now"),
                            ),
                        ) ?>
                    </div>
<?php if (!empty($apkMeta["downloads"])): ?>
                    <div class="apk-info__item">
                        <span class="material-symbols-outlined">download</span>
                        <?= number_format(
                            $apkMeta["downloads"],
                        ) ?> téléchargements
                    </div>
<?php endif; ?>
                </div>

<?php if (!empty($apkMeta["changelog"])): ?>
                <div style="margin-bottom:20px;padding:12px 16px;background:rgba(255,130,0,0.04);border:1px solid rgba(255,130,0,0.08);border-radius:8px;text-align:left;">
                    <div style="font-family:var(--font-display);font-size:10px;font-weight:600;letter-spacing:.5px;color:var(--primary);margin-bottom:6px;text-transform:uppercase;">Nouveautés</div>
                    <div style="font-size:13px;color:var(--on-surface-variant);line-height:1.5;"><?= nl2br(
                        htmlspecialchars($apkMeta["changelog"]),
                    ) ?></div>
                </div>
<?php endif; ?>

                <a href="apk_handler.php?download=1" class="apk-download-btn">
                    <span class="material-symbols-outlined">download</span>
                    TÉLÉCHARGER L'APK
                </a>

<?php else: ?>
                <div class="apk-no-file">
                    <span class="material-symbols-outlined">cloud_off</span>
                    Aucun APK disponible pour le moment.
                </div>
<?php endif; ?>

<?php if (isAdmin()): ?>
                <div class="apk-admin-bar">
<?php if ($apkMeta): ?>
                    <button class="apk-admin-btn apk-admin-btn--danger" onclick="deleteApk()">
                        <span class="material-symbols-outlined">delete</span>
                        SUPPRIMER
                    </button>
<?php endif; ?>
                    <button class="apk-admin-btn" onclick="openApkModal()">
                        <span class="material-symbols-outlined">upload_file</span>
                        <?= $apkMeta ? 'REMPLACER L\'APK' : "UPLOADER UN APK" ?>
                    </button>
                </div>
<?php endif; ?>
            </div>
        </div>
    </section>

<?php if (isAdmin()): ?>
    <!-- APK Upload Modal -->
    <div class="apk-modal" id="apkModal">
        <div class="apk-modal__box">
            <button class="apk-modal__close" onclick="closeApkModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="apk-modal__title">
                <span class="material-symbols-outlined">upload_file</span>
                Uploader un APK
            </div>
            <form id="apkUploadForm" onsubmit="return submitApk(event)">
                <input type="hidden" name="action" value="upload">

                <div class="apk-dropzone" id="apkDropzone" onclick="document.getElementById('apkFileInput').click()">
                    <div class="apk-dropzone__icon">
                        <span class="material-symbols-outlined" style="font-size:36px;">cloud_upload</span>
                    </div>
                    <div class="apk-dropzone__text" id="apkDropText">
                        Glissez un fichier <strong>.apk</strong> ici<br>ou cliquez pour parcourir
                    </div>
                    <input type="file" name="apk_file" id="apkFileInput" accept=".apk">
                </div>

                <div style="display:flex;gap:12px;margin-bottom:16px;">
                    <div class="apk-modal__field" style="flex:1;">
                        <label>Version</label>
                        <input type="text" name="version" placeholder="1.0.0" value="<?= htmlspecialchars(
                            $apkMeta["version"] ?? "1.0.0",
                        ) ?>">
                    </div>
                    <div class="apk-modal__field" style="flex:1;">
                        <label>Android min.</label>
                        <input type="text" name="min_android" placeholder="5.0" value="<?= htmlspecialchars(
                            $apkMeta["min_android"] ?? "5.0",
                        ) ?>">
                    </div>
                </div>

                <div class="apk-modal__field">
                    <label>Changelog</label>
                    <textarea name="changelog" placeholder="Nouveautés de cette version..."><?= htmlspecialchars(
                        $apkMeta["changelog"] ?? "",
                    ) ?></textarea>
                </div>

                <button type="submit" class="apk-modal__submit" id="apkSubmitBtn">
                    <span class="material-symbols-outlined" style="font-size:18px;">upload</span>
                    UPLOADER
                </button>
            </form>
            <div class="apk-modal__msg" id="apkMsg"></div>
        </div>
    </div>
<?php endif; ?>

    <!-- ================================
         CTA
         ================================ -->
    <section class="case-cta">
        <div class="container">
            <div class="case-cta__buttons">
                <a href="index.php#projects" class="case-cta__btn">
                    <span class="material-symbols-outlined" style="font-size:18px;">grid_view</span>
                    TOUS LES PROJETS
                </a>
                <a href="index.php#contact" class="case-cta__btn case-cta__btn--outline">
                    <span class="material-symbols-outlined" style="font-size:18px;">alternate_email</span>
                    ME CONTACTER
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="case-footer">
        <div class="container">
            BLACK_PROTOCOL &copy; <?= $year ?> — Case Study // Nouchi Lexicon App
        </div>
    </footer>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox__close material-symbols-outlined">close</button>
        <button class="lightbox__nav lightbox__prev material-symbols-outlined">chevron_left</button>
        <button class="lightbox__nav lightbox__next material-symbols-outlined">chevron_right</button>
        <img src="" alt="Screenshot" id="lightbox-img">
    </div>

    <script>
        // ── Lightbox ──
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const screenshots = document.querySelectorAll('.screenshot-card');
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            lightboxImg.src = screenshots[index].getAttribute('data-img');
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function navigate(dir) {
            currentIndex = (currentIndex + dir + screenshots.length) % screenshots.length;
            lightboxImg.src = screenshots[currentIndex].getAttribute('data-img');
        }

        screenshots.forEach((card, i) => card.addEventListener('click', () => openLightbox(i)));
        document.querySelector('.lightbox__close').addEventListener('click', closeLightbox);
        document.querySelector('.lightbox__prev').addEventListener('click', () => navigate(-1));
        document.querySelector('.lightbox__next').addEventListener('click', () => navigate(1));
        lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        });

        // ── Fade-up Animation ──
        const fadeEls = document.querySelectorAll('.fade-up');
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        fadeEls.forEach(el => fadeObserver.observe(el));

<?php if (isAdmin()): ?>
        // ── APK Upload System ──
        const apkModal = document.getElementById('apkModal');
        const apkDropzone = document.getElementById('apkDropzone');
        const apkFileInput = document.getElementById('apkFileInput');
        const apkDropText = document.getElementById('apkDropText');
        const apkSubmitBtn = document.getElementById('apkSubmitBtn');
        const apkMsg = document.getElementById('apkMsg');
        let selectedFile = null;

        function openApkModal() {
            apkModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            apkMsg.className = 'apk-modal__msg';
            apkMsg.textContent = '';
        }

        function closeApkModal() {
            apkModal.classList.remove('active');
            document.body.style.overflow = '';
            selectedFile = null;
            apkDropText.innerHTML = 'Glissez un fichier <strong>.apk</strong> ici<br>ou cliquez pour parcourir';
        }

        apkModal.addEventListener('click', function(e) {
            if (e.target === apkModal) closeApkModal();
        });

        // Drag & Drop
        apkDropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            apkDropzone.classList.add('dragover');
        });
        apkDropzone.addEventListener('dragleave', function() {
            apkDropzone.classList.remove('dragover');
        });
        apkDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            apkDropzone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file && file.name.toLowerCase().endsWith('.apk')) {
                selectedFile = file;
                apkDropText.innerHTML = '<strong>' + file.name + '</strong><br>' + (file.size / 1048576).toFixed(1) + ' MB';
            } else {
                apkDropText.innerHTML = '<span style="color:#ff5f57;">Fichier invalide. Seul .apk accepté.</span>';
            }
        });

        apkFileInput.addEventListener('change', function() {
            if (this.files[0]) {
                selectedFile = this.files[0];
                apkDropText.innerHTML = '<strong>' + selectedFile.name + '</strong><br>' + (selectedFile.size / 1048576).toFixed(1) + ' MB';
            }
        });

        function submitApk(e) {
            e.preventDefault();
            if (!selectedFile) {
                apkMsg.className = 'apk-modal__msg apk-modal__msg--error';
                apkMsg.textContent = 'Sélectionnez un fichier APK.';
                return false;
            }

            const form = document.getElementById('apkUploadForm');
            const formData = new FormData(form);
            formData.set('apk_file', selectedFile);

            apkSubmitBtn.disabled = true;
            apkSubmitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;animation:spin 1s linear infinite;">progress_activity</span> Upload en cours...';

            fetch('apk_handler.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        apkMsg.className = 'apk-modal__msg apk-modal__msg--success';
                        apkMsg.textContent = 'APK uploadé avec succès ! Rechargement...';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        throw new Error(data.error || 'Erreur inconnue');
                    }
                })
                .catch(err => {
                    apkMsg.className = 'apk-modal__msg apk-modal__msg--error';
                    apkMsg.textContent = err.message;
                    apkSubmitBtn.disabled = false;
                    apkSubmitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;">upload</span> UPLOADER';
                });

            return false;
        }

        function deleteApk() {
            if (!confirm('Supprimer l\'APK actuel ?')) return;
            fetch('apk_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.error || 'Erreur');
            })
            .catch(() => alert('Erreur réseau.'));
        }

        // Spin animation for upload progress
        const style = document.createElement('style');
        style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(style);
<?php endif; ?>
    </script>
</body>
</html>
