<?php
/**
 * start.gg GraphQL API Proxy — Esports Tournament Data
 * Bearer auth + GraphQL POST queries
 * Cache 5 min dans ../cache/startgg_cache.json
 */

// ── Config ──
$API_KEY = "220712a36ea7c56202858a0ef929f70d";
$ENDPOINT = "https://api.start.gg/gql/alpha";
$CACHE = __DIR__ . "/../cache/startgg_cache.json";
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

// ── Helpers ──

/**
 * Determine tournament status based on start/end timestamps
 */
function sgg_status($startAt, $endAt)
{
    $now = time();
    $s = $startAt ? (int) $startAt : null;
    $e = $endAt ? (int) $endAt : null;

    if ($e !== null && $e < $now) {
        return "completed";
    }
    if ($s !== null && $s <= $now && ($e === null || $e >= $now)) {
        return "live";
    }
    return "upcoming";
}

/**
 * Convert Unix epoch to Y-m-d H:i:s
 */
function sgg_fmt_date($ts)
{
    if (!$ts) {
        return null;
    }
    return date("Y-m-d H:i:s", (int) $ts);
}

/**
 * Transform a raw tournament node from GraphQL into our standard format
 */
function sgg_transform_tournament($t)
{
    $events = [];
    if (isset($t["events"]) && is_array($t["events"])) {
        foreach ($t["events"] as $ev) {
            $gameName = $ev["videogame"]["name"] ?? null;
            $events[] = [
                "id" => $ev["id"] ?? null,
                "name" => $ev["name"] ?? null,
                "numEntrants" => $ev["numEntrants"] ?? null,
                "game" => $gameName,
            ];
        }
    }

    return [
        "id" => "startgg_" . $t["id"],
        "name" => $t["name"] ?? "Tournament",
        "slug" => $t["slug"] ?? null,
        "source" => "start.gg",
        "start_at" => sgg_fmt_date($t["startAt"] ?? null),
        "end_at" => sgg_fmt_date($t["endAt"] ?? null),
        "city" => $t["city"] ?? null,
        "country" => $t["countryCode"] ?? null,
        "state" => $t["addrState"] ?? null,
        "attendees" => $t["numAttendees"] ?? null,
        "events" => $events,
        "url" => isset($t["slug"]) ? "https://start.gg/" . $t["slug"] : null,
        "status" => sgg_status($t["startAt"] ?? null, $t["endAt"] ?? null),
        "_source" => "start.gg",
    ];
}

/**
 * Transform a results tournament (with standings/winner info)
 */
function sgg_transform_result($t)
{
    $row = sgg_transform_tournament($t);

    $winners = [];
    $eventStandings = [];
    if (isset($t["events"]) && is_array($t["events"])) {
        foreach ($t["events"] as $ev) {
            $standings = $ev["standings"]["nodes"] ?? [];
            $evStandings = [];
            foreach ($standings as $s) {
                $placement = $s["placement"] ?? null;
                $entrantName = $s["entrant"]["name"] ?? null;
                $entrantId = $s["entrant"]["id"] ?? null;
                if ($placement === 1) {
                    $winners[] = [
                        "event" => $ev["name"] ?? null,
                        "winner" => $entrantName,
                        "game" => $ev["videogame"]["name"] ?? null,
                    ];
                }
                $evStandings[] = [
                    "placement" => $placement,
                    "name" => $entrantName,
                    "id" => $entrantId,
                ];
            }
            if (!empty($evStandings)) {
                $eventStandings[] = [
                    "eventId" => $ev["id"] ?? null,
                    "eventName" => $ev["name"] ?? null,
                    "game" => $ev["videogame"]["name"] ?? null,
                    "rankings" => $evStandings,
                ];
            }
        }
    }
    $row["winners"] = $winners;
    $row["standings"] = $eventStandings;
    return $row;
}

// ── GraphQL Fetch via cURL ──

/**
 * Send a GraphQL query to start.gg API
 */
function sgg_query($query, $endpoint, $key, $variables = [])
{
    $body = json_encode(["query" => $query, "variables" => $variables]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
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
    if (isset($data["errors"])) {
        $msgs = array_map(function ($e) {
            return $e["message"] ?? "Unknown GraphQL error";
        }, $data["errors"]);
        return ["error" => "GraphQL: " . implode("; ", $msgs)];
    }

    return ["data" => $data["data"] ?? $data];
}

// ── Cache (same pattern as pandascore) ──

function sgg_read_cache($file, $ttl)
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

function sgg_write_cache($file, $data)
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

// ── Action Handlers ──

/**
 * action=tournaments — fetch general tournaments
 */
function sgg_action_tournaments($endpoint, $key)
{
    $query = <<<'GQL'
    query($perPage: Int!, $page: Int) {
      tournaments(query: { perPage: $perPage, page: $page, sortBy: "startAt asc" }) {
        nodes {
          id name slug startAt endAt
          city countryCode addrState
          numAttendees
          events { id name numEntrants videogame { id name slug } }
        }
      }
    }
    GQL;

    $r = sgg_query($query, $endpoint, $key, ["perPage" => 80, "page" => 1]);

    if (isset($r["error"])) {
        return ["success" => false, "error" => $r["error"]];
    }

    $nodes = $r["data"]["tournaments"]["nodes"] ?? [];
    $out = [];
    foreach ($nodes as $t) {
        $out[] = sgg_transform_tournament($t);
    }

    return ["success" => true, "count" => count($out), "tournaments" => $out];
}

/**
 * action=upcoming — fetch upcoming tournaments only
 */
function sgg_action_upcoming($endpoint, $key)
{
    $query = <<<'GQL'
    query {
      tournaments(query: { perPage: 40, page: 1, sortBy: "startAt asc", filter: { upcoming: true } }) {
        nodes {
          id name slug startAt endAt
          city countryCode addrState numAttendees
          events { id name numEntrants videogame { id name slug } }
        }
      }
    }
    GQL;

    $r = sgg_query($query, $endpoint, $key);

    if (isset($r["error"])) {
        return ["success" => false, "error" => $r["error"]];
    }

    $nodes = $r["data"]["tournaments"]["nodes"] ?? [];
    $out = [];
    foreach ($nodes as $t) {
        $out[] = sgg_transform_tournament($t);
    }

    return ["success" => true, "count" => count($out), "upcoming" => $out];
}

/**
 * action=results — fetch past tournaments with winners
 */
function sgg_action_results($endpoint, $key)
{
    $query = <<<'GQL'
    query {
      tournaments(query: { perPage: 30, page: 1, sortBy: "startAt desc", filter: { past: true } }) {
        nodes {
          id name slug startAt endAt city countryCode numAttendees
          events {
            id name numEntrants videogame { id name slug }
            standings(query: { perPage: 8 }) {
              nodes { placement entrant { id name } }
            }
          }
        }
      }
    }
    GQL;

    $r = sgg_query($query, $endpoint, $key);

    if (isset($r["error"])) {
        return ["success" => false, "error" => $r["error"]];
    }

    $nodes = $r["data"]["tournaments"]["nodes"] ?? [];
    $out = [];
    foreach ($nodes as $t) {
        $out[] = sgg_transform_result($t);
    }

    return ["success" => true, "count" => count($out), "results" => $out];
}

/**
 * action=brawlstars — Brawl Stars tournaments (videogame ID 24)
 */
function sgg_action_brawlstars($endpoint, $key)
{
    $query = <<<'GQL'
    query {
      tournaments(query: {
        perPage: 30, page: 1, sortBy: "startAt asc",
        filter: { videogameIds: [24], upcoming: true }
      }) {
        nodes {
          id name slug startAt endAt city countryCode numAttendees
          events { id name numEntrants slug }
        }
      }
    }
    GQL;

    $r = sgg_query($query, $endpoint, $key);

    if (isset($r["error"])) {
        return ["success" => false, "error" => $r["error"]];
    }

    $nodes = $r["data"]["tournaments"]["nodes"] ?? [];
    $out = [];
    foreach ($nodes as $t) {
        $out[] = sgg_transform_tournament($t);
    }

    return ["success" => true, "count" => count($out), "brawlstars" => $out];
}

/**
 * action=all (default) — combine upcoming + past + brawlstars
 */
function sgg_action_all($endpoint, $key, $cache, $ttl)
{
    $cached = sgg_read_cache($cache, $ttl);
    if ($cached !== null) {
        return $cached;
    }

    $upcoming = sgg_action_upcoming($endpoint, $key);
    $past = sgg_action_results($endpoint, $key);
    $brawlstars = sgg_action_brawlstars($endpoint, $key);

    // Check if all three failed
    if (
        isset($upcoming["success"]) &&
        $upcoming["success"] === false &&
        isset($past["success"]) &&
        $past["success"] === false &&
        isset($brawlstars["success"]) &&
        $brawlstars["success"] === false
    ) {
        return [
            "success" => false,
            "error" => "API indisponible",
            "details" => [
                $upcoming["error"] ?? null,
                $past["error"] ?? null,
                $brawlstars["error"] ?? null,
            ],
        ];
    }

    $out = [
        "success" => true,
        "upcoming" => $upcoming["upcoming"] ?? [],
        "past" => $past["results"] ?? [],
        "brawlstars" => $brawlstars["brawlstars"] ?? [],
        "counts" => [
            "upcoming" => count($upcoming["upcoming"] ?? []),
            "past" => count($past["results"] ?? []),
            "brawlstars" => count($brawlstars["brawlstars"] ?? []),
        ],
    ];

    sgg_write_cache($cache, $out);
    return $out;
}

/**
 * action=standings — fetch top 8 standings for a specific event or tournament
 * Params: event_id (int) OR slug (string)
 */
function sgg_action_standings(
    $endpoint,
    $key,
    $eventId = null,
    $tournamentSlug = null,
) {
    // If we have a tournament slug, fetch its events + standings
    if ($tournamentSlug && !$eventId) {
        $query = <<<'GQL'
        query($slug: String!) {
          tournament(slug: $slug) {
            id name slug
            events {
              id name
              numEntrants
              videogame { id name slug }
              standings(query: { perPage: 8, page: 1 }) {
                nodes {
                  placement
                  entrant { id name }
                }
              }
            }
          }
        }
        GQL;

        $r = sgg_query($query, $endpoint, $key, ["slug" => $tournamentSlug]);

        if (isset($r["error"])) {
            return ["success" => false, "error" => $r["error"]];
        }

        $t = $r["data"]["tournament"] ?? null;
        if (!$t) {
            return ["success" => false, "error" => "Tournoi introuvable"];
        }

        $events = [];
        foreach ($t["events"] ?? [] as $ev) {
            $standings = [];
            foreach ($ev["standings"]["nodes"] ?? [] as $s) {
                $standings[] = [
                    "placement" => $s["placement"] ?? null,
                    "name" => $s["entrant"]["name"] ?? "Unknown",
                ];
            }
            $events[] = [
                "id" => $ev["id"],
                "name" => $ev["name"],
                "game" => $ev["videogame"]["name"] ?? null,
                "numEntrants" => $ev["numEntrants"] ?? 0,
                "standings" => $standings,
            ];
        }

        return [
            "success" => true,
            "tournament" => [
                "id" => $t["id"],
                "name" => $t["name"],
                "slug" => $t["slug"],
            ],
            "events" => $events,
        ];
    }

    // If we have an event ID, fetch standings for that event
    if ($eventId) {
        $query = <<<'GQL'
        query($eventId: ID!) {
          event(id: $eventId) {
            id name
            numEntrants
            videogame { id name slug }
            standings(query: { perPage: 8, page: 1 }) {
              nodes {
                placement
                entrant { id name }
              }
            }
            tournament {
              id name slug
            }
          }
        }
        GQL;

        $r = sgg_query($query, $endpoint, $key, ["eventId" => $eventId]);

        if (isset($r["error"])) {
            return ["success" => false, "error" => $r["error"]];
        }

        $ev = $r["data"]["event"] ?? null;
        if (!$ev) {
            return ["success" => false, "error" => "Event introuvable"];
        }

        $standings = [];
        foreach ($ev["standings"]["nodes"] ?? [] as $s) {
            $standings[] = [
                "placement" => $s["placement"] ?? null,
                "name" => $s["entrant"]["name"] ?? "Unknown",
            ];
        }

        return [
            "success" => true,
            "tournament" => $ev["tournament"] ?? null,
            "events" => [
                [
                    "id" => $ev["id"],
                    "name" => $ev["name"],
                    "game" => $ev["videogame"]["name"] ?? null,
                    "numEntrants" => $ev["numEntrants"] ?? 0,
                    "standings" => $standings,
                ],
            ],
        ];
    }

    return [
        "success" => false,
        "error" => "Paramètres manquants: event_id ou slug requis",
    ];
}

// ── Route ──
$act = isset($_GET["action"]) ? $_GET["action"] : "all";

switch ($act) {
    case "tournaments":
        $res = sgg_action_tournaments($ENDPOINT, $API_KEY);
        break;

    case "upcoming":
        $res = sgg_action_upcoming($ENDPOINT, $API_KEY);
        break;

    case "results":
        $res = sgg_action_results($ENDPOINT, $API_KEY);
        break;

    case "brawlstars":
        $res = sgg_action_brawlstars($ENDPOINT, $API_KEY);
        break;

    case "standings":
        $eventId = isset($_GET["event_id"]) ? (int) $_GET["event_id"] : null;
        $tournamentSlug = isset($_GET["slug"]) ? $_GET["slug"] : null;
        $res = sgg_action_standings(
            $ENDPOINT,
            $API_KEY,
            $eventId,
            $tournamentSlug,
        );
        break;

    case "refresh":
        if (file_exists($CACHE)) {
            unlink($CACHE);
        }
        $res = sgg_action_all($ENDPOINT, $API_KEY, $CACHE, $TTL);
        break;

    case "clear_cache":
        if (file_exists($CACHE)) {
            unlink($CACHE);
        }
        $res = ["success" => true, "message" => "Cache start.gg vidé"];
        break;

    case "all":
    default:
        $res = sgg_action_all($ENDPOINT, $API_KEY, $CACHE, $TTL);
        break;
}

// ── Output ──
if (isset($res["success"]) && $res["success"] === false) {
    http_response_code(502);
}
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
