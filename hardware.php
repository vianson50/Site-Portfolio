<?php
/**
 * BLACK_PROTOCOL — Hardware Gaming
 * Page publique affichant les actualités hardware gaming (GPU, stockage, périphériques, consoles, audio, écrans, accessoires, moteurs & outils)
 * Design cyberpunk, filtrage par catégorie, recherche.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";

$lang = "fr";
$charset = "UTF-8";
$seoTitle = "Hardware Gaming — BLACK_PROTOCOL";
$seoDesc =
    "Toutes les actualités hardware gaming : GPU, processeurs, stockage, périphériques, consoles, audio, écrans, accessoires et moteurs de jeu. Restez informé des dernières sorties et tendances.";
$seoKeywords = [
    "hardware",
    "gaming",
    "GPU",
    "processeur",
    "stockage",
    "périphériques",
    "consoles",
    "audio gaming",
    "écrans",
    "accessoires",
    "moteurs de jeu",
    "PC gamer",
    "actualités",
];
$title = $seoTitle;
$year = date("Y");

$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

// ── Catégories ──
$categories = [
    "all" => ["label" => "Tous", "icon" => "apps"],
    "gpu" => ["label" => "GPU & Processeurs", "icon" => "memory"],
    "stockage" => ["label" => "Stockage", "icon" => "sd_storage"],
    "peripheriques" => ["label" => "Périphériques", "icon" => "keyboard"],
    "consoles" => ["label" => "Consoles", "icon" => "gamepad"],
    "audio" => ["label" => "Audio", "icon" => "headphones"],
    "ecrans" => ["label" => "Écrans", "icon" => "monitor"],
    "accessoires" => ["label" => "Accessoires", "icon" => "chair"],
    "moteurs" => ["label" => "Moteurs & Outils", "icon" => "code"],
];

// ── Couleurs par catégorie ──
$catColors = [
    "gpu" => "#22c55e",
    "stockage" => "#38bdf8",
    "peripheriques" => "#ff8200",
    "consoles" => "#a855f7",
    "audio" => "#ec4899",
    "ecrans" => "#eab308",
    "accessoires" => "#f97316",
    "moteurs" => "#4ecdc4",
];

// ── Mots-clés pour catégorisation automatique ──
$catKeywords = [
    "gpu" => [
        "carte graphique",
        "gpu",
        "rtx",
        "radeon",
        "geforce",
        "nvidia",
        "amd radeon",
        "arc ",
        "battlemage",
        "dlss",
        "fsr",
        "processeur",
        "cpu",
        "intel core",
        "ryzen",
        "overclocking",
    ],
    "stockage" => [
        "ssd",
        "nvme",
        "disque dur",
        "hdd",
        "stockage",
        "microsd",
        "to ",
        "go ",
        "samsung evo",
        "wd black",
        "seagate",
        "crucial",
    ],
    "peripheriques" => [
        "clavier",
        "souris",
        "manette",
        "gamepad",
        "joystick",
        "controller",
        "deathadder",
        "logitech g",
        "corsair k",
        "razer",
        "hyperx",
        "manette access",
        "adaptatif",
    ],
    "consoles" => [
        "ps5",
        "ps6",
        "xbox",
        "nintendo",
        "switch",
        "steam deck",
        "rog ally",
        "legion go",
        "console",
        "portable",
        "project helix",
    ],
    "audio" => [
        "casque",
        "headset",
        "audio",
        "arctis",
        "turtle beach",
        "hyperx cloud",
        "sony inzone",
        "blackshark",
        "micro",
        "microphone",
        "speaker",
        "haut-parleur",
    ],
    "ecrans" => [
        "ecran",
        "moniteur",
        "monitor",
        "oled",
        "g-sync",
        "freesync",
        "hz ",
        "4k",
        "144hz",
        "240hz",
        "360hz",
        "samsung odyssey",
        "rog swift",
        "alienware",
        "lg ultragear",
    ],
    "accessoires" => [
        "chaise",
        "bureau",
        "siège",
        "gamer chair",
        "secretlab",
        "quest ",
        "vr",
        "réalité virtuelle",
        "stream deck",
        "webcam",
        "elgato",
        "razer enki",
    ],
    "moteurs" => [
        "unreal engine",
        "unity",
        "godot",
        "gamemaker",
        "cryengine",
        "moteur ",
        "engine",
        "développement",
        "sdk",
        "api",
        "game engine",
    ],
];

// ── Sources RSS ──
$rssSources = [
    ["name" => "Frandroid", "url" => "https://www.frandroid.com/feed"],
    [
        "name" => "Les Numériques",
        "url" => "https://www.lesnumeriques.com/rss/rss.xml",
    ],
    ["name" => "Clubic", "url" => "https://www.clubic.com/feed.rss"],
    [
        "name" => "Journal du Geek",
        "url" => "https://www.journaldugeek.com/feed",
    ],
    [
        "name" => "Tom's Hardware FR",
        "url" => "https://www.tomshardware.fr/feed/",
    ],
];

// ── Hardware keywords pour filtrer les articles gaming/tech ──
$hardwareKeywords = [
    "gpu",
    "carte graphique",
    "rtx",
    "radeon",
    "geforce",
    "nvidia",
    "processeur",
    "cpu",
    "ryzen",
    "ssd",
    "nvme",
    "disque",
    "stockage",
    "clavier",
    "souris",
    "manette",
    "casque",
    "ecran",
    "monitor",
    "oled",
    "console",
    "ps5",
    "ps6",
    "xbox",
    "nintendo",
    "switch",
    "steam deck",
    "rog ally",
    "vr",
    "quest",
    "gaming",
    "gamer",
    "unreal",
    "unity",
    "godot",
    "pc ",
    "laptop",
    "pc gamer",
    "ram",
    "ddr5",
    "motherboard",
    "carte mere",
    "alimentation",
    "psu",
    "boitier",
    "refroidissement",
    "watercooling",
    "aio",
    "4k",
    "144hz",
    "240hz",
    "hz",
    "oled",
    "wifi",
    "bluetooth",
    "usb-c",
    "intel",
    "amd",
    "samsung",
    "lg ",
    "asus",
    "msi",
    "gigabyte",
    "corsair",
    "razer",
    "logitech",
    "steelseries",
    "hyperx",
    "secretlab",
    "sony",
    "lenovo",
    "snapdragon",
    "mediatek",
    "exynos",
    "apple",
    "iphone",
    "pixel",
    "telecom",
    "5g",
    "fibre",
    "box ",
    "routeur",
    "mesh",
    "ia ",
    "intelligence artificielle",
    "llm",
    "chatgpt",
    "windows",
    "android",
    "ios",
    "macos",
    "linux",
    "drone",
    "robot",
    "imprimante",
    "3d",
];

/**
 * Catégoriser un article par mots-clés
 */
function hw_categorize($title, $desc, $catKeywords)
{
    $text = strtolower($title . " " . $desc);
    $scores = [];
    foreach ($catKeywords as $cat => $keywords) {
        $score = 0;
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) {
                $score++;
            }
        }
        if ($score > 0) {
            $scores[$cat] = $score;
        }
    }
    if (empty($scores)) {
        return "accessoires"; // défaut
    }
    arsort($scores);
    return array_key_first($scores);
}

/**
 * Vérifier si un article est lié au hardware/tech
 */
function hw_is_relevant($title, $desc, $hardwareKeywords)
{
    $text = strtolower($title . " " . $desc);
    foreach ($hardwareKeywords as $kw) {
        if (strpos($text, trim($kw)) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Fetch et parse un flux RSS
 */
function hw_fetch_rss($source)
{
    $ctx = stream_context_create([
        "http" => [
            "timeout" => 8,
            "header" => "User-Agent: BLACK_PROTOCOL/1.0\r\n",
        ],
    ]);
    $xml = @file_get_contents($source["url"], false, $ctx);
    if (!$xml) {
        return [];
    }

    $rss = @simplexml_load_string($xml);
    if (!$rss) {
        return [];
    }

    $items = $rss->channel->item ?? [];
    $articles = [];
    foreach ($items as $item) {
        $title = (string) ($item->title ?? "");
        $desc = strip_tags((string) ($item->description ?? ""));
        $desc = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, "UTF-8");
        $desc = mb_substr(trim($desc), 0, 250);
        $link = (string) ($item->link ?? "");
        $date = (string) ($item->pubDate ?? "");
        $pubDate = $date ? date("Y-m-d", strtotime($date)) : date("Y-m-d");

        $articles[] = [
            "id" => md5($title . $source["name"]),
            "title" => $title,
            "category" => "", // sera catégorisé après
            "date" => $pubDate,
            "excerpt" => $desc,
            "url" => $link,
            "source" => $source["name"],
            "views" => 0,
            "image" => null,
        ];
    }
    return $articles;
}

// ── Merge RSS + statique avec cache ──
$hwCache = __DIR__ . "/cache/hardware_rss_cache.json";
$hwCacheTTL = 1800; // 30 minutes

// Refresh si demandé
if (isset($_GET["refresh"]) && file_exists($hwCache)) {
    unlink($hwCache);
}

$articles = [];
$fromCache = false;

// Essayer le cache
if (file_exists($hwCache)) {
    $c = json_decode(file_get_contents($hwCache), true);
    if ($c && isset($c["ts"]) && time() - $c["ts"] < $hwCacheTTL) {
        $articles = $c["d"];
        $fromCache = true;
    }
}

if (!$fromCache) {
    // Fetch tous les flux RSS
    foreach ($rssSources as $src) {
        $fetched = hw_fetch_rss($src);
        foreach ($fetched as &$a) {
            if (hw_is_relevant($a["title"], $a["excerpt"], $hardwareKeywords)) {
                $a["category"] = hw_categorize(
                    $a["title"],
                    $a["excerpt"],
                    $catKeywords,
                );
                $articles[] = $a;
            }
        }
        unset($a);
    }

    // Ajouter les articles statiques
    $staticFile = __DIR__ . "/cache/hardware_news.json";
    if (file_exists($staticFile)) {
        $static = json_decode(file_get_contents($staticFile), true) ?: [];
        foreach ($static as $s) {
            $s["source"] = $s["source"] ?? "AFJV";
            $s["id"] = "static_" . ($s["id"] ?? rand());
            $articles[] = $s;
        }
    }

    // Dédupliquer par titre
    $seen = [];
    $unique = [];
    foreach ($articles as $a) {
        $key = strtolower(trim($a["title"] ?? ""));
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $a;
        }
    }

    // Trier par date décroissante
    usort($unique, function ($a, $b) {
        return strcmp($b["date"] ?? "", $a["date"] ?? "");
    });

    // Garder les 100 plus récents
    $articles = array_slice($unique, 0, 100);

    // Sauvegarder le cache
    $dir = dirname($hwCache);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $hwCache,
        json_encode(
            ["ts" => time(), "d" => $articles],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        ),
    );
}

// ── Stats ──
$totalArticles = count($articles);
$totalCategories = count($catColors);
$sourcesUsed = array_unique(array_column($articles, "source"));
$totalSources = count($sourcesUsed);
$lastUpdate =
    $fromCache && file_exists($hwCache)
        ? date("d/m/Y H:i", filemtime($hwCache))
        : date("d/m/Y H:i");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="<?= $charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <?= renderMeta($seoTitle, $seoDesc, "website", null, $seoKeywords) ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"></noscript>
    <link rel="icon" type="image/png" href="Favicon/android-chrome-512x512.png">

    <style>
        :root {
            --void: #0a0a0b;
            --surface: #161618;
            --surface-2: #1e1e21;
            --primary: #ff8200;
            --primary-dim: #ffb785;
            --primary-glow: rgba(255,130,0,0.35);
            --secondary: #009e60;
            --secondary-bright: #61dd98;
            --secondary-glow: rgba(0,158,96,0.3);
            --white: #ffffff;
            --on-surface: #f4ded2;
            --on-surface-variant: #dec1af;
            --outline: #a68b7b;
            --font-display: "Space Grotesk", sans-serif;
            --font-body: "Inter", sans-serif;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: var(--font-body);
            background: var(--void);
            color: var(--on-surface);
            overflow-x: hidden;
            line-height: 1.6;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* ── Top Header ── */
        .top-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            background: rgba(10,10,11,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(166,139,123,0.1);
            height: 64px;
            display: flex;
            align-items: center;
        }
        .top-header__inner {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .top-header__brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .top-header__avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary);
        }
        .top-header__avatar img { width:100%; height:100%; object-fit:cover; }
        .top-header__name {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 14px;
            color: var(--primary);
            letter-spacing: 1px;
        }
        .top-header__back {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--on-surface-variant);
            text-decoration: none;
            font-size: 13px;
            font-family: var(--font-display);
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: color .2s;
        }
        .top-header__back:hover { color: var(--primary); }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ── Hero ── */
        .hw-hero {
            position: relative;
            padding: 140px 0 50px;
            text-align: center;
            overflow: hidden;
        }
        .hw-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(ellipse at 50% 0%, var(--primary-glow) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 100%, var(--secondary-glow) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .hw-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,130,0,0.1);
            border: 1px solid rgba(255,130,0,0.3);
            border-radius: 100px;
            padding: 6px 16px;
            font-size: 12px;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 1.5px;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        .hw-hero__badge .dot {
            width: 6px; height: 6px;
            background: var(--secondary-bright);
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }
        .hw-hero__title {
            font-family: var(--font-display);
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .hw-hero__title span {
            background: linear-gradient(135deg, var(--primary), var(--primary-dim));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hw-hero__subtitle {
            font-size: 1.05rem;
            color: var(--on-surface-variant);
            max-width: 600px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
        }

        /* ── Stats Row ── */
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .stat-item { text-align: center; }
        .stat-item__value {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        .stat-item__value--green { color: var(--secondary-bright); }
        .stat-item__label {
            font-size: 0.75rem;
            color: var(--on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        /* ── Toolbar (Search + Filters) ── */
        .hw-toolbar {
            position: sticky;
            top: 64px;
            z-index: 100;
            background: rgba(10,10,11,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(166,139,123,0.1);
            padding: 16px 0;
        }
        .toolbar-inner {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .search-bar {
            position: relative;
            max-width: 400px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px 16px 10px 42px;
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.15);
            border-radius: 10px;
            color: var(--white);
            font-family: var(--font-body);
            font-size: 0.85rem;
            outline: none;
            transition: border-color .2s;
        }
        .search-bar input::placeholder { color: var(--outline); }
        .search-bar input:focus { border-color: var(--primary); }
        .search-bar .material-symbols-outlined {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--outline);
            font-size: 18px;
        }
        .filter-bar {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.15);
            border-radius: 100px;
            color: var(--on-surface-variant);
            font-family: var(--font-display);
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
        }
        .filter-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .filter-btn.active {
            background: var(--primary);
            color: var(--void);
            border-color: var(--primary);
            font-weight: 600;
        }
        .filter-btn .material-symbols-outlined { font-size: 15px; }

        /* ── Articles Grid ── */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            padding: 30px 0 60px;
        }

        /* ── Article Card Link ── */
        .article-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            border-radius: 14px;
            margin-bottom: 14px;
        }
        .article-card-link .article-card {
            cursor: pointer;
            transition: all .3s ease;
        }
        .article-card-link:hover .article-card {
            border-color: rgba(255,130,0,0.25);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3), 0 0 15px var(--card-color);
        }
        .article-card-link:hover .article-card::before {
            opacity: 1;
        }
        .article-card-link:hover .article-card__title {
            color: var(--primary);
        }
        .article-card__open {
            display: inline-flex;
            align-items: center;
            margin-left: auto;
            color: var(--outline);
            opacity: 0;
            transition: opacity .2s, color .2s;
        }
        .article-card-link:hover .article-card__open {
            opacity: 1;
            color: var(--primary);
        }
        .article-card__open .material-symbols-outlined {
            font-size: 16px;
        }
        .article-card-link.hidden { display: none; }

        /* ── Article Card ── */
        .article-card {
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.08);
            border-radius: 14px;
            padding: 22px 20px 18px;
            transition: all .25s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .article-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            border-radius: 3px 0 0 3px;
        }

        /* ── Category Tag (pill, top-right) ── */
        .article-card__tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 0.65rem;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            white-space: nowrap;
            position: absolute;
            top: 14px;
            right: 14px;
        }
        .article-card__tag .material-symbols-outlined { font-size: 13px; }

        /* ── Title ── */
        .article-card__title {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--white);
            line-height: 1.35;
            margin-bottom: 10px;
            padding-right: 110px;
        }

        /* ── Excerpt ── */
        .article-card__excerpt {
            font-size: 0.82rem;
            color: var(--on-surface-variant);
            line-height: 1.55;
            margin-bottom: 16px;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ── Bottom Row ── */
        .article-card__bottom {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            border-top: 1px solid rgba(166,139,123,0.08);
            padding-top: 12px;
        }
        .article-card__date {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            color: var(--outline);
        }
        .article-card__date .material-symbols-outlined { font-size: 14px; }
        .article-card__views {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            color: var(--outline);
        }
        .article-card__views .material-symbols-outlined { font-size: 14px; }
        .article-card__source {
            margin-left: auto;
            font-size: 0.65rem;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--primary);
            background: rgba(255,130,0,0.1);
            padding: 2px 10px;
            border-radius: 100px;
            text-transform: uppercase;
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        .empty-state__icon {
            font-size: 48px;
            color: var(--outline);
            margin-bottom: 16px;
        }
        .empty-state__text {
            font-size: 1rem;
            color: var(--on-surface-variant);
            margin-bottom: 8px;
        }
        .empty-state__sub {
            font-size: 0.85rem;
            color: var(--outline);
        }

        /* ── Footer ── */
        .hw-footer {
            padding: 40px 0;
            border-top: 1px solid rgba(166,139,123,0.08);
            text-align: center;
            color: var(--outline);
            font-size: 0.8rem;
        }
        .hw-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .hw-footer a:hover { text-decoration: underline; }

        /* ── Animations ── */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hw-hero { padding: 120px 0 40px; }
            .articles-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
            .stats-row { gap: 20px; }
            .stat-item__value { font-size: 1.4rem; }
            .filter-bar { gap: 4px; }
            .filter-btn { padding: 5px 10px; font-size: 0.7rem; }
        }
        @media (max-width: 480px) {
            .articles-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .article-card__title {
                font-size: 0.92rem;
                padding-right: 0;
                margin-top: 28px;
            }
            .article-card__tag {
                top: auto;
                bottom: 14px;
                right: 14px;
            }
            .search-bar { max-width: 100%; }
        }
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
            <a href="index.php" class="top-header__back">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                RETOUR
            </a>
        </div>
    </header>

    <!-- ================================
         HERO
         ================================ -->
    <section class="hw-hero">
        <div class="container">
            <div class="hw-hero__badge fade-up">
                <span class="dot"></span>
                HARDWARE // JEUX VIDÉO
            </div>
            <h1 class="hw-hero__title fade-up">
                Hardware <span>Gaming</span>
            </h1>
            <p class="hw-hero__subtitle fade-up">
                GPU, processeurs, stockage, périphériques, consoles, audio, écrans — toute l'actualité hardware pour les gamers.
            </p>
            <div class="stats-row fade-up">
                <div class="stat-item">
                    <div class="stat-item__value"><?= $totalArticles ?></div>
                    <div class="stat-item__label">Articles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value stat-item__value--green"><?= $totalSources ?></div>
                    <div class="stat-item__label">Sources</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value"><?= $totalCategories ?></div>
                    <div class="stat-item__label">Catégories</div>
                </div>
            </div>
            <div style="text-align:center;margin-top:12px;font-size:0.72rem;color:var(--outline);position:relative;z-index:1;">
                Dernière mise à jour : <?= $lastUpdate ?> — <a href="?refresh=1" style="color:var(--primary);text-decoration:none;">Rafraîchir</a>
            </div>
        </div>
    </section>

    <!-- ================================
         TOOLBAR (sticky)
         ================================ -->
    <div class="hw-toolbar">
        <div class="container">
            <div class="toolbar-inner">
                <!-- Search -->
                <div class="search-bar">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="hw-search" placeholder="Rechercher un article...">
                </div>
                <!-- Filters -->
                <div class="filter-bar" id="filter-bar">
                    <?php foreach ($categories as $key => $cat): ?>
                    <button class="filter-btn<?= $key === "all"
                        ? " active"
                        : "" ?>" data-filter="<?= $key ?>">
                        <span class="material-symbols-outlined"><?= $cat[
                            "icon"
                        ] ?></span>
                        <?= $cat["label"] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================
         ARTICLES GRID
         ================================ -->
    <div class="container">
        <div class="articles-grid" id="articles-grid">
            <?php foreach ($articles as $article):

                $cat = $article["category"] ?? "gpu";
                $color = $catColors[$cat] ?? "#a68b7b";
                $catLabel = $categories[$cat]["label"] ?? $cat;
                $catIcon = $categories[$cat]["icon"] ?? "article";
                $views = $article["views"] ?? 0;
                if ($views >= 1000) {
                    $viewsDisplay = round($views / 1000, 1) . "k";
                } else {
                    $viewsDisplay = $views;
                }
                $date = $article["date"] ?? "";
                if ($date) {
                    $dt = new DateTime($date);
                    $monthsFr = [
                        1 => "Jan",
                        2 => "Fév",
                        3 => "Mar",
                        4 => "Avr",
                        5 => "Mai",
                        6 => "Juin",
                        7 => "Juil",
                        8 => "Août",
                        9 => "Sep",
                        10 => "Oct",
                        11 => "Nov",
                        12 => "Déc",
                    ];
                    $dateDisplay =
                        $dt->format("j") .
                        " " .
                        $monthsFr[(int) $dt->format("n")] .
                        " " .
                        $dt->format("Y");
                } else {
                    $dateDisplay = "";
                }
                ?>
            <?php if (!empty($article["url"])) {
                $articleUrl = $article["url"];
            } else {
                $searchQuery = urlencode(
                    $article["title"] ?? "hardware gaming",
                );
                $articleUrl = "https://www.google.com/search?q=" . $searchQuery;
            } ?>
            <a href="<?= htmlspecialchars(
                $articleUrl,
            ) ?>" target="_blank" rel="noopener noreferrer" class="article-card-link fade-up"
                 data-category="<?= htmlspecialchars($cat) ?>"
                 data-title="<?= htmlspecialchars($article["title"] ?? "") ?>"
                 style="--card-color: <?= $color ?>;">

                <div class="article-card">
                    <!-- Category Tag -->
                    <span class="article-card__tag" style="background:<?= $color ?>22; color:<?= $color ?>;">
                        <span class="material-symbols-outlined"><?= $catIcon ?></span>
                        <?= htmlspecialchars($catLabel) ?>
                    </span>

                    <!-- Title -->
                    <h3 class="article-card__title"><?= htmlspecialchars(
                        $article["title"] ?? "",
                    ) ?></h3>

                    <!-- Excerpt -->
                    <p class="article-card__excerpt"><?= htmlspecialchars(
                        $article["excerpt"] ?? "",
                    ) ?></p>

                    <!-- Bottom Row -->
                    <div class="article-card__bottom">
                        <?php if ($dateDisplay): ?>
                        <span class="article-card__date">
                            <span class="material-symbols-outlined">calendar_today</span>
                            <?= $dateDisplay ?>
                        </span>
                        <?php endif; ?>
                        <span class="article-card__views">
                            <span class="material-symbols-outlined">visibility</span>
                            <?= $viewsDisplay ?>
                        </span>
                        <?php if (!empty($article["source"])): ?>
                        <span class="article-card__source"><?= htmlspecialchars(
                            $article["source"],
                        ) ?></span>
                        <?php endif; ?>
                        <span class="article-card__open">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </span>
                    </div>
                </div>
            </a>
            <?php
            endforeach; ?>

            <!-- Empty state for filtered results -->
            <div class="empty-state" id="no-results" style="display:none;">
                <div class="empty-state__icon material-symbols-outlined">search_off</div>
                <div class="empty-state__text">Aucun article trouvé</div>
                <div class="empty-state__sub">Essayez un autre filtre ou terme de recherche.</div>
            </div>
        </div>
    </div>

    <!-- ================================
         FOOTER
         ================================ -->
    <footer class="hw-footer">
        <div class="container">
            BLACK_PROTOCOL &copy; <?= $year ?> — Hardware Gaming // Actualités mises à jour régulièrement
        </div>
    </footer>

    <script>
        // ── Category colors & labels (from PHP) ──
        const catColors = <?= json_encode($catColors) ?>;
        const categories = <?= json_encode($categories) ?>;

        // ── Filter state ──
        const cards = document.querySelectorAll('.article-card-link');
        const noResults = document.getElementById('no-results');
        const searchInput = document.getElementById('hw-search');

        let currentFilter = 'all';
        let currentSearch = '';

        // ── Apply all filters ──
        function applyFilters() {
            let visibleCount = 0;
            cards.forEach(card => {
                const cat = card.getAttribute('data-category');
                const title = (card.getAttribute('data-title') || '').toLowerCase();
                const search = currentSearch.toLowerCase();
                const matchCat = currentFilter === 'all' || cat === currentFilter;
                const matchSearch = search === '' || title.includes(search);
                if (matchCat && matchSearch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // ── Category Filters ──
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                applyFilters();
            });
        });

        // ── Search ──
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.toLowerCase().trim();
            applyFilters();
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
        }, { threshold: 0.1 });
        fadeEls.forEach(el => fadeObserver.observe(el));

        // ── Inject category colors (left border) ──
        <?php foreach ($catColors as $cat => $color): ?>
        document.head.insertAdjacentHTML('beforeend', '<style>.article-card[data-category="<?= $cat ?>"]::before { background: <?= $color ?>; }</style>');
        document.head.insertAdjacentHTML('beforeend', '<style>.article-card[data-category="<?= $cat ?>"]:hover { border-color: <?= $color ?>55; box-shadow: 0 8px 30px <?= $color ?>22; }</style>');
        <?php endforeach; ?>
    </script>
</body>
</html>
