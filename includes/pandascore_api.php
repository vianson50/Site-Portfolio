<?php
/**
 * PandaScore API Proxy — Gaming Calendar
 * Authentification Bearer + fallback token query param
 * Cache 5 min dans ../cache/pandascore_cache.json
 */

// ── Config ──
$API_KEY = "zDJK_rpaAxN-2qEzji6mNfCcXA-UdTCtLQLuJxfCp2zFNTpb6UI";
$BASE_URL = "https://api.pandascore.co";
$CACHE = __DIR__ . "/../cache/pandascore_cache.json";
$TTL = 300;

// ── CORS ──
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// ── Mappings ──
$CAT_MAP = [
    // MOBA
    "league-of-legends" => "moba",
    "lol" => "moba",
    "dota-2" => "moba",
    // FPS
    "cs-go" => "fps",
    "counter-strike" => "fps",
    "cs2" => "fps",
    "valorant" => "fps",
    "call-of-duty" => "fps",
    "cod" => "fps",
    "overwatch" => "fps",
    "overwatch-2" => "fps",
    // SPORT
    "rocket-league" => "sport",
    "rl" => "sport",
    "fifa" => "sport",
    "ea-sports-fc" => "sport",
    "fc-25" => "sport",
    "nba-2k" => "sport",
    // COMBAT
    "street-fighter" => "combat",
    "street-fighter-6" => "combat",
    "sf6" => "combat",
    "tekken" => "combat",
    "tekken-8" => "combat",
    "super-smash-bros" => "combat",
    "smash-bros" => "combat",
    // MOBILE
    "mobile-legends" => "mobile",
    "mlbb" => "mobile",
    "brawl-stars" => "mobile",
    "clash-royale" => "mobile",
    "free-fire" => "mobile",
    "pubg-mobile" => "mobile",
    "arena-of-valor" => "mobile",
];

$GAME_NAMES = [
    "league-of-legends" => "League of Legends",
    "lol" => "League of Legends",
    "dota-2" => "Dota 2",
    "cs-go" => "Counter-Strike 2",
    "counter-strike" => "Counter-Strike 2",
    "cs2" => "Counter-Strike 2",
    "valorant" => "Valorant",
    "call-of-duty" => "Call of Duty",
    "rocket-league" => "Rocket League",
    "rl" => "Rocket League",
    "fifa" => "FC 25",
    "ea-sports-fc" => "FC 25",
    "fc-25" => "FC 25",
    "street-fighter" => "Street Fighter 6",
    "street-fighter-6" => "Street Fighter 6",
    "tekken" => "Tekken 8",
    "tekken-8" => "Tekken 8",
    "mobile-legends" => "Mobile Legends",
    "mlbb" => "Mobile Legends",
    "brawl-stars" => "Brawl Stars",
    "clash-royale" => "Clash Royale",
    "free-fire" => "Free Fire",
    "pubg-mobile" => "PUBG Mobile",
];

// ── Helpers ──
function bp_esport_status($begin, $end)
{
    $now = time();
    $s = $begin ? strtotime($begin) : null;
    $e = $end ? strtotime($end) : null;
    if ($e !== null && $e < $now) {
        return "completed";
    }
    if ($s !== null && $s <= $now && ($e === null || $e >= $now)) {
        return "live";
    }
    return "upcoming";
}

function bp_esport_tier($t)
{
    $t = strtolower(trim($t ?: ""));
    if ($t === "s") {
        return "S-Tier";
    }
    if ($t === "a") {
        return "A-Tier";
    }
    if ($t === "b" || $t === "c") {
        return "B-Tier";
    }
    if ($t === "d") {
        return "C-Tier";
    }
    return null;
}

function bp_esport_format($type)
{
    $t = strtolower(trim($type ?: ""));
    return $t === "offline" || $t === "online/offline" ? "LAN" : "Online";
}

function bp_esport_stream_platform($url)
{
    if (!$url) {
        return null;
    }
    $u = strtolower($url);
    if (strpos($u, "twitch") !== false) {
        return "Twitch";
    }
    if (strpos($u, "youtube") !== false || strpos($u, "youtu.be") !== false) {
        return "YouTube";
    }
    if (strpos($u, "lolesports") !== false) {
        return "LoL Esports";
    }
    if (strpos($u, "facebook") !== false || strpos($u, "fb.gg") !== false) {
        return "Facebook";
    }
    return "Stream";
}

function bp_esport_extract_stream($t)
{
    $matches = isset($t["matches"]) ? $t["matches"] : [];
    foreach ($matches as $m) {
        $streams = isset($m["streams_list"]) ? $m["streams_list"] : [];
        foreach ($streams as $s) {
            if (!empty($s["raw_url"])) {
                return $s["raw_url"];
            }
        }
    }
    return null;
}

function bp_esport_location($t)
{
    if (!empty($t["country"])) {
        return $t["country"];
    }
    if (!empty($t["region"])) {
        return $t["region"];
    }
    if (strtolower($t["type"] ?? "") === "online") {
        return "En ligne";
    }
    return null;
}

function bp_esport_transform($t, $catMap, $names)
{
    $slug = $t["videogame"]["slug"] ?? "";
    $cat = $catMap[$slug] ?? null;
    $game = $names[$slug] ?? ($t["videogame"]["name"] ?? "Unknown");
    $url = bp_esport_extract_stream($t);

    return [
        "name" => $t["name"] ?? "Tournament",
        "organizer" => $t["league"]["name"] ?? null,
        "game" => $game,
        "date" => $t["begin_at"] ?? null,
        "endDate" => $t["end_at"] ?? null,
        "format" => bp_esport_format($t["type"] ?? null),
        "prize" => $t["prizepool"] ?? null,
        "location" => bp_esport_location($t),
        "tier" => bp_esport_tier($t["tier"] ?? null),
        "status" => bp_esport_status(
            $t["begin_at"] ?? null,
            $t["end_at"] ?? null,
        ),
        "teams" => is_array($t["teams"] ?? null) ? count($t["teams"]) : 0,
        "logo" => $t["league"]["image_url"] ?? null,
        "stream" => $url,
        "streamPlatform" => bp_esport_stream_platform($url),
        "_cat" => $cat,
    ];
}

// ── HTTP Fetch via cURL ──
function bp_esport_fetch($endpoint, $key)
{
    $url = "https://api.pandascore.co" . $endpoint;
    $sep = strpos($endpoint, "?") !== false ? "&" : "?";
    $fullUrl = $url . $sep . "token=" . urlencode($key);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Authorization: Bearer " . $key,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    if ($resp === false || $err) {
        return ["error" => "cURL: " . $err];
    }
    if ($code >= 400) {
        return ["error" => "HTTP $code — " . mb_substr($resp, 0, 200)];
    }

    $data = json_decode($resp, true);
    if ($data === null) {
        return ["error" => "JSON decode error"];
    }
    return ["data" => $data];
}

// ── Cache ──
function bp_esport_read_cache($file, $ttl)
{
    if (!file_exists($file)) {
        return null;
    }
    $c = json_decode(file_get_contents($file), true);
    if (!$c || !isset($c["ts"]) || time() - $c["ts"] > $ttl) {
        return null;
    }
    return $c["d"];
}

function bp_esport_write_cache($file, $data)
{
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $file,
        json_encode(
            ["ts" => time(), "d" => $data],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        ),
    );
}

// ── Main ──
function bp_esport_get_tournaments($key, $cache, $ttl, $catMap, $names)
{
    $cached = bp_esport_read_cache($cache, $ttl);
    if ($cached !== null) {
        return $cached;
    }

    $r1 = bp_esport_fetch(
        "/tournaments/running?sort=begin_at&page_size=100",
        $key,
    );
    $r2 = bp_esport_fetch(
        "/tournaments/upcoming?sort=begin_at&page_size=100",
        $key,
    );

    if (isset($r1["error"]) && isset($r2["error"])) {
        return [
            "success" => false,
            "error" => "API indisponible",
            "details" => [$r1["error"], $r2["error"]],
        ];
    }

    $all = [];
    if (isset($r1["data"]) && is_array($r1["data"])) {
        $all = array_merge($all, $r1["data"]);
    }
    if (isset($r2["data"]) && is_array($r2["data"])) {
        $all = array_merge($all, $r2["data"]);
    }

    $out = [
        "moba" => [],
        "fps" => [],
        "sport" => [],
        "combat" => [],
        "mobile" => [],
    ];

    foreach ($all as $t) {
        $row = bp_esport_transform($t, $catMap, $names);
        $cat = $row["_cat"];
        unset($row["_cat"]);
        if ($cat !== null) {
            $out[$cat][] = $row;
        }
    }

    foreach ($out as &$list) {
        usort($list, function ($a, $b) {
            $o = ["live" => 0, "upcoming" => 1, "completed" => 2];
            return ($o[$a["status"]] ?? 9) - ($o[$b["status"]] ?? 9);
        });
    }
    unset($list);

    bp_esport_write_cache($cache, $out);
    return $out;
}

// ── Route ──
$act = isset($_GET["action"]) ? $_GET["action"] : "";

if ($act === "tournaments" || $act === "refresh") {
    if ($act === "refresh" && file_exists($CACHE)) {
        unlink($CACHE);
    }
    $res = bp_esport_get_tournaments(
        $API_KEY,
        $CACHE,
        $TTL,
        $CAT_MAP,
        $GAME_NAMES,
    );
    if (isset($res["success"]) && $res["success"] === false) {
        http_response_code(502);
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} elseif ($act === "clear_cache") {
    if (file_exists($CACHE)) {
        unlink($CACHE);
    }
    echo json_encode(["success" => true, "message" => "Cache vide"]);
} else {
    http_response_code(400);
    echo json_encode([
        "error" => "Action invalide",
        "message" => "Actions: tournaments, refresh, clear_cache",
    ]);
}
