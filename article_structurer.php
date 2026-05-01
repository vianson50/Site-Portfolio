<?php
/**
 * BLACK_PROTOCOL — Article Structurer
 * Génère un article structuré à partir d'un lien ou d'une info brute.
 */
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";
require_once __DIR__ . "/includes/article_structurer.php";
require_once __DIR__ . "/includes/articles.php";

$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

$result = null;
$published = false;
$error = "";
$inputUrl = "";
$inputText = "";
$inputTopic = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputUrl = trim($_POST["url"] ?? "");
    $inputText = trim($_POST["raw_text"] ?? "");
    $inputTopic = trim($_POST["topic"] ?? "");

    if (empty($inputUrl) && empty($inputText)) {
        $error = "Fournissez un lien ou du texte brut pour générer un article.";
    } else {
        $data = [
            "source" => "text",
            "raw_text" => $inputText,
            "topic" => $inputTopic,
        ];

        if (!empty($inputUrl)) {
            $urlData = fetchUrlContent($inputUrl);
            if ($urlData) {
                $data["url_data"] = $urlData;
                $data["source"] = "url";
            }
        }

        $result = generateArticle($data);

        // Publier l'article sur le blog ?
        if (isset($_POST["publish"]) && $result && $isLoggedIn && isAdmin()) {
            $articleId = saveArticle(
                $result,
                (int) $currentUser["id"],
                $inputUrl,
            );
            if ($articleId) {
                $published = true;
            }
        }
    }
}

$year = date("Y");
$seoTitle = "Générateur d'Articles Structurés — Outil Rédactionnel";
$seoDesc =
    "Structurez vos articles tech en quelques secondes. Collez un lien ou une info brute et obtenez un plan détaillé, une introduction captivante et un article SEO-optimisé.";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Structurer | BLACK_PROTOCOL</title>
    <?= renderMeta($seoTitle, $seoDesc, "website") ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { background: var(--void); }
        .art-page { min-height: 100vh; padding-bottom: 80px; }
        .art-container { max-width: 900px; margin: 0 auto; padding: 0 var(--container-margin); }
        .art-back { display: inline-flex; align-items: center; gap: 6px; padding: var(--sp-sm) var(--sp-md); color: rgba(255,255,255,0.5); text-decoration: none; font-family: var(--font-display); font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase; transition: color 0.2s; }
        .art-back:hover { color: var(--primary); }
        .art-header { margin: var(--sp-lg) 0; }
        .art-header__label { display: flex; align-items: center; gap: 6px; font-family: var(--font-display); font-size: 10px; letter-spacing: 0.2em; color: rgba(255,255,255,0.3); text-transform: uppercase; margin-bottom: var(--sp-sm); }
        .art-header__label .material-symbols-outlined { font-size: 14px; color: var(--primary); }
        .art-header h1 { font-family: var(--font-display); font-size: clamp(24px, 5vw, 40px); font-weight: 700; color: var(--white); text-transform: uppercase; letter-spacing: -0.02em; }
        .art-header h1 span { color: var(--primary); }
        .art-header p { color: rgba(255,255,255,0.5); margin-top: var(--sp-sm); line-height: 1.6; }
        .art-form { background: var(--surface); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius); overflow: hidden; margin-bottom: var(--sp-xl); }
        .art-form__bar { background: var(--void); padding: 10px var(--sp-md); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: var(--sp-sm); }
        .art-form__dots { display: flex; gap: 6px; }
        .art-form__dot { width: 10px; height: 10px; border-radius: 50%; }
        .art-form__dot--red { background: rgba(255,180,171,0.4); }
        .art-form__dot--yellow { background: rgba(255,183,133,0.4); }
        .art-form__dot--green { background: rgba(97,221,152,0.4); }
        .art-form__bar-title { font-family: var(--font-display); font-size: 10px; letter-spacing: 0.2em; color: rgba(255,255,255,0.4); }
        .art-form__body { padding: var(--sp-lg); }
        .art-field { margin-bottom: var(--sp-md); }
        .art-field label { display: block; font-family: var(--font-display); font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 6px; }
        .art-field input, .art-field textarea { width: 100%; padding: var(--sp-sm) var(--sp-md); font-family: var(--font-body); font-size: 15px; color: var(--on-surface); background: var(--void); border: 1px solid rgba(255,255,255,0.1); outline: none; border-radius: var(--radius); transition: border-color 0.2s; }
        .art-field input:focus, .art-field textarea:focus { border-color: var(--primary); }
        .art-field textarea { min-height: 150px; resize: vertical; }
        .art-field input::placeholder, .art-field textarea::placeholder { color: rgba(255,255,255,0.2); }
        .art-field .hint { font-size: 12px; color: rgba(255,255,255,0.25); margin-top: 4px; }
        .art-submit { display: flex; align-items: center; justify-content: center; gap: var(--sp-sm); width: 100%; padding: var(--sp-md) var(--sp-xl); background: var(--primary); color: var(--void); font-family: var(--font-display); font-size: 13px; font-weight: 700; letter-spacing: 0.15em; border: none; cursor: pointer; border-radius: var(--radius); transition: box-shadow 0.2s, transform 0.1s; }
        .art-submit:hover { box-shadow: 0 0 20px rgba(255,130,0,0.3); }
        .art-submit:active { transform: scale(0.98); }
        .art-submit .material-symbols-outlined { font-size: 18px; }
        .art-error { padding: var(--sp-sm) var(--sp-md); background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: var(--error); font-size: 14px; border-radius: var(--radius); margin-bottom: var(--sp-md); display: flex; align-items: center; gap: 8px; }
        /* Result */
        .art-result { margin-top: var(--sp-xl); }
        .art-result__card { background: var(--surface); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius); overflow: hidden; margin-bottom: var(--sp-lg); }
        .art-result__bar { background: var(--void); padding: 10px var(--sp-md); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: var(--sp-sm); }
        .art-result__bar-title { font-family: var(--font-display); font-size: 10px; letter-spacing: 0.2em; color: rgba(255,255,255,0.4); flex: 1; }
        .art-result__body { padding: var(--sp-lg); }
        .art-result__badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; font-family: var(--font-display); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; border-radius: 12px; margin-bottom: var(--sp-md); }
        .art-result__badge--cat { background: rgba(0,158,96,0.1); border: 1px solid rgba(0,158,96,0.3); color: var(--secondary-bright); }
        .art-result__badge--seo { background: rgba(255,130,0,0.1); border: 1px solid rgba(255,130,0,0.3); color: var(--primary); }
        .art-result h2 { font-family: var(--font-display); font-size: 20px; color: var(--white); margin-bottom: var(--sp-sm); }
        .art-result__meta-desc { color: rgba(255,255,255,0.5); font-size: 14px; line-height: 1.5; padding: var(--sp-sm) var(--sp-md); background: rgba(0,0,0,0.3); border-left: 2px solid var(--primary); border-radius: 0 var(--radius) var(--radius) 0; margin-bottom: var(--sp-md); }
        .art-result__intro { color: var(--on-surface-variant); font-size: 15px; line-height: 1.8; margin-bottom: var(--sp-lg); }
        .art-result__intro strong { color: var(--primary-dim); }
        .art-result__summary { color: rgba(255,255,255,0.6); font-size: 14px; line-height: 1.8; margin-bottom: var(--sp-lg); background: rgba(0,0,0,0.2); padding: var(--sp-md); border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05); }
        .art-result__summary strong { color: var(--primary); }
        .art-result__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: var(--sp-md); }
        .art-tag { padding: 3px 8px; font-family: var(--font-display); font-size: 10px; letter-spacing: 0.05em; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.5); border-radius: 10px; }
        .art-plan { list-style: none; }
        .art-plan__section { margin-bottom: var(--sp-md); }
        .art-plan__title { font-family: var(--font-display); font-size: 14px; font-weight: 700; color: var(--white); letter-spacing: 0.05em; padding: var(--sp-sm) var(--sp-md); background: rgba(255,130,0,0.08); border-left: 3px solid var(--primary); margin-bottom: var(--sp-xs); }
        .art-plan__desc { font-size: 13px; color: rgba(255,255,255,0.4); padding-left: var(--sp-lg); margin-bottom: var(--sp-xs); }
        .art-plan__subs { list-style: none; padding-left: var(--sp-lg); }
        .art-plan__sub { padding: 6px var(--sp-md); border-left: 1px solid rgba(255,255,255,0.06); margin-bottom: 2px; }
        .art-plan__sub-title { font-family: var(--font-display); font-size: 12px; color: var(--on-surface-variant); letter-spacing: 0.05em; }
        .art-plan__sub-desc { font-size: 12px; color: rgba(255,255,255,0.3); margin-top: 2px; }
        .copy-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.5); font-family: var(--font-display); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; border-radius: var(--radius); transition: all 0.2s; }
        .copy-btn:hover { background: rgba(255,130,0,0.1); border-color: rgba(255,130,0,0.3); color: var(--primary); }
        .copy-btn .material-symbols-outlined { font-size: 14px; }

        /* ====== ACTION MODAL ====== */
        .action-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--sp-md);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s;
        }
        .action-modal.active {
            opacity: 1;
            pointer-events: all;
        }
        .action-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
        }
        .action-modal__dialog {
            position: relative;
            width: 100%;
            max-width: 520px;
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius);
            overflow: hidden;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s;
        }
        .action-modal.active .action-modal__dialog {
            transform: translateY(0) scale(1);
        }
        .action-modal__bar {
            background: var(--void);
            padding: 10px var(--sp-md);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: var(--sp-sm);
        }
        .action-modal__bar-title {
            font-family: var(--font-display);
            font-size: 10px;
            letter-spacing: 0.2em;
            color: rgba(255,255,255,0.5);
            flex: 1;
            transition: color 0.3s;
        }
        .action-modal__close {
            background: none;
            border: none;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            padding: 2px;
            display: flex;
            transition: color 0.2s;
        }
        .action-modal__close:hover { color: var(--white); }
        .action-modal__close .material-symbols-outlined { font-size: 18px; }
        .action-modal__body {
            padding: var(--sp-md);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .action-modal__option { margin: 0; padding: 0; }

        /* --- Boutons d'option --- */
        .action-modal__btn {
            display: flex;
            align-items: center;
            gap: var(--sp-md);
            width: 100%;
            padding: var(--sp-md);
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius);
            cursor: pointer;
            text-align: left;
            transition: all 0.2s;
            font-family: inherit;
            color: inherit;
        }
        .action-modal__btn:hover {
            border-color: rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.06);
            transform: translateX(4px);
        }
        .action-modal__btn-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .action-modal__btn-icon .material-symbols-outlined {
            font-size: 20px;
        }
        .action-modal__btn-text {
            flex: 1;
            min-width: 0;
        }
        .action-modal__btn-title {
            display: block;
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 600;
            color: var(--white);
            letter-spacing: 0.02em;
            margin-bottom: 2px;
        }
        .action-modal__btn-desc {
            display: block;
            font-size: 11px;
            color: rgba(255,255,255,0.35);
            line-height: 1.4;
        }
        .action-modal__btn-arrow {
            font-size: 16px;
            color: rgba(255,255,255,0.15);
            transition: color 0.2s, transform 0.2s;
        }
        .action-modal__btn:hover .action-modal__btn-arrow {
            color: rgba(255,255,255,0.4);
            transform: translateX(2px);
        }

        /* Variants */
        .action-modal__btn--publish .action-modal__btn-icon {
            background: rgba(0,158,96,0.1);
            border: 1px solid rgba(0,158,96,0.2);
            color: var(--secondary-bright);
        }
        .action-modal__btn--publish:hover {
            border-color: rgba(0,158,96,0.3);
            background: rgba(0,158,96,0.05);
        }
        .action-modal__btn--copy .action-modal__btn-icon {
            background: rgba(255,130,0,0.1);
            border: 1px solid rgba(255,130,0,0.2);
            color: var(--primary);
        }
        .action-modal__btn--copy:hover {
            border-color: rgba(255,130,0,0.3);
            background: rgba(255,130,0,0.05);
        }
        .action-modal__btn--download .action-modal__btn-icon {
            background: rgba(100,149,237,0.1);
            border: 1px solid rgba(100,149,237,0.2);
            color: #6495ED;
        }
        .action-modal__btn--download:hover {
            border-color: rgba(100,149,237,0.3);
            background: rgba(100,149,237,0.05);
        }
        .action-modal__btn--json .action-modal__btn-icon {
            background: rgba(186,85,211,0.1);
            border: 1px solid rgba(186,85,211,0.2);
            color: #BA55D3;
        }
        .action-modal__btn--json:hover {
            border-color: rgba(186,85,211,0.3);
            background: rgba(186,85,211,0.05);
        }
    </style>
</head>
<body>

    <!-- Header -->
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
            </div>
        </div>
    </header>

    <main class="art-page">
        <div class="art-container">

            <a href="index.php" class="art-back">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Retour au portfolio
            </a>

            <div class="art-header">
                <div class="art-header__label">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    Outil Rédactionnel v1.0
                </div>
                <h1>ARTICLE <span>STRUCTURER</span></h1>
                <p>Collez un lien ou une info brute et obtenez un plan détaillé, une introduction captivante et un article SEO-optimisé. 70% du travail mâché en quelques secondes.</p>
            </div>

            <!-- Formulaire -->
            <div class="art-form">
                <div class="art-form__bar">
                    <div class="art-form__dots">
                        <span class="art-form__dot art-form__dot--red"></span>
                        <span class="art-form__dot art-form__dot--yellow"></span>
                        <span class="art-form__dot art-form__dot--green"></span>
                    </div>
                    <span class="art-form__bar-title">INPUT_TERMINAL</span>
                </div>
                <div class="art-form__body">
                    <?php if ($error): ?>
                        <div class="art-error">
                            <span class="material-symbols-outlined" style="font-size:18px;">error</span>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($published)): ?>
                        <div class="art-error" style="background:rgba(0,158,96,0.1); border-color:rgba(0,158,96,0.3); color:var(--secondary-bright);">
                            <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                            Article publié avec succès dans le BLOG_FEED !
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="article_structurer.php">
                        <div class="art-field">
                            <label for="topic">Sujet / Thème</label>
                            <input type="text" id="topic" name="topic" placeholder="Ex: Nouvelle mise à jour Flutter 3.24" value="<?= htmlspecialchars(
                                $inputTopic,
                            ) ?>">
                            <div class="hint">Décrivez le sujet de votre article en une phrase.</div>
                        </div>

                        <div class="art-field">
                            <label for="url">Lien source (optionnel)</label>
                            <input type="url" id="url" name="url" placeholder="https://example.com/article-sur-le-sujet" value="<?= htmlspecialchars(
                                $inputUrl,
                            ) ?>">
                            <div class="hint">L'article analysera automatiquement le contenu de cette page.</div>
                        </div>

                        <div class="art-field">
                            <label for="raw_text">Ou texte brut / notes (optionnel)</label>
                            <textarea id="raw_text" name="raw_text" placeholder="Collez ici vos notes brutes, un communiqué, une description produit, des notes techniques..."><?= htmlspecialchars(
                                $inputText,
                            ) ?></textarea>
                            <div class="hint">Minimum 50 mots pour une analyse pertinente.</div>
                        </div>

                        <button type="submit" class="art-submit">
                            <span>GÉNÉRER L'ARTICLE</span>
                            <span class="material-symbols-outlined">auto_awesome</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Résultat -->
            <?php if ($result): ?>
            <div class="art-result">
                <div class="art-result__card">
                    <div class="art-result__bar">
                        <div class="art-form__dots">
                            <span class="art-form__dot art-form__dot--red"></span>
                            <span class="art-form__dot art-form__dot--yellow"></span>
                            <span class="art-form__dot art-form__dot--green"></span>
                        </div>
                        <span class="art-result__bar-title">OUTPUT_ARTICLE</span>
                        <button class="copy-btn" onclick="openActionModal()">
                            <span class="material-symbols-outlined" style="font-size:14px;">rocket_launch</span>
                            Actions
                        </button>
                    </div>
                    <div class="art-result__body">
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:var(--sp-md);">
                            <span class="art-result__badge art-result__badge--cat">
                                <span class="material-symbols-outlined" style="font-size:12px;">category</span>
                                <?= htmlspecialchars($result["category"]) ?>
                            </span>
                            <span class="art-result__badge art-result__badge--seo">
                                <span class="material-symbols-outlined" style="font-size:12px;">seo</span>
                                SEO Optimisé
                            </span>
                        </div>

                        <h2><?= htmlspecialchars($result["seo_title"]) ?></h2>

                        <div class="art-result__meta-desc">
                            <strong style="color:rgba(255,255,255,0.5); font-size:10px; letter-spacing:0.1em; display:block; margin-bottom:4px;">META_DESCRIPTION</strong>
                            <?= htmlspecialchars($result["meta_description"]) ?>
                        </div>

                        <?php if (!empty($result["tags"])): ?>
                        <div class="art-result__tags">
                            <?php foreach ($result["tags"] as $tag): ?>
                            <span class="art-tag"><?= htmlspecialchars(
                                $tag,
                            ) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div style="margin-bottom:var(--sp-sm); font-family:var(--font-display); font-size:11px; letter-spacing:0.15em; color:rgba(255,255,255,0.3); text-transform:uppercase;">Introduction</div>
                        <div class="art-result__intro"><?= nl2br(
                            $result["introduction"],
                        ) ?></div>

                        <div style="margin-bottom:var(--sp-sm); font-family:var(--font-display); font-size:11px; letter-spacing:0.15em; color:rgba(255,255,255,0.3); text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                            <span class="material-symbols-outlined" style="font-size:14px; color:var(--primary);">format_list_bulleted</span>
                            Plan détaillé
                        </div>

                        <ul class="art-plan">
                            <?php foreach (
                                $result["plan"]
                                as $i => $section
                            ): ?>
                            <li class="art-plan__section">
                                <div class="art-plan__title">
                                    <span style="color:var(--primary); margin-right:8px;"><?= str_pad(
                                        $i + 1,
                                        2,
                                        "0",
                                        STR_PAD_LEFT,
                                    ) ?></span>
                                    <?= htmlspecialchars($section["title"]) ?>
                                </div>
                                <div class="art-plan__desc"><?= htmlspecialchars(
                                    $section["desc"],
                                ) ?></div>
                                <ul class="art-plan__subs">
                                    <?php foreach (
                                        $section["subsections"]
                                        as $sub
                                    ): ?>
                                    <li class="art-plan__sub">
                                        <div class="art-plan__sub-title">→ <?= htmlspecialchars(
                                            $sub["title"],
                                        ) ?></div>
                                        <div class="art-plan__sub-desc"><?= htmlspecialchars(
                                            $sub["desc"],
                                        ) ?></div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (!empty($result["full_summary"])): ?>
                        <!-- Résumé complet -->
                        <div style="margin-top:var(--sp-lg); margin-bottom:var(--sp-sm); font-family:var(--font-display); font-size:11px; letter-spacing:0.15em; color:rgba(255,255,255,0.3); text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                            <span class="material-symbols-outlined" style="font-size:14px; color:var(--secondary-bright);">summarize</span>
                            Résumé complet
                        </div>
                        <div class="art-result__summary"><?= nl2br(
                            htmlspecialchars($result["full_summary"]),
                        ) ?></div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div id="raw-output" style="display:none;"><?= htmlspecialchars(
                json_encode(
                    $result,
                    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
                ),
            ) ?></div>

            <!-- ====== ACTION MODAL ====== -->
            <div class="action-modal" id="actionModal">
                <div class="action-modal__backdrop" onclick="closeActionModal()"></div>
                <div class="action-modal__dialog">
                    <div class="action-modal__bar">
                        <div class="art-form__dots">
                            <span class="art-form__dot art-form__dot--red"></span>
                            <span class="art-form__dot art-form__dot--yellow"></span>
                            <span class="art-form__dot art-form__dot--green"></span>
                        </div>
                        <span class="action-modal__bar-title">QUE VOULEZ-VOUS FAIRE ?</span>
                        <button class="action-modal__close" onclick="closeActionModal()">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="action-modal__body">

                        <!-- Option 1 : Publier sur le blog -->
                        <?php if ($isLoggedIn && isAdmin()): ?>
                        <form method="POST" action="article_structurer.php" class="action-modal__option" id="publishForm">
                            <input type="hidden" name="topic" value="<?= htmlspecialchars(
                                $inputTopic,
                            ) ?>">
                            <input type="hidden" name="url" value="<?= htmlspecialchars(
                                $inputUrl,
                            ) ?>">
                            <input type="hidden" name="raw_text" value="<?= htmlspecialchars(
                                $inputText,
                            ) ?>">
                            <input type="hidden" name="publish" value="1">
                            <button type="submit" class="action-modal__btn action-modal__btn--publish">
                                <span class="action-modal__btn-icon">
                                    <span class="material-symbols-outlined">publish</span>
                                </span>
                                <span class="action-modal__btn-text">
                                    <span class="action-modal__btn-title">Publier sur le Blog</span>
                                    <span class="action-modal__btn-desc">L'article apparaîtra dans la section BLOG_FEED de la page d'accueil.</span>
                                </span>
                                <span class="material-symbols-outlined action-modal__btn-arrow">arrow_forward</span>
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Option 2 : Copier le texte -->
                        <button class="action-modal__btn action-modal__btn--copy" onclick="copyAsText()">
                            <span class="action-modal__btn-icon">
                                <span class="material-symbols-outlined">content_copy</span>
                            </span>
                            <span class="action-modal__btn-text">
                                <span class="action-modal__btn-title">Copier le texte</span>
                                <span class="action-modal__btn-desc">Titre, méta, introduction et plan — prêt à coller dans votre éditeur.</span>
                            </span>
                            <span class="material-symbols-outlined action-modal__btn-arrow">arrow_forward</span>
                        </button>

                        <!-- Option 3 : Télécharger en Markdown -->
                        <button class="action-modal__btn action-modal__btn--download" onclick="downloadMarkdown()">
                            <span class="action-modal__btn-icon">
                                <span class="material-symbols-outlined">download</span>
                            </span>
                            <span class="action-modal__btn-text">
                                <span class="action-modal__btn-title">Télécharger en Markdown</span>
                                <span class="action-modal__btn-desc">Fichier .md formaté prêt pour votre CMS ou GitHub.</span>
                            </span>
                            <span class="material-symbols-outlined action-modal__btn-arrow">arrow_forward</span>
                        </button>

                        <!-- Option 4 : Copier le JSON -->
                        <button class="action-modal__btn action-modal__btn--json" onclick="copyAsJSON()">
                            <span class="action-modal__btn-icon">
                                <span class="material-symbols-outlined">data_object</span>
                            </span>
                            <span class="action-modal__btn-text">
                                <span class="action-modal__btn-title">Copier le JSON brut</span>
                                <span class="action-modal__btn-desc">Données structurées complètes pour intégration API ou traitement.</span>
                            </span>
                            <span class="material-symbols-outlined action-modal__btn-arrow">arrow_forward</span>
                        </button>

                    </div>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- Bottom Nav -->
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

    <script>
        // ====== Action Modal ======
        function openActionModal() {
            document.getElementById('actionModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeActionModal() {
            document.getElementById('actionModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeActionModal();
        });

        // ====== Helpers ======
        function getArticleData() {
            const el = document.getElementById('raw-output');
            if (!el) return null;
            return JSON.parse(el.textContent);
        }

        function buildText(data) {
            let text = 'TITRE SEO : ' + data.seo_title + '\n\n';
            text += 'META DESCRIPTION : ' + data.meta_description + '\n\n';
            text += 'TAGS : ' + (data.tags || []).join(', ') + '\n\n';
            text += '=== INTRODUCTION ===\n' + data.introduction.replace(/<br\s*\/?>/gi, '\n').replace(/\*\*/g, '') + '\n\n';
            text += '=== PLAN DÉTAILLÉ ===\n\n';
            (data.plan || []).forEach((s, i) => {
                text += (i+1) + '. ' + s.title + '\n   ' + s.desc + '\n';
                (s.subsections || []).forEach(sub => {
                    text += '   → ' + sub.title + '\n     ' + sub.desc + '\n';
                });
                text += '\n';
            });
            return text;
        }

        function buildMarkdown(data) {
            let md = '# ' + data.seo_title + '\n\n';
            md += '> ' + data.meta_description + '\n\n';
            if (data.tags && data.tags.length) {
                md += '**Tags :** ' + data.tags.map(t => '`' + t + '`').join(' ') + '\n\n';
            }
            md += '---\n\n';
            md += '## Introduction\n\n' + data.introduction.replace(/<br\s*\/?>/gi, '\n') + '\n\n';
            md += '---\n\n';
            (data.plan || []).forEach((s, i) => {
                md += '## ' + (i+1) + '. ' + s.title + '\n\n';
                md += s.desc + '\n\n';
                (s.subsections || []).forEach(sub => {
                    md += '### → ' + sub.title + '\n\n';
                    md += sub.desc + '\n\n';
                });
            });
            return md;
        }

        function showSuccess(msg) {
            const bar = document.querySelector('.action-modal__bar-title');
            const orig = bar.textContent;
            bar.textContent = msg;
            bar.style.color = 'var(--secondary-bright)';
            setTimeout(() => {
                bar.textContent = orig;
                bar.style.color = '';
                closeActionModal();
            }, 1500);
        }

        // ====== Actions ======

        function copyAsText() {
            const data = getArticleData();
            if (!data) return;
            navigator.clipboard.writeText(buildText(data)).then(() => {
                showSuccess('✓ Texte copié !');
            });
        }

        function copyAsJSON() {
            const data = getArticleData();
            if (!data) return;
            const json = JSON.stringify(data, null, 2);
            navigator.clipboard.writeText(json).then(() => {
                showSuccess('✓ JSON copié !');
            });
        }

        function downloadMarkdown() {
            const data = getArticleData();
            if (!data) return;
            const md = buildMarkdown(data);
            const blob = new Blob([md], { type: 'text/markdown;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const slug = (data.subject || data.seo_title || 'article').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            a.href = url;
            a.download = slug + '.md';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showSuccess('✓ Markdown téléchargé !');
        }
    </script>
</body>
</html>
