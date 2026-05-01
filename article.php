<?php
/**
 * BLACK_PROTOCOL — Article Reader (Public Page)
 *
 * Publicly accessible page to read a full article.
 * Takes an `id` parameter via GET: article.php?id=1
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
require_once __DIR__ . "/includes/articles.php";
require_once __DIR__ . "/includes/comments.php";

// === Safe multibyte helpers (no mbstring dependency) ===
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

// === Auth state ===
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

// === Fetch article ===
$id = (int) ($_GET["id"] ?? 0);
$art = $id ? getArticleById($id) : null;

// === SEO for article (or 404) ===
if ($art) {
    $seoTitle = $art["title"] ?? "Article";
    $seoDesc = $art["meta_description"] ?? "Article publié sur BLACK_PROTOCOL";
    $seoKeywords = array_merge(
        ["article", "blog"],
        json_decode($art["tags"] ?? "[]", true) ?: [],
    );
} else {
    $seoTitle = "Article introuvable";
    $seoDesc = "L'article demandé n'existe pas ou a été supprimé.";
    $seoKeywords = [];
}

$title = $seoTitle . " | BLACK_PROTOCOL";
$lang = "fr";
$charset = "UTF-8";

// === Decode plan & tags ===
$plan = $art ? (json_decode($art["plan"], true) ?: []) : [];
$tags = $art ? (json_decode($art["tags"], true) ?: []) : [];

// === Format date ===
$dateFormatted = "";
if ($art && !empty($art["created_at"])) {
    $ts = strtotime($art["created_at"]);
    if ($ts) {
        $months = [
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre",
        ];
        $dateFormatted =
            date("d", $ts) .
            " " .
            $months[(int) date("m", $ts) - 1] .
            " " .
            date("Y", $ts);
    }
}

// === Reading time estimate ===
$readingTime = 0;
if ($art) {
    $wordCount = safeStrlen(
        strip_tags(($art["introduction"] ?? "") . " " . ($art["plan"] ?? "")),
    );
    $readingTime = max(1, (int) ceil($wordCount / 200));
}

// ── Handle comment POST ──
$commentError = "";
$commentSuccess = false;
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_comment"]) &&
    $art
) {
    $commentName = trim($_POST["comment_name"] ?? "");
    $commentEmail = trim($_POST["comment_email"] ?? "");
    $commentContent = trim($_POST["comment_content"] ?? "");
    $commentParent = (int) ($_POST["comment_parent"] ?? 0);

    if (empty($commentName) || empty($commentContent)) {
        $commentError = "Le nom et le commentaire sont requis.";
    } elseif (safeStrlen($commentContent) < 3) {
        $commentError = "Le commentaire est trop court.";
    } elseif (safeStrlen($commentContent) > 2000) {
        $commentError = "Le commentaire est trop long (2000 caractères max).";
    } else {
        $isAdminComment = $isLoggedIn && isAdmin();
        // If admin is logged in, override name
        if ($isAdminComment && $currentUser) {
            $commentName = $currentUser["username"];
            $commentEmail = $currentUser["email"];
        }
        $result = addComment(
            $art["id"],
            $commentName,
            $commentContent,
            $commentEmail,
            $isAdminComment,
            $commentParent,
        );
        if ($result) {
            $commentSuccess = true;
        } else {
            $commentError = "Erreur lors de l'ajout du commentaire.";
        }
    }
}

// ── Handle comment delete (admin) ──
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_comment"]) &&
    $isLoggedIn &&
    isAdmin()
) {
    $delCommentId = (int) ($_POST["delete_comment"] ?? 0);
    if ($delCommentId > 0) {
        deleteComment($delCommentId);
    }
}

// ── Load comments ──
$comments = $art ? getArticleComments($art["id"]) : [];
$commentCount = $art ? getCommentCount($art["id"]) : 0;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="<?= $charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <?= renderMeta($seoTitle, $seoDesc, "article", null, $seoKeywords) ?>

    <!-- Fonts: Space Grotesk + Inter + Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ====== Material Symbols config ====== */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* ====== Page base ====== */
        body { background: var(--void); }
        .ap-page { min-height: 100vh; padding-bottom: 100px; }
        .ap-container { max-width: 860px; margin: 0 auto; padding: 0 var(--container-margin); }

        /* ====== Back button ====== */
        .ap-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: var(--sp-sm) var(--sp-md);
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-family: var(--font-display);
            font-size: 12px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: color 0.2s;
            margin-top: var(--sp-md);
        }
        .ap-back:hover { color: var(--primary); }

        /* ====== Article header ====== */
        .ap-header { margin: var(--sp-lg) 0 var(--sp-xl); }

        .ap-header__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--sp-sm);
            margin-bottom: var(--sp-md);
        }

        .ap-header__category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border-radius: 12px;
            background: rgba(0,158,96,0.1);
            border: 1px solid rgba(0,158,96,0.3);
            color: var(--secondary-bright);
        }
        .ap-header__category .material-symbols-outlined {
            font-size: 14px;
        }

        .ap-header__dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
        }

        .ap-header__date {
            font-family: var(--font-display);
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.05em;
        }

        .ap-header__reading-time {
            font-family: var(--font-display);
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .ap-header__reading-time .material-symbols-outlined {
            font-size: 14px;
        }

        .ap-header h1 {
            font-family: var(--font-display);
            font-size: clamp(24px, 5vw, 40px);
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: -0.02em;
            line-height: 1.15;
            margin-bottom: var(--sp-sm);
        }

        .ap-header__author {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: var(--sp-sm);
        }
        .ap-header__author-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ap-header__author-avatar .material-symbols-outlined {
            font-size: 16px;
            color: rgba(255,255,255,0.5);
        }
        .ap-header__author-name {
            font-family: var(--font-display);
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            letter-spacing: 0.05em;
        }
        .ap-header__author-name strong {
            color: var(--on-surface-variant);
            font-weight: 600;
        }

        /* ====== Terminal card (used for meta desc, intro, sections) ====== */
        .ap-card {
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: var(--sp-lg);
        }
        .ap-card__bar {
            background: var(--void);
            padding: 10px var(--sp-md);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
        }
        .ap-card__dots {
            display: flex;
            gap: 6px;
        }
        .ap-card__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .ap-card__dot--red { background: rgba(255,180,171,0.4); }
        .ap-card__dot--yellow { background: rgba(255,183,133,0.4); }
        .ap-card__dot--green { background: rgba(97,221,152,0.4); }

        .ap-card__bar-label {
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.2em;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            flex: 1;
        }

        .ap-card__body {
            padding: var(--sp-lg);
        }

        /* ====== Meta description block ====== */
        .ap-meta-desc {
            color: rgba(255,255,255,0.5);
            font-size: 14px;
            line-height: 1.6;
            padding: var(--sp-sm) var(--sp-md);
            background: rgba(0,0,0,0.3);
            border-left: 2px solid var(--primary);
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        /* ====== Introduction block ====== */
        .ap-intro {
            color: var(--on-surface-variant);
            font-size: 15px;
            line-height: 1.85;
        }

        /* ====== Tags ====== */
        .ap-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: var(--sp-lg);
        }
        .ap-tag {
            padding: 3px 10px;
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.06em;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.45);
            border-radius: 10px;
            transition: all 0.2s;
        }
        .ap-tag:hover {
            border-color: rgba(255,130,0,0.3);
            color: var(--primary);
            background: rgba(255,130,0,0.05);
        }

        /* ====== Plan sections ====== */
        .ap-plan { list-style: none; padding: 0; }

        .ap-plan__section {
            margin-bottom: var(--sp-md);
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .ap-plan__section:hover {
            border-color: rgba(255,255,255,0.12);
        }

        .ap-plan__section-header {
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
            padding: var(--sp-md);
            background: rgba(255,130,0,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.04);
            cursor: default;
        }

        .ap-plan__section-num {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,130,0,0.15);
            border: 1px solid rgba(255,130,0,0.3);
            border-radius: 50%;
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            flex-shrink: 0;
        }

        .ap-plan__section-title {
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 700;
            color: var(--white);
            letter-spacing: 0.03em;
        }

        .ap-plan__section-body {
            padding: var(--sp-md);
        }

        .ap-plan__section-desc {
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            line-height: 1.6;
            margin-bottom: var(--sp-md);
        }

        .ap-plan__subs {
            list-style: none;
            padding: 0;
        }

        .ap-plan__sub {
            padding: var(--sp-sm) var(--sp-md);
            border-left: 2px solid rgba(255,255,255,0.06);
            margin-bottom: 4px;
            transition: border-color 0.2s;
        }
        .ap-plan__sub:hover {
            border-left-color: var(--primary);
        }

        .ap-plan__sub-title {
            font-family: var(--font-display);
            font-size: 13px;
            color: var(--on-surface-variant);
            letter-spacing: 0.04em;
            font-weight: 500;
        }
        .ap-plan__sub-desc {
            font-size: 12px;
            color: rgba(255,255,255,0.3);
            margin-top: 3px;
            line-height: 1.5;
        }

        /* ====== 404 state ====== */
        .ap-404 {
            text-align: center;
            padding: var(--sp-3xl) var(--sp-md);
        }
        .ap-404__icon {
            font-size: 64px;
            color: rgba(255,255,255,0.1);
            margin-bottom: var(--sp-lg);
        }
        .ap-404 h1 {
            font-family: var(--font-display);
            font-size: clamp(28px, 6vw, 48px);
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: -0.02em;
            margin-bottom: var(--sp-sm);
        }
        .ap-404 h1 span { color: var(--primary); }
        .ap-404 p {
            color: rgba(255,255,255,0.4);
            font-size: 15px;
            line-height: 1.6;
            max-width: 480px;
            margin: 0 auto var(--sp-lg);
        }
        .ap-404__btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: var(--sp-sm) var(--sp-lg);
            background: var(--primary);
            color: var(--void);
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: var(--radius);
            transition: box-shadow 0.2s, transform 0.1s;
        }
        .ap-404__btn:hover {
            box-shadow: 0 0 20px rgba(255,130,0,0.3);
        }
        .ap-404__btn:active {
            transform: scale(0.97);
        }
        .ap-404__btn .material-symbols-outlined { font-size: 16px; }

        /* ====== Footer info ====== */
        .ap-footer-info {
            text-align: center;
            padding: var(--sp-lg) 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            margin-top: var(--sp-xl);
        }
        .ap-footer-info p {
            font-family: var(--font-display);
            font-size: 11px;
            color: rgba(255,255,255,0.2);
            letter-spacing: 0.05em;
        }
        .ap-footer-info a {
            color: rgba(255,255,255,0.35);
            text-decoration: none;
            transition: color 0.2s;
        }
        .ap-footer-info a:hover { color: var(--primary); }

        /* ====== Responsive ====== */
        @media (max-width: 640px) {
            .ap-header h1 { font-size: 22px; }
            .ap-card__body { padding: var(--sp-md); }
            .ap-plan__section-body { padding: var(--sp-sm) var(--sp-md); }
        }

        /* ====== COMMENTS ====== */
        .ap-comments { margin-top: var(--sp-xl); }
        .ap-comments__header { display: flex; align-items: center; gap: 8px; margin-bottom: var(--sp-lg); }
        .ap-comments__title { font-family: var(--font-display); font-size: 18px; font-weight: 700; color: var(--white); text-transform: uppercase; letter-spacing: 0.05em; }
        .ap-comments__count { font-family: var(--font-display); font-size: 11px; background: rgba(255,130,0,0.15); color: var(--primary); padding: 2px 8px; border-radius: 10px; border: 1px solid rgba(255,130,0,0.3); }
        .ap-comments__msg { padding: 10px 14px; border-radius: var(--radius); margin-bottom: var(--sp-md); display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .ap-comments__msg--error { background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: var(--error); }
        .ap-comments__msg--success { background: rgba(0,158,96,0.1); border: 1px solid rgba(0,158,96,0.3); color: var(--secondary-bright); }
        .ap-comments__form-card { background: var(--surface-container); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius); overflow: hidden; margin-bottom: var(--sp-xl); }
        .ap-comments__form-bar { background: var(--void); padding: 8px 14px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 8px; }
        .ap-comments__form-dots { display: flex; gap: 5px; }
        .ap-comments__form-bar-title { font-family: var(--font-display); font-size: 9px; letter-spacing: 0.2em; color: rgba(255,255,255,0.4); }
        .ap-comments__form { padding: var(--sp-md); }
        .ap-comments__form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-sm); margin-bottom: var(--sp-sm); }
        @media (max-width: 520px) { .ap-comments__form-row { grid-template-columns: 1fr; } }
        .ap-comments__field label { display: block; font-family: var(--font-display); font-size: 9px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 4px; }
        .ap-comments__field input, .ap-comments__field textarea { width: 100%; padding: 8px 12px; font-family: var(--font-body); font-size: 14px; color: var(--on-surface); background: var(--void); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius); outline: none; transition: border-color 0.2s; }
        .ap-comments__field input:focus, .ap-comments__field textarea:focus { border-color: var(--primary); }
        .ap-comments__field input::placeholder, .ap-comments__field textarea::placeholder { color: rgba(255,255,255,0.2); }
        .ap-comments__field textarea { resize: vertical; min-height: 80px; }
        .ap-comments__field input[readonly] { opacity: 0.6; cursor: not-allowed; }
        .ap-comments__submit { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; font-family: var(--font-display); font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--void); background: var(--primary); border: none; border-radius: var(--radius); cursor: pointer; transition: box-shadow 0.2s; }
        .ap-comments__submit:hover { box-shadow: 0 0 16px rgba(255,130,0,0.3); }
        .ap-comments__list { display: flex; flex-direction: column; gap: var(--sp-sm); }
        .ap-comment { display: flex; gap: var(--sp-sm); padding: var(--sp-md); background: var(--surface-container); border: 1px solid rgba(255,255,255,0.06); border-radius: var(--radius); transition: border-color 0.2s; }
        .ap-comment:hover { border-color: rgba(255,255,255,0.1); }
        .ap-comment--admin { border-left: 3px solid var(--primary); }
        .ap-comment__avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,130,0,0.15); border: 1px solid rgba(255,130,0,0.3); display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 14px; font-weight: 700; color: var(--primary); flex-shrink: 0; }
        .ap-comment--admin .ap-comment__avatar { background: rgba(255,130,0,0.25); border-color: var(--primary); }
        .ap-comment__body { flex: 1; min-width: 0; }
        .ap-comment__header { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
        .ap-comment__name { font-family: var(--font-display); font-size: 13px; font-weight: 600; color: var(--white); }
        .ap-comment__badge { font-family: var(--font-display); font-size: 8px; letter-spacing: 0.1em; padding: 2px 6px; background: rgba(255,130,0,0.15); border: 1px solid rgba(255,130,0,0.3); color: var(--primary); border-radius: 8px; }
        .ap-comment__date { font-size: 11px; color: rgba(255,255,255,0.25); }
        .ap-comment__delete { background: none; border: none; color: rgba(255,82,82,0.4); cursor: pointer; padding: 2px; display: inline-flex; transition: color 0.2s; margin-left: auto; }
        .ap-comment__delete .material-symbols-outlined { font-size: 14px; }
        .ap-comment__delete:hover { color: rgba(255,82,82,1); }
        .ap-comment__content { font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.6; margin-bottom: 8px; word-wrap: break-word; }
        .ap-comment__reply { background: none; border: none; color: rgba(255,255,255,0.25); cursor: pointer; font-family: var(--font-display); font-size: 10px; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px; padding: 0; transition: color 0.2s; }
        .ap-comment__reply:hover { color: var(--primary); }
        .ap-comments__empty { text-align: center; padding: var(--sp-xl) var(--sp-md); }
        .ap-comments__empty p { color: rgba(255,255,255,0.3); font-size: 14px; margin-top: 8px; }
        .ap-comments__empty p:last-child { color: rgba(255,255,255,0.15); }
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
                <?php if ($isLoggedIn && $currentUser): ?>
                    <!-- Utilisateur connecté -->
                    <a href="profile.php" class="top-header__user">
                        <div class="top-header__user-avatar">
                            <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1;">person</span>
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
                    <?php endif; ?>
                    <a href="profile.php" class="top-header__btn top-header__btn--login">
                        <span class="material-symbols-outlined" style="font-size:16px;">person</span>
                        PROFIL
                    </a>
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
            </div>
        </div>
    </header>

    <!-- ================================
         ARTICLE CONTENT
         ================================ -->
    <main class="ap-page">
        <div class="ap-container">

            <?php if (!$art): ?>
                <!-- ========== 404 STATE ========== -->
                <div class="ap-404">
                    <span class="material-symbols-outlined ap-404__icon">error_outline</span>
                    <h1>404 <span>//</span> Article introuvable</h1>
                    <p>L'article que vous cherchez n'existe pas, a été supprimé, ou l'identifiant est invalide.</p>
                    <a href="index.php#blog" class="ap-404__btn">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Retour au blog
                    </a>
                </div>
            <?php else: ?>
                <!-- ========== ARTICLE FOUND ========== -->

                <!-- Back link -->
                <a href="index.php#blog" class="ap-back">
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                    Retour au blog
                </a>

                <?php if ($isLoggedIn && isAdmin()): ?>
                <form method="POST" action="index.php#blog" style="display:inline; margin-left:8px;" onsubmit="return confirm('Supprimer cet article ?');">
                    <input type="hidden" name="delete_article" value="<?= $art[
                        "id"
                    ] ?>">
                    <button type="submit" style="display:inline-flex; align-items:center; gap:4px; padding:6px 14px; font-family:var(--font-display); font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:rgba(255,82,82,0.8); background:rgba(255,82,82,0.08); border:1px solid rgba(255,82,82,0.2); border-radius:var(--radius); cursor:pointer; transition:all 0.2s;">
                        <span class="material-symbols-outlined" style="font-size:14px;">delete</span>
                        Supprimer
                    </button>
                </form>
                <?php endif; ?>

                <!-- Article header -->
                <div class="ap-header">
                    <div class="ap-header__meta">
                        <?php if (!empty($art["category"])): ?>
                            <span class="ap-header__category">
                                <span class="material-symbols-outlined">label</span>
                                <?= htmlspecialchars($art["category"]) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($dateFormatted): ?>
                            <span class="ap-header__dot"></span>
                            <span class="ap-header__date"><?= $dateFormatted ?></span>
                        <?php endif; ?>
                        <?php if ($readingTime > 0): ?>
                            <span class="ap-header__dot"></span>
                            <span class="ap-header__reading-time">
                                <span class="material-symbols-outlined">schedule</span>
                                <?= $readingTime ?> min de lecture
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1><?= htmlspecialchars($art["title"]) ?></h1>

                    <?php if (!empty($art["author_name"])): ?>
                        <div class="ap-header__author">
                            <div class="ap-header__author-avatar">
                                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">person</span>
                            </div>
                            <span class="ap-header__author-name">
                                Par <strong><?= htmlspecialchars(
                                    $art["author_name"],
                                ) ?></strong>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tags)): ?>
                    <!-- Tags -->
                    <div class="ap-tags">
                        <?php foreach ($tags as $tag): ?>
                            <span class="ap-tag">#&nbsp;<?= htmlspecialchars(
                                $tag,
                            ) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($art["meta_description"])): ?>
                    <!-- Meta description card -->
                    <div class="ap-card">
                        <div class="ap-card__bar">
                            <div class="ap-card__dots">
                                <span class="ap-card__dot ap-card__dot--red"></span>
                                <span class="ap-card__dot ap-card__dot--yellow"></span>
                                <span class="ap-card__dot ap-card__dot--green"></span>
                            </div>
                            <span class="ap-card__bar-label">description.seo</span>
                        </div>
                        <div class="ap-card__body">
                            <div class="ap-meta-desc">
                                <?= htmlspecialchars(
                                    $art["meta_description"],
                                ) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($art["introduction"])): ?>
                    <!-- Introduction card -->
                    <div class="ap-card">
                        <div class="ap-card__bar">
                            <div class="ap-card__dots">
                                <span class="ap-card__dot ap-card__dot--red"></span>
                                <span class="ap-card__dot ap-card__dot--yellow"></span>
                                <span class="ap-card__dot ap-card__dot--green"></span>
                            </div>
                            <span class="ap-card__bar-label">introduction.md</span>
                        </div>
                        <div class="ap-card__body">
                            <div class="ap-intro">
                                <?= nl2br(
                                    htmlspecialchars(
                                        strip_tags($art["introduction"]),
                                    ),
                                ) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($plan)): ?>
                    <!-- Plan sections -->
                    <div class="ap-card">
                        <div class="ap-card__bar">
                            <div class="ap-card__dots">
                                <span class="ap-card__dot ap-card__dot--red"></span>
                                <span class="ap-card__dot ap-card__dot--yellow"></span>
                                <span class="ap-card__dot ap-card__dot--green"></span>
                            </div>
                            <span class="ap-card__bar-label">plan.structure</span>
                        </div>
                        <div class="ap-card__body">
                            <ul class="ap-plan">
                                <?php
                                $sectionNum = 0;
                                foreach ($plan as $section):

                                    $sectionNum++;
                                    $sectionTitle =
                                        $section["title"] ??
                                        "Section {$sectionNum}";
                                    $sectionDesc = $section["desc"] ?? "";
                                    $subsections =
                                        $section["subsections"] ?? [];
                                    ?>
                                    <li class="ap-plan__section">
                                        <div class="ap-plan__section-header">
                                            <span class="ap-plan__section-num"><?= $sectionNum ?></span>
                                            <span class="ap-plan__section-title"><?= htmlspecialchars(
                                                $sectionTitle,
                                            ) ?></span>
                                        </div>
                                        <div class="ap-plan__section-body">
                                            <?php if (!empty($sectionDesc)): ?>
                                                <div class="ap-plan__section-desc"><?= htmlspecialchars(
                                                    $sectionDesc,
                                                ) ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($subsections)): ?>
                                                <ul class="ap-plan__subs">
                                                    <?php foreach (
                                                        $subsections
                                                        as $sub
                                                    ): ?>
                                                        <li class="ap-plan__sub">
                                                            <?php if (
                                                                !empty(
                                                                    $sub[
                                                                        "title"
                                                                    ]
                                                                )
                                                            ): ?>
                                                                <div class="ap-plan__sub-title"><?= htmlspecialchars(
                                                                    $sub[
                                                                        "title"
                                                                    ],
                                                                ) ?></div>
                                                            <?php endif; ?>
                                                            <?php if (
                                                                !empty(
                                                                    $sub["desc"]
                                                                )
                                                            ): ?>
                                                                <div class="ap-plan__sub-desc"><?= htmlspecialchars(
                                                                    $sub[
                                                                        "desc"
                                                                    ],
                                                                ) ?></div>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php
                                endforeach;
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Footer info -->
                <div class="ap-footer-info">
                    <p>
                        Publié le <?= $dateFormatted ?: "date inconnue" ?>
                        <?php if (!empty($art["author_name"])): ?>
                            &nbsp;·&nbsp; Par <?= htmlspecialchars(
                                $art["author_name"],
                            ) ?>
                        <?php endif; ?>
                        &nbsp;·&nbsp; <a href="index.php#blog">BLACK_PROTOCOL</a>
                    </p>
                </div>

                <!-- ====== COMMENTS ====== -->
                <div class="ap-comments" id="comments">
                    <div class="ap-comments__header">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">forum</span>
                        <span class="ap-comments__title">Commentaires</span>
                        <span class="ap-comments__count"><?= $commentCount ?></span>
                    </div>

                    <?php if ($commentError): ?>
                    <div class="ap-comments__msg ap-comments__msg--error">
                        <span class="material-symbols-outlined" style="font-size:16px;">error</span>
                        <?= htmlspecialchars($commentError) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($commentSuccess): ?>
                    <div class="ap-comments__msg ap-comments__msg--success">
                        <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                        Commentaire ajouté avec succès !
                    </div>
                    <?php endif; ?>

                    <!-- Comment form -->
                    <div class="ap-comments__form-card">
                        <div class="ap-comments__form-bar">
                            <div class="ap-comments__form-dots">
                                <span style="width:8px;height:8px;border-radius:50%;background:rgba(255,180,171,0.4);"></span>
                                <span style="width:8px;height:8px;border-radius:50%;background:rgba(255,183,133,0.4);"></span>
                                <span style="width:8px;height:8px;border-radius:50%;background:rgba(97,221,152,0.4);"></span>
                            </div>
                            <span class="ap-comments__form-bar-title">LAISSER UN COMMENTAIRE</span>
                        </div>
                        <form method="POST" action="article.php?id=<?= $art[
                            "id"
                        ] ?>#comments" class="ap-comments__form">
                            <input type="hidden" name="add_comment" value="1">
                            <input type="hidden" name="comment_parent" value="0" id="comment_parent">
                            <div class="ap-comments__form-row">
                                <div class="ap-comments__field">
                                    <label>Nom *</label>
                                    <input type="text" name="comment_name" placeholder="Votre nom ou pseudo" required maxlength="100" value="<?= $isLoggedIn &&
                                    $currentUser
                                        ? htmlspecialchars(
                                            $currentUser["username"],
                                        )
                                        : "" ?>" <?= $isLoggedIn
    ? "readonly"
    : "" ?>>
                                </div>
                                <div class="ap-comments__field">
                                    <label>Email (optionnel)</label>
                                    <input type="email" name="comment_email" placeholder="votre@email.com" maxlength="100" value="<?= $isLoggedIn &&
                                    $currentUser
                                        ? htmlspecialchars(
                                            $currentUser["email"],
                                        )
                                        : "" ?>" <?= $isLoggedIn
    ? "readonly"
    : "" ?>>
                                </div>
                            </div>
                            <div class="ap-comments__field">
                                <label>Commentaire *</label>
                                <textarea name="comment_content" placeholder="Partagez votre avis, une question, une remarque..." rows="4" required maxlength="2000"></textarea>
                            </div>
                            <button type="submit" class="ap-comments__submit">
                                <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                                Publier
                            </button>
                        </form>
                    </div>

                    <!-- Comments list -->
                    <?php if (!empty($comments)): ?>
                    <div class="ap-comments__list">
                        <?php foreach ($comments as $comment): ?>
                        <div class="ap-comment <?= $comment["is_admin"]
                            ? "ap-comment--admin"
                            : "" ?>" id="comment-<?= $comment["id"] ?>">
                            <div class="ap-comment__avatar">
                                <?= strtoupper(
                                    safeSubstr($comment["author_name"], 0, 1),
                                ) ?>
                            </div>
                            <div class="ap-comment__body">
                                <div class="ap-comment__header">
                                    <span class="ap-comment__name"><?= htmlspecialchars(
                                        $comment["author_name"],
                                    ) ?></span>
                                    <?php if ($comment["is_admin"]): ?>
                                    <span class="ap-comment__badge">ADMIN</span>
                                    <?php endif; ?>
                                    <span class="ap-comment__date"><?= date(
                                        "d.m.Y \à H:i",
                                        strtotime($comment["created_at"]),
                                    ) ?></span>
                                    <?php if ($isLoggedIn && isAdmin()): ?>
                                    <form method="POST" action="article.php?id=<?= $art[
                                        "id"
                                    ] ?>#comments" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                        <input type="hidden" name="delete_comment" value="<?= $comment[
                                            "id"
                                        ] ?>">
                                        <button type="submit" class="ap-comment__delete" title="Supprimer">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <div class="ap-comment__content"><?= nl2br(
                                    htmlspecialchars($comment["content"]),
                                ) ?></div>
                                <button class="ap-comment__reply" onclick="document.getElementById('comment_parent').value='<?= $comment[
                                    "id"
                                ] ?>';document.querySelector('.ap-comments__form').scrollIntoView({behavior:'smooth'});">
                                    <span class="material-symbols-outlined" style="font-size:14px;">reply</span>
                                    Répondre
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="ap-comments__empty">
                        <span class="material-symbols-outlined" style="font-size:32px; color:rgba(255,255,255,0.1);">chat_bubble_outline</span>
                        <p>Aucun commentaire pour le moment.</p>
                        <p style="font-size:12px;">Soyez le premier à partager votre avis !</p>
                    </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- ================================
         BOTTOM NAVIGATION
         ================================ -->
    <nav class="nav-bottom" aria-label="Navigation principale">
        <ul class="nav-bottom__list">
            <li class="nav-bottom__item">
                <a href="index.php" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">home</span>
                    <span class="nav-bottom__text">Accueil</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#projects" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">grid_view</span>
                    <span class="nav-bottom__text">Projets</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <a href="index.php#contact" class="nav-bottom__link">
                    <span class="material-symbols-outlined nav-bottom__icon">alternate_email</span>
                    <span class="nav-bottom__text">Contact</span>
                </a>
            </li>
            <li class="nav-bottom__item">
                <?php if ($isLoggedIn && $currentUser): ?>
                    <a href="profile.php" class="nav-bottom__link">
                        <span class="material-symbols-outlined nav-bottom__icon" style="font-variation-settings:'FILL' 1;">person</span>
                        <span class="nav-bottom__text">Profil</span>
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

</body>
</html>
