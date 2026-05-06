<?php
/**
 * BLACK_PROTOCOL — Agenda Jeux Vidéo
 * Page publique affichant les événements gaming (salons, festivals, esport, conférences...)
 * Design cyberpunk, filtrage par catégorie et mois, recherche.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/seo.php";

$lang = "fr";
$charset = "UTF-8";
$seoTitle = "Agenda Jeux Vidéo 2026 — BLACK_PROTOCOL";
$seoDesc =
    "Agenda complet des événements jeux vidéo, esport, festivals et salons gaming en 2026. Salons, compétitions, conférences, game jams et plus encore.";
$seoKeywords = [
    "agenda",
    "jeux vidéo",
    "salon gaming",
    "esport",
    "festival",
    "game jam",
    "conférence",
    "2026",
    "événement",
    "gaming",
];
$title = $seoTitle;
$year = date("Y");

$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
if ($isLoggedIn && !$currentUser) {
    $isLoggedIn = false;
}

// ── Config ──
$API_KEY = "zDJK_rpaAxN-2qEzji6mNfCcXA-UdTCtLQLuJxfCp2zFNTpb6UI";
$AGENDA_CACHE = __DIR__ . "/cache/agenda_merged_cache.json";
$CACHE_TTL = 3600; // 1 heure

// ── Charger les événements statiques ──
$eventsFile = __DIR__ . "/cache/agenda_events.json";
$staticEvents = [];
if (file_exists($eventsFile)) {
    $raw = file_get_contents($eventsFile);
    $staticEvents = json_decode($raw, true) ?: [];
}

// ── Catégories ──
$categories = [
    "all" => ["label" => "Tous", "icon" => "apps"],
    "salon" => ["label" => "Salon", "icon" => "storefront"],
    "festival" => ["label" => "Festival", "icon" => "celebration"],
    "esport" => ["label" => "Esport", "icon" => "sports_esports"],
    "conference" => ["label" => "Conférence", "icon" => "mic"],
    "gamejam" => ["label" => "Game Jam", "icon" => "code"],
    "jpo" => ["label" => "JPO", "icon" => "school"],
    "expo" => ["label" => "Expo", "icon" => "museum"],
    "lan" => ["label" => "LAN", "icon" => "computer"],
];

// ── Couleurs par catégorie ──
$catColors = [
    "salon" => "#ff8200",
    "festival" => "#a855f7",
    "esport" => "#22c55e",
    "conference" => "#38bdf8",
    "gamejam" => "#f97316",
    "jpo" => "#ec4899",
    "expo" => "#eab308",
    "lan" => "#4ecdc4",
];

// ── PandaScore Fetch (cURL) ──
function agenda_fetch_pandascore($key)
{
    if (!function_exists("curl_init")) {
        return [];
    }

    $endpoints = [
        "/tournaments/running?sort=begin_at&per_page=20",
        "/tournaments/upcoming?sort=begin_at&per_page=20",
    ];

    $tournaments = [];
    foreach ($endpoints as $ep) {
        $sep = strpos($ep, "?") !== false ? "&" : "?";
        $fullUrl =
            "https://api.pandascore.co" .
            $ep .
            $sep .
            "token=" .
            urlencode($key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Authorization: Bearer " . $key,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($resp !== false && $code < 400) {
            $data = json_decode($resp, true);
            if (is_array($data)) {
                $tournaments = array_merge($tournaments, $data);
            }
        }
    }

    // Transformer en format agenda
    $esportEvents = [];
    $seen = [];
    foreach ($tournaments as $t) {
        $leagueName = $t["league"]["name"] ?? "Unknown";
        $tName = $t["name"] ?? "Tournament";
        $fullName = $leagueName . " — " . $tName;
        $hash = md5($fullName);
        if (isset($seen[$hash])) {
            continue;
        }
        $seen[$hash] = true;

        $slug = $t["videogame"]["slug"] ?? "";
        $location = $t["country"] ?? ($t["region"] ?? "En ligne");
        $isOnline = strtolower($t["type"] ?? "") === "online";

        $esportEvents[] = [
            "id" => "ps_" . ($t["id"] ?? rand()),
            "name" => $fullName,
            "start" => $t["begin_at"] ?? null,
            "end" => $t["end_at"] ?? null,
            "location" => $location ?: "En ligne",
            "department" => $isOnline ? "En ligne" : $t["country"] ?? "",
            "category" => "esport",
            "description" =>
                ($t["videogame"]["name"] ?? "Esport") .
                " — " .
                ($t["league"]["name"] ?? "") .
                ". " .
                ($t["prizepool"]
                    ? "Prize pool : " . $t["prizepool"]
                    : "Compétition esport."),
            "url" => $t["league"]["url"] ?? null,
            "online" => $isOnline,
            "source" => "pandascore",
            "game" => $t["videogame"]["name"] ?? "",
            "prizepool" => $t["prizepool"] ?? null,
            "tier" => $t["tier"] ?? null,
            "teams" => is_array($t["teams"] ?? null) ? count($t["teams"]) : 0,
            "logo" => $t["league"]["image_url"] ?? null,
        ];
    }
    return $esportEvents;
}

// ── Merge : cache ou re-fetch ──
function agenda_get_merged($staticEvents, $cacheFile, $ttl, $apiKey)
{
    // Refresh si demandé
    if (isset($_GET["refresh"]) && file_exists($cacheFile)) {
        unlink($cacheFile);
    }

    // Lecture cache
    if (file_exists($cacheFile)) {
        $c = json_decode(file_get_contents($cacheFile), true);
        if ($c && isset($c["ts"]) && time() - $c["ts"] < $ttl) {
            return $c["d"];
        }
    }

    // Fetch PandaScore
    $esportEvents = agenda_fetch_pandascore($apiKey);

    // Fusionner : static + esport
    $all = array_merge($staticEvents, $esportEvents);

    // Supprimer doublons par nom approximatif
    $seen = [];
    $unique = [];
    foreach ($all as $e) {
        $key = strtolower(trim($e["name"] ?? ""));
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $e;
        }
    }

    // Trier par date
    usort($unique, function ($a, $b) {
        $sa = $a["start"] ?? "2099-01-01";
        $sb = $b["start"] ?? "2099-01-01";
        return strcmp($sa, $sb);
    });

    // Écrire cache
    $dir = dirname($cacheFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $cacheFile,
        json_encode(
            ["ts" => time(), "d" => $unique],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        ),
    );

    return $unique;
}

$events = agenda_get_merged($staticEvents, $AGENDA_CACHE, $CACHE_TTL, $API_KEY);

// ── Noms de mois en français ──
$monthsFr = [
    1 => "Janvier",
    2 => "Février",
    3 => "Mars",
    4 => "Avril",
    5 => "Mai",
    6 => "Juin",
    7 => "Juillet",
    8 => "Août",
    9 => "Septembre",
    10 => "Octobre",
    11 => "Novembre",
    12 => "Décembre",
];

// ── Calculer les stats ──
$totalEvents = count($events);
$locationCities = array_unique(array_column($events, "location"));
$totalCities = count($locationCities);
$onlineCount = count(array_filter($events, fn($e) => !empty($e["online"])));

// ── Grouper par mois ──
$eventsByMonth = [];
foreach ($events as $e) {
    if (empty($e["start"])) {
        continue;
    }
    $m = (int) date("n", strtotime($e["start"]));
    if (!isset($eventsByMonth[$m])) {
        $eventsByMonth[$m] = [];
    }
    $eventsByMonth[$m][] = $e;
}
foreach ($eventsByMonth as &$list) {
    usort(
        $list,
        fn($a, $b) => strtotime($a["start"] ?? "now") -
            strtotime($b["start"] ?? "now"),
    );
}
unset($list);
ksort($eventsByMonth);

// ── Nombre d'événements PandaScore ──
$esportCount = count(
    array_filter($events, fn($e) => ($e["source"] ?? "") === "pandascore"),
);

// ── Années disponibles pour la navigation ──
$availableYears = [2026, 2025, 2024];
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
        .agenda-hero {
            position: relative;
            padding: 140px 0 50px;
            text-align: center;
            overflow: hidden;
        }
        .agenda-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(ellipse at 50% 0%, var(--primary-glow) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 100%, var(--secondary-glow) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .agenda-hero__badge {
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
        .agenda-hero__badge .dot {
            width: 6px; height: 6px;
            background: var(--secondary-bright);
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }
        .agenda-hero__title {
            font-family: var(--font-display);
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .agenda-hero__title span {
            background: linear-gradient(135deg, var(--primary), var(--primary-dim));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .agenda-hero__subtitle {
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
        .agenda-toolbar {
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

        /* ── Month Navigation ── */
        .month-nav {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            padding: 20px 0 10px;
        }
        .month-nav__btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid rgba(166,139,123,0.1);
            border-radius: 8px;
            color: var(--on-surface-variant);
            font-family: var(--font-display);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s ease;
        }
        .month-nav__btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .month-nav__btn.active {
            background: var(--primary);
            color: var(--void);
            border-color: var(--primary);
        }
        .month-nav__btn.has-events {
            position: relative;
        }
        .month-nav__btn.has-events::after {
            content: '';
            position: absolute;
            bottom: 3px;
            right: 5px;
            width: 4px; height: 4px;
            border-radius: 50%;
            background: var(--secondary-bright);
        }
        .month-nav__btn.active.has-events::after {
            background: var(--void);
        }

        /* ── Month Section ── */
        .month-section {
            padding: 10px 0 40px;
        }
        .month-section.hidden { display: none; }
        .month-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(166,139,123,0.1);
        }
        .month-header__label {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--white);
        }
        .month-header__count {
            font-size: 0.75rem;
            color: var(--outline);
            background: var(--surface);
            padding: 2px 10px;
            border-radius: 100px;
        }
        .month-header__line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(255,130,0,0.3), transparent);
        }

        /* ── Event Card ── */
        .event-card {
            cursor: pointer;
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 16px;
            align-items: start;
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.08);
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 10px;
            transition: all .25s ease;
            position: relative;
            overflow: hidden;
        }
        .event-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            border-radius: 3px 0 0 3px;
        }
        .event-card:hover {
            border-color: rgba(255,130,0,0.25);
            transform: translateX(4px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .event-card.hidden { display: none; }

        /* ── Date Block ── */
        .event-date {
            text-align: center;
            padding: 8px;
            background: var(--surface-2);
            border-radius: 10px;
        }
        .event-date__day {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1;
        }
        .event-date__month {
            font-size: 0.7rem;
            color: var(--primary);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .event-date__range {
            font-size: 0.65rem;
            color: var(--outline);
            margin-top: 2px;
        }

        /* ── Event Info ── */
        .event-info { min-width: 0; }
        .event-info__name {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 4px;
        }
        .event-info__desc {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin-bottom: 6px;
            line-height: 1.4;
        }
        .event-info__meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .event-info__location {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--outline);
        }
        .event-info__location .material-symbols-outlined {
            font-size: 14px;
        }
        .event-info__online {
            font-size: 0.7rem;
            color: var(--secondary-bright);
            background: rgba(0,158,96,0.1);
            padding: 2px 8px;
            border-radius: 100px;
            font-weight: 500;
        }

        /* ── Category Tag ── */
        .event-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .event-tag .material-symbols-outlined { font-size: 13px; }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
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

        /* ── Industry Stats ── */
        .industry-section {
            padding: 60px 0;
            border-top: 1px solid rgba(166,139,123,0.08);
            position: relative;
            overflow: hidden;
        }
        .industry-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 200px;
            background: radial-gradient(ellipse at 30% 0%, rgba(0,158,96,0.08) 0%, transparent 60%),
                        radial-gradient(ellipse at 70% 0%, rgba(255,130,0,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .industry-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        .industry-header__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0,158,96,0.1);
            border: 1px solid rgba(0,158,96,0.25);
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 11px;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 1.5px;
            color: var(--secondary-bright);
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .industry-header__title {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 10px;
        }
        .industry-header__title span {
            background: linear-gradient(135deg, var(--secondary-bright), var(--primary-dim));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .industry-header__desc {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            max-width: 550px;
            margin: 0 auto;
        }
        .industry-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        .industry-card {
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.08);
            border-radius: 16px;
            padding: 22px 20px;
            text-align: center;
            transition: all .3s ease;
            position: relative;
            overflow: hidden;
        }
        .industry-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity .3s;
        }
        .industry-card:hover {
            border-color: rgba(255,130,0,0.2);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        .industry-card:hover::after { opacity: 1; }
        .industry-card__icon {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .industry-card__icon--green { color: var(--secondary-bright); }
        .industry-card__value {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .industry-card__value span {
            font-size: 1rem;
            color: var(--primary);
        }
        .industry-card__label {
            font-size: 0.72rem;
            color: var(--on-surface-variant);
            line-height: 1.4;
        }
        .industry-card__trend {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 6px;
            padding: 2px 8px;
            border-radius: 100px;
        }
        .industry-card__trend--up {
            color: var(--secondary-bright);
            background: rgba(0,158,96,0.1);
        }
        .industry-card__trend--down {
            color: #ff6b6b;
            background: rgba(255,107,107,0.1);
        }
        .industry-card__trend .material-symbols-outlined { font-size: 14px; }

        /* ── Split Charts Row ── */
        .industry-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        .split-card {
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.08);
            border-radius: 16px;
            padding: 24px;
        }
        .split-card__title {
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .split-card__title .material-symbols-outlined {
            font-size: 18px;
            color: var(--primary);
        }
        .split-bar {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }
        .split-bar__label {
            font-size: 0.78rem;
            color: var(--on-surface-variant);
            min-width: 90px;
            text-align: right;
        }
        .split-bar__track {
            flex: 1;
            height: 22px;
            background: var(--surface-2);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }
        .split-bar__fill {
            height: 100%;
            border-radius: 6px;
            transition: width 1.5s ease;
            width: 0;
        }
        .split-bar__fill.animated { /* width set inline */ }
        .split-bar__pct {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--white);
            min-width: 40px;
        }

        /* ── Highlights Row ── */
        .industry-highlights {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            position: relative;
            z-index: 1;
        }
        .highlight-card {
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.08);
            border-radius: 14px;
            padding: 20px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .highlight-card__icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .highlight-card__icon .material-symbols-outlined { font-size: 22px; }
        .highlight-card__text h4 {
            font-family: var(--font-display);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 4px;
        }
        .highlight-card__text p {
            font-size: 0.75rem;
            color: var(--on-surface-variant);
            line-height: 1.5;
        }
        .industry-source {
            text-align: center;
            margin-top: 30px;
            font-size: 0.7rem;
            color: var(--outline);
            position: relative;
            z-index: 1;
        }
        .industry-source a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .industry-grid { grid-template-columns: repeat(2, 1fr); }
            .industry-split { grid-template-columns: 1fr; }
            .industry-highlights { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .industry-grid { grid-template-columns: 1fr; }
        }

        /* ── Footer ── */
        .agenda-footer {
            padding: 40px 0;
            border-top: 1px solid rgba(166,139,123,0.08);
            text-align: center;
            color: var(--outline);
            font-size: 0.8rem;
        }
        .agenda-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .agenda-footer a:hover { text-decoration: underline; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-card {
            background: var(--surface);
            border: 1px solid rgba(166,139,123,0.15);
            border-radius: 20px;
            max-width: 560px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.9) translateY(20px);
            transition: transform .3s ease;
        }
        .modal-overlay.active .modal-card {
            transform: scale(1) translateY(0);
        }
        .modal-close {
            position: absolute;
            top: 16px; right: 16px;
            background: var(--surface-2);
            border: 1px solid rgba(166,139,123,0.1);
            border-radius: 50%;
            width: 36px; height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--outline);
            cursor: pointer;
            font-size: 20px;
            transition: all .2s;
            z-index: 1;
        }
        .modal-close:hover { color: var(--primary); border-color: var(--primary); }
        .modal-header {
            padding: 24px 24px 0;
        }
        .modal-cat {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .modal-title {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.3;
            padding-right: 40px;
        }
        .modal-body { padding: 20px 24px 24px; }
        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }
        .modal-field {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .modal-field .material-symbols-outlined {
            font-size: 18px;
            color: var(--primary);
            margin-top: 2px;
        }
        .modal-field__label {
            font-size: 0.65rem;
            color: var(--outline);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .modal-field__value {
            font-size: 0.85rem;
            color: var(--white);
            font-weight: 500;
        }
        .modal-desc-section { margin-bottom: 16px; }
        .modal-desc {
            font-size: 0.85rem;
            color: var(--on-surface-variant);
            line-height: 1.6;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .modal-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-family: var(--font-display);
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid;
            transition: all .2s;
        }
        .modal-action-btn .material-symbols-outlined { font-size: 15px; }
        .modal-action-btn--primary {
            background: var(--primary);
            color: var(--void);
            border-color: var(--primary);
        }
        .modal-action-btn--primary:hover { box-shadow: 0 0 16px var(--primary-glow); }
        .modal-action-btn--outline {
            background: transparent;
            color: var(--on-surface-variant);
            border-color: rgba(166,139,123,0.2);
        }
        .modal-action-btn--outline:hover { border-color: var(--primary); color: var(--primary); }
        @media (max-width: 480px) {
            .modal-grid { grid-template-columns: 1fr; }
            .modal-title { font-size: 1.1rem; }
        }

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
            .agenda-hero { padding: 120px 0 40px; }
            .event-card {
                grid-template-columns: 80px 1fr;
                gap: 12px;
                padding: 14px;
            }
            .event-card::before { display: none; }
            .event-tag-wrap { display: none; }
            .stats-row { gap: 20px; }
            .stat-item__value { font-size: 1.4rem; }
            .filter-bar { gap: 4px; }
            .filter-btn { padding: 5px 10px; font-size: 0.7rem; }
            .month-nav { gap: 3px; }
            .month-nav__btn { padding: 5px 8px; font-size: 0.7rem; }
        }
        @media (max-width: 480px) {
            .event-card {
                grid-template-columns: 1fr;
            }
            .event-date {
                display: flex;
                align-items: center;
                gap: 8px;
                text-align: left;
            }
            .event-date__day { font-size: 1.1rem; }
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
    <section class="agenda-hero">
        <div class="container">
            <div class="agenda-hero__badge fade-up">
                <span class="dot"></span>
                AGENDA // JEUX VIDÉO
            </div>
            <h1 class="agenda-hero__title fade-up">
                Agenda <span>Gaming 2026</span>
            </h1>
            <p class="agenda-hero__subtitle fade-up">
                Salons, festivals, compétitions esport, conférences, game jams — tous les événements gaming de l'année.
            </p>
            <div class="stats-row fade-up">
                <div class="stat-item">
                    <div class="stat-item__value"><?= $totalEvents ?></div>
                    <div class="stat-item__label">Événements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value stat-item__value--green"><?= $totalCities ?></div>
                    <div class="stat-item__label">Villes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value"><?= $onlineCount ?></div>
                    <div class="stat-item__label">En ligne</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value" style="color:var(--secondary-bright);"><?= $esportCount ?></div>
                    <div class="stat-item__label">Live Esport</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================
         TOOLBAR (sticky)
         ================================ -->
    <div class="agenda-toolbar">
        <div class="container">
            <div class="toolbar-inner">
                <!-- Search -->
                <div class="search-bar">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="agenda-search" placeholder="Rechercher un événement, une ville...">
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
         MONTH NAVIGATION
         ================================ -->
    <div class="container">
        <div class="month-nav fade-up" id="month-nav">
            <button class="month-nav__btn active" data-month="all">Tout</button>
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <button class="month-nav__btn<?= isset($eventsByMonth[$m])
                ? " has-events"
                : "" ?>"
                    data-month="<?= $m ?>">
                <?= substr($monthsFr[$m], 0, 3) ?>
            </button>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ================================
         EVENTS BY MONTH
         ================================ -->
    <div class="container" id="agenda-content">
        <?php foreach ($eventsByMonth as $monthNum => $monthEvents): ?>
        <div class="month-section fade-up" data-month-section="<?= $monthNum ?>">
            <div class="month-header">
                <span class="month-header__label"><?= $monthsFr[
                    $monthNum
                ] ?> 2026</span>
                <span class="month-header__count"><?= count(
                    $monthEvents,
                ) ?> événement<?= count($monthEvents) > 1 ? "s" : "" ?></span>
                <div class="month-header__line"></div>
            </div>

            <?php foreach ($monthEvents as $event):

                $start = new DateTime($event["start"]);
                $end = !empty($event["end"])
                    ? new DateTime($event["end"])
                    : null;
                $dayStart = $start->format("j");
                $monthStart = $start->format("M");
                $cat = $event["category"];
                $color = $catColors[$cat] ?? "#a68b7b";
                $dateRange = $dayStart;
                if ($end && $end->format("Y-m-d") !== $start->format("Y-m-d")) {
                    $dateRange = $dayStart . " → " . $end->format("j");
                }
                ?>
            <div class="event-card"
                 data-id="<?= $event["id"] ?>"
                 data-category="<?= $cat ?>"
                 data-name="<?= htmlspecialchars($event["name"]) ?>"
                 data-location="<?= htmlspecialchars($event["location"]) ?>">

                <!-- Date -->
                <div class="event-date">
                    <div>
                        <div class="event-date__day"><?= $dayStart ?></div>
                        <div class="event-date__month"><?= $monthStart ?></div>
                        <?php if (
                            $end &&
                            $end->format("Y-m-d") !== $start->format("Y-m-d")
                        ): ?>
                        <div class="event-date__range">→ <?= $end->format(
                            "j M",
                        ) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info -->
                <div class="event-info">
                    <h3 class="event-info__name"><?= htmlspecialchars(
                        $event["name"],
                    ) ?></h3>
                    <?php if (!empty($event["description"])): ?>
                    <p class="event-info__desc"><?= htmlspecialchars(
                        $event["description"],
                    ) ?></p>
                    <?php endif; ?>
                    <div class="event-info__meta">
                        <span class="event-info__location">
                            <span class="material-symbols-outlined">location_on</span>
                            <?= htmlspecialchars($event["location"]) ?>
                            <?php if (!empty($event["department"])): ?>
                            (<?= htmlspecialchars($event["department"]) ?>)
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($event["online"])): ?>
                        <span class="event-info__online">En ligne</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tag -->
                <div class="event-tag-wrap">
                    <span class="event-tag" style="background:<?= $color ?>22; color:<?= $color ?>;">
                        <span class="material-symbols-outlined"><?= $categories[
                            $cat
                        ]["icon"] ?? "event" ?></span>
                        <?= $categories[$cat]["label"] ?? $cat ?>
                    </span>
                </div>
            </div>
            <?php
            endforeach; ?>
        </div>
        <?php endforeach; ?>

        <!-- Empty state for filtered results -->
        <div class="empty-state" id="no-results" style="display:none;">
            <div class="empty-state__icon material-symbols-outlined">search_off</div>
            <div class="empty-state__text">Aucun événement trouvé</div>
            <div class="empty-state__sub">Essayez un autre filtre ou terme de recherche.</div>
        </div>
    </div>

    <!-- ================================
         INDUSTRY STATS
         ================================ -->
    <section class="industry-section" id="chiffres">
        <div class="container">
            <div class="industry-header fade-up">
                <div class="industry-header__badge">
                    <span class="material-symbols-outlined" style="font-size:14px;">analytics</span>
                    DONNÉES // INDUSTRIE
                </div>
                <h2 class="industry-header__title">Chiffres de l'<span>Industrie</span></h2>
                <p class="industry-header__desc">Le marché mondial du jeu vidéo en chiffres — données mises à jour régulièrement.</p>
            </div>

<?php
// ── Charger les stats industrie ──
$statsFile = __DIR__ . "/cache/industry_stats.json";
$stats = null;
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true);
}
if ($stats): ?>
            <!-- ── KPI Row ── -->
            <div class="industry-grid fade-up">
                <?php foreach ($stats["kpi"] ?? [] as $kpi): ?>
                <div class="industry-card">
                    <div class="material-symbols-outlined industry-card__icon<?= ($kpi[
                        "color"
                    ] ??
                        "") ===
                    "green"
                        ? " industry-card__icon--green"
                        : "" ?>"><?= $kpi["icon"] ?? "analytics" ?></div>
                    <div class="industry-card__value"><?= htmlspecialchars(
                        $kpi["value"] ?? "",
                    ) ?><span><?= htmlspecialchars(
    $kpi["unit"] ?? "",
) ?></span></div>
                    <div class="industry-card__label"><?= htmlspecialchars(
                        $kpi["label"] ?? "",
                    ) ?></div>
                    <?php if (!empty($kpi["trend"])): ?>
                    <div class="industry-card__trend industry-card__trend--<?= $kpi[
                        "trend_dir"
                    ] ?? "up" ?>">
                        <span class="material-symbols-outlined"><?= ($kpi[
                            "trend_dir"
                        ] ??
                            "up") ===
                        "down"
                            ? "trending_down"
                            : "trending_up" ?></span> <?= htmlspecialchars(
    $kpi["trend"],
) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Split Charts ── -->
            <div class="industry-split fade-up">
                <?php foreach ($stats["splits"] ?? [] as $split): ?>
                <div class="split-card">
                    <div class="split-card__title">
                        <span class="material-symbols-outlined"><?= $split[
                            "icon"
                        ] ?? "bar_chart" ?></span>
                        <?= htmlspecialchars($split["title"] ?? "") ?>
                    </div>
                    <?php foreach ($split["bars"] ?? [] as $bar): ?>
                    <div class="split-bar">
                        <span class="split-bar__label"><?= htmlspecialchars(
                            $bar["label"] ?? "",
                        ) ?></span>
                        <div class="split-bar__track">
                            <div class="split-bar__fill" data-width="<?= $bar[
                                "pct"
                            ] ?? 0 ?>" style="background:<?= $bar["color"] ??
    "#a68b7b" ?>;"></div>
                        </div>
                        <span class="split-bar__pct"><?= $bar["pct"] ??
                            0 ?> %</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Highlights Row ── -->
            <div class="industry-highlights fade-up">
                <?php foreach ($stats["highlights"] ?? [] as $hl): ?>
                <div class="highlight-card">
                    <div class="highlight-card__icon" style="background:<?= $hl[
                        "icon_bg"
                    ] ?? "rgba(255,130,0,0.12)" ?>;">
                        <span class="material-symbols-outlined" style="color:<?= $hl[
                            "icon_color"
                        ] ?? "var(--primary)" ?>;"><?= $hl["icon"] ??
    "info" ?></span>
                    </div>
                    <div class="highlight-card__text">
                        <h4><?= htmlspecialchars($hl["title"] ?? "") ?></h4>
                        <p><?= htmlspecialchars($hl["text"] ?? "") ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="industry-source">
                Dernière mise à jour : <?= date(
                    "d/m/Y",
                    strtotime($stats["updated"] ?? "now"),
                ) ?> — Sources : <?= htmlspecialchars($stats["source"] ?? "") ?>
            </div>
<?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon material-symbols-outlined">analytics</div>
                <div class="empty-state__text">Données indisponibles</div>
            </div>
<?php endif;
?>
        </div>
    </section>

    <!-- ================================
         FOOTER
         ================================ -->
    <footer class="agenda-footer">
        <div class="container">
            BLACK_PROTOCOL &copy; <?= $year ?> — Agenda Gaming // Données mises à jour régulièrement
        </div>
    </footer>

    <!-- ================================
         EVENT DETAIL MODAL
         ================================ -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal-card" id="modalCard">
            <button class="modal-close material-symbols-outlined" id="modalClose">close</button>
            <div class="modal-header" id="modalHeader">
                <span class="modal-cat" id="modalCat"></span>
                <h2 class="modal-title" id="modalTitle"></h2>
            </div>
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="modal-field">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <div>
                            <div class="modal-field__label">Date</div>
                            <div class="modal-field__value" id="modalDate"></div>
                        </div>
                    </div>
                    <div class="modal-field">
                        <span class="material-symbols-outlined">location_on</span>
                        <div>
                            <div class="modal-field__label">Lieu</div>
                            <div class="modal-field__value" id="modalLocation"></div>
                        </div>
                    </div>
                    <div class="modal-field" id="modalGameField" style="display:none;">
                        <span class="material-symbols-outlined">sports_esports</span>
                        <div>
                            <div class="modal-field__label">Jeu</div>
                            <div class="modal-field__value" id="modalGame"></div>
                        </div>
                    </div>
                    <div class="modal-field" id="modalPrizeField" style="display:none;">
                        <span class="material-symbols-outlined">payments</span>
                        <div>
                            <div class="modal-field__label">Prize Pool</div>
                            <div class="modal-field__value" id="modalPrize"></div>
                        </div>
                    </div>
                    <div class="modal-field" id="modalTeamsField" style="display:none;">
                        <span class="material-symbols-outlined">groups</span>
                        <div>
                            <div class="modal-field__label">Équipes</div>
                            <div class="modal-field__value" id="modalTeams"></div>
                        </div>
                    </div>
                    <div class="modal-field" id="modalTierField" style="display:none;">
                        <span class="material-symbols-outlined">military_tech</span>
                        <div>
                            <div class="modal-field__label">Tier</div>
                            <div class="modal-field__value" id="modalTier"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-desc-section">
                    <div class="modal-field__label" style="margin-bottom:6px;">Description</div>
                    <p class="modal-desc" id="modalDesc"></p>
                </div>
                <div class="modal-actions" id="modalActions"></div>
            </div>
        </div>
    </div>

    <script>
        // ── Event data (from PHP) ──
        const allEvents = <?= json_encode($events, JSON_UNESCAPED_UNICODE) ?>;
        const catColors = <?= json_encode($catColors) ?>;
        const categories = <?= json_encode($categories) ?>;

        // ── Filter state ──
        const cards = document.querySelectorAll('.event-card');
        const monthSections = document.querySelectorAll('.month-section');
        const noResults = document.getElementById('no-results');
        const searchInput = document.getElementById('agenda-search');
        const modal = document.getElementById('eventModal');

        let currentFilter = 'all';
        let currentMonth = 'all';
        let currentSearch = '';

        // ── Apply all filters ──
        function applyFilters() {
            let visibleCount = 0;
            cards.forEach(card => {
                const cat = card.getAttribute('data-category');
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const loc = (card.getAttribute('data-location') || '').toLowerCase();
                const search = currentSearch.toLowerCase();
                const matchCat = currentFilter === 'all' || cat === currentFilter;
                const matchSearch = search === '' || name.includes(search) || loc.includes(search);
                if (matchCat && matchSearch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            monthSections.forEach(section => {
                const month = section.getAttribute('data-month-section');
                const matchMonth = currentMonth === 'all' || month === currentMonth;
                const visibleCards = section.querySelectorAll('.event-card:not(.hidden)');
                if (!matchMonth || visibleCards.length === 0) {
                    section.classList.add('hidden');
                } else {
                    section.classList.remove('hidden');
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

        // ── Month Navigation ──
        document.querySelectorAll('.month-nav__btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.month-nav__btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentMonth = btn.getAttribute('data-month');
                applyFilters();
            });
        });

        // ── Search ──
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.toLowerCase().trim();
            applyFilters();
        });

        // ── Modal ──
        function formatDateFr(dateStr) {
            if (!dateStr) return 'Non défini';
            const d = new Date(dateStr);
            const months = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }

        function openModal(eventId) {
            const ev = allEvents.find(e => String(e.id) === String(eventId));
            if (!ev) return;

            const cat = ev.category || 'salon';
            const color = catColors[cat] || '#a68b7b';
            const catInfo = categories[cat] || { label: cat, icon: 'event' };

            // Header
            document.getElementById('modalCat').innerHTML = '<span class="material-symbols-outlined" style="font-size:13px;">' + catInfo.icon + '</span> ' + catInfo.label;
            document.getElementById('modalCat').style.cssText = 'background:' + color + '22; color:' + color + ';';
            document.getElementById('modalTitle').textContent = ev.name || 'Événement';

            // Date
            let dateText = formatDateFr(ev.start);
            if (ev.end && ev.end !== ev.start) {
                dateText += ' → ' + formatDateFr(ev.end);
            }
            document.getElementById('modalDate').textContent = dateText;

            // Location
            let loc = ev.location || 'Non défini';
            if (ev.department && ev.department !== ev.location && ev.department !== 'En ligne') loc += ' (' + ev.department + ')';
            if (ev.online) loc += ' — En ligne';
            document.getElementById('modalLocation').textContent = loc;

            // Game (PandaScore)
            const gf = document.getElementById('modalGameField');
            if (ev.game) { gf.style.display = 'flex'; document.getElementById('modalGame').textContent = ev.game; }
            else { gf.style.display = 'none'; }

            // Prize
            const pf = document.getElementById('modalPrizeField');
            if (ev.prizepool) { pf.style.display = 'flex'; document.getElementById('modalPrize').textContent = ev.prizepool; }
            else { pf.style.display = 'none'; }

            // Teams
            const tf = document.getElementById('modalTeamsField');
            if (ev.teams && ev.teams > 0) { tf.style.display = 'flex'; document.getElementById('modalTeams').textContent = ev.teams + ' équipes'; }
            else { tf.style.display = 'none'; }

            // Tier
            const tif = document.getElementById('modalTierField');
            if (ev.tier) { tif.style.display = 'flex'; document.getElementById('modalTier').textContent = ev.tier.toUpperCase() + ' Tier'; }
            else { tif.style.display = 'none'; }

            // Description
            document.getElementById('modalDesc').textContent = ev.description || 'Aucune description disponible.';

            // Actions
            let actions = '<a href="?refresh=1" class="modal-action-btn modal-action-btn--outline"><span class="material-symbols-outlined">sync</span> Rafraîchir</a>';
            if (ev.url) {
                actions += '<a href="' + ev.url + '" target="_blank" class="modal-action-btn modal-action-btn--primary"><span class="material-symbols-outlined">open_in_new</span> Site officiel</a>';
            }
            document.getElementById('modalActions').innerHTML = actions;

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close on overlay click
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        document.getElementById('modalClose').addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

        // Card click → open modal
        cards.forEach(card => {
            card.addEventListener('click', () => {
                openModal(card.getAttribute('data-id'));
            });
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

        // ── Animate split bars on scroll ──
        const splitFills = document.querySelectorAll('.split-bar__fill');
        const splitObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const fill = entry.target;
                    const w = fill.getAttribute('data-width');
                    fill.style.width = w + '%';
                    splitObserver.unobserve(fill);
                }
            });
        }, { threshold: 0.3 });
        splitFills.forEach(f => splitObserver.observe(f));

        // ── Inject category colors ──
        <?php foreach ($catColors as $cat => $color): ?>
        document.head.insertAdjacentHTML('beforeend', '<style>.event-card[data-category="<?= $cat ?>"]::before { background: <?= $color ?>; }</style>');
        <?php endforeach; ?>
    </script>
</body>
</html>
