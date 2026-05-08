<?php
/**
 * RSS Proxy — Fetch, cache & serve RSS feeds as JSON
 * Used by the Blog section to display crypto news
 */

error_reporting(0);

$FEED_URL_CRYPTO = "https://cdn.feedcontrol.net/14871/27292-Rc9DZPFhetRT7.xml";
$FEED_URL_GAMING = "https://cdn.feedcontrol.net/14871/27293-1yMCzlIjBW4mB.xml";
$FEED_URL_JV = "https://cdn.feedcontrol.net/14871/27294-pyzyhF8x9pQUx.xml";
$FEED_URL_COINS = "https://cdn.feedcontrol.net/14871/27295-hO2iXvGzPMuN9.xml";
$CACHE = __DIR__ . "/../cache/rss_cache.json";
$CACHE_TICKER = __DIR__ . "/../cache/rss_ticker_cache.json";
$CACHE_COINS = __DIR__ . "/../cache/rss_coins_cache.json";
$TTL = 900; // 15 min

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

/* ── Cache read ── */
function rss_read_cache($file, $ttl)
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

/* ── Cache write ── */
function rss_write_cache($file, $data)
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

/* ── Parse RSS XML → array ── */
function rss_parse($xml, $maxItems = 20)
{
    libxml_use_internal_errors(true);
    $doc = simplexml_load_string($xml);
    if (!$doc) {
        return null;
    }

    // Handle both RSS and Atom feeds
    $channel = null;
    $items = [];

    // RSS 2.0
    if (isset($doc->channel)) {
        $ch = $doc->channel;
        $channel = [
            "title" => (string) ($ch->title ?? ""),
            "link" => (string) ($ch->link ?? ""),
            "description" => (string) ($ch->description ?? ""),
        ];
        $cnt = 0;
        foreach ($ch->item as $item) {
            if ($cnt >= $maxItems) {
                break;
            }
            $cnt++;

            // Extract image
            $img = null;
            if ($item->enclosure && $item->enclosure["url"]) {
                $img = (string) $item->enclosure["url"];
            }
            // Fallback: search <img> in content
            if (!$img) {
                $content =
                    (string) ($item->children("content", true)->encoded ??
                        ($item->description ?? ""));
                if (
                    preg_match(
                        '/<img[^>]+src=["\']([^"\']+)["\']/i',
                        $content,
                        $m,
                    )
                ) {
                    $img = $m[1];
                }
            }
            // Decode HTML entities in image URL
            if ($img) {
                $img = html_entity_decode(
                    $img,
                    ENT_QUOTES | ENT_HTML5,
                    "UTF-8",
                );
            }

            // Clean description — strip HTML, limit length
            $desc = (string) ($item->description ?? "");
            $desc = strip_tags(
                html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, "UTF-8"),
            );
            $desc = preg_replace("/\s+/", " ", trim($desc));
            if (mb_strlen($desc) > 220) {
                $desc = mb_substr($desc, 0, 217) . "...";
            }

            $pubDate = (string) ($item->pubDate ?? "");

            $items[] = [
                "title" => (string) ($item->title ?? ""),
                "link" => (string) ($item->link ?? ""),
                "desc" => $desc,
                "date" => $pubDate,
                "ts" => $pubDate ? strtotime($pubDate) : null,
                "image" => $img,
                "author" =>
                    (string) ($item->children("dc", true)->creator ?? ""),
            ];
        }
    }

    return [
        "channel" => $channel,
        "items" => $items,
        "count" => count($items),
    ];
}

/* ── Main ── */
/* ── Fetch & parse a feed URL ── */
function rss_fetch_and_parse($url, $cacheFile, $ttl, $maxItems = 20)
{
    $cached = rss_read_cache($cacheFile, $ttl);
    if ($cached !== null) {
        return $cached;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => "BLACK_PROTOCOL/1.0 RSS Reader",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);

    $xml = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($xml === false || $err) {
        return ["success" => false, "error" => "cURL: $err"];
    }
    if ($code >= 400) {
        return ["success" => false, "error" => "HTTP $code"];
    }

    $parsed = rss_parse($xml, $maxItems);
    if (!$parsed) {
        return ["success" => false, "error" => "XML parse error"];
    }

    $result = [
        "success" => true,
        "source" => $parsed["channel"]["title"] ?? "RSS Feed",
        "count" => $parsed["count"],
        "items" => $parsed["items"],
    ];

    rss_write_cache($cacheFile, $result);
    return $result;
}

/* ── Parse CoinGecko RSS — extract coin stats before truncation ── */
function rss_parse_coins($xml, $maxItems = 20)
{
    libxml_use_internal_errors(true);
    $doc = simplexml_load_string($xml);
    if (!$doc || !isset($doc->channel)) {
        return null;
    }

    $ch = $doc->channel;
    $channel = [
        "title" => (string) ($ch->title ?? ""),
        "link" => (string) ($ch->link ?? ""),
    ];

    // Known slug → symbol map
    $symMap = [
        "bitcoin" => "BTC",
        "ethereum" => "ETH",
        "tether" => "USDT",
        "bnb" => "BNB",
        "xrp" => "XRP",
        "solana" => "SOL",
        "usd-coin" => "USDC",
        "cardano" => "ADA",
        "dogecoin" => "DOGE",
        "tron" => "TRX",
        "avalanche" => "AVAX",
        "polkadot" => "DOT",
        "chainlink" => "LINK",
        "polygon" => "POL",
        "litecoin" => "LTC",
    ];

    $items = [];
    $cnt = 0;
    foreach ($ch->item as $item) {
        if ($cnt >= $maxItems) {
            break;
        }
        $cnt++;

        $title = (string) ($item->title ?? "");
        $link = (string) ($item->link ?? "#");

        // Full HTML content (content:encoded or description)
        $rawHtml =
            (string) ($item->children("content", true)->encoded ??
                ($item->description ?? ""));
        $rawText = strip_tags(
            html_entity_decode($rawHtml, ENT_QUOTES | ENT_HTML5, "UTF-8"),
        );
        $rawText = preg_replace("/\s+/", " ", trim($rawText));

        // Slug from URL
        $slug = preg_replace("/.*\/coins\//", "", rtrim($link, "/"));
        $symbol = $symMap[$slug] ?? strtoupper(substr($slug, 0, 5));

        // Extract coin image from content
        $img = null;
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $rawHtml, $m)) {
            $img = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8");
        }

        // Extract rank (e.g. "classée 1 sur CoinGecko")
        $rank = "";
        if (
            preg_match('/class[\xe9\xe8]e (\d+) sur CoinGecko/i', $rawText, $m)
        ) {
            $rank = (int) $m[1];
        }

        // Extract market cap
        $mcap = "";
        if (
            preg_match(
                '/capitalisation boursi[\xe8\xe9]re.*?\$([\d\s,.]+)/i',
                $rawText,
                $m,
            )
        ) {
            $mcap = "$" . trim($m[1]);
        }

        // Extract 24h volume
        $vol = "";
        if (
            preg_match(
                '/volume d\'[\xe9]change.*?\$([\d\s,.]+)/i',
                $rawText,
                $m,
            )
        ) {
            $vol = "$" . trim($m[1]);
        }

        // Extract 7d change
        $change = "";
        if (
            preg_match(
                "/changement (hausse|baisse) de ([\d,.]+)\s*%.*?7 derniers jours/is",
                $rawText,
                $m,
            )
        ) {
            $dir = $m[1] === "hausse" ? "+" : "-";
            $change = $dir . $m[2] . "%";
        }

        // Extract ATH
        $ath = "";
        if (
            preg_match(
                '/plus [\xe9\xe8]lev[\xe9\xe8].*?\$([\d,.\s]+)/i',
                $rawText,
                $m,
            )
        ) {
            $ath = "$" . trim($m[1]);
        }

        // Extract % below ATH (e.g. "-36,30 % en dessous")
        $vsAth = "";
        if (
            preg_match(
                '/n[\xe9\xe8]gocie actuellement (-?[\d,.]+)\s*% en dessous/i',
                $rawText,
                $m,
            )
        ) {
            $vsAth = "-" . trim($m[1], "-") . "%";
        }

        // Extract circulating supply (e.g. "20 million jetons")
        $supply = "";
        if (
            preg_match(
                "/offre en circulation.*?(\d+[\s.]?\d*\s*(?:milliard|million|millier)?\s*jetons)/i",
                $rawText,
                $m,
            )
        ) {
            $supply = trim($m[1]);
        }

        // Extract max supply from FDV line
        $maxSupply = "";
        if (
            preg_match(
                "/nombre maximal de jetons\s+(\d+[\s.]?\d*\s*(?:milliard|million)?)/i",
                $rawText,
                $m,
            )
        ) {
            $maxSupply = trim($m[1]);
        }

        // Extract top exchange
        $exchange = "";
        if (
            preg_match(
                '/l\'[\xe9]change le plus populaire.*?<a[^>]*>([^<]+)<\/a>/i',
                $rawHtml,
                $m,
            )
        ) {
            $exchange = trim(strip_tags($m[1]));
        }

        // Extract active pair
        $pair = "";
        if (
            preg_match(
                "/paire la plus active\s+([A-Z]+\/[A-Z]+)/i",
                $rawText,
                $m,
            )
        ) {
            $pair = $m[1];
        }

        // Short description — first sentence only
        $shortDesc = "";
        if (preg_match("/^([^.!?]+[.!?])/", $rawText, $m)) {
            $shortDesc = trim($m[1]);
            if (mb_strlen($shortDesc) > 160) {
                $shortDesc = mb_substr($shortDesc, 0, 157) . "...";
            }
        }

        // 24h volume change direction
        $volDir = "";
        if (
            preg_match(
                '/volume.*?diff[\xe9]rence de ([\d,.]+)\s*%\s*(hausse|baisse)/i',
                $rawText,
                $m,
            )
        ) {
            $volDir = ($m[2] === "hausse" ? "+" : "-") . $m[1] . "%";
        }

        $items[] = [
            "title" => $title,
            "link" => $link,
            "desc" => $shortDesc,
            "image" => $img,
            "symbol" => $symbol,
            "slug" => $slug,
            "rank" => $rank,
            "mcap" => $mcap,
            "volume" => $vol,
            "volDir" => $volDir,
            "change" => $change,
            "ath" => $ath,
            "vsAth" => $vsAth,
            "supply" => $supply,
            "maxSupply" => $maxSupply,
            "exchange" => $exchange,
            "pair" => $pair,
        ];
    }

    return ["channel" => $channel, "items" => $items, "count" => count($items)];
}

/* ── Fetch & parse CoinGecko feed ── */
function rss_fetch_coins($url, $cacheFile, $ttl, $maxItems = 20)
{
    $cached = rss_read_cache($cacheFile, $ttl);
    if ($cached !== null) {
        return $cached;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => "BLACK_PROTOCOL/1.0 RSS Reader",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);
    $xml = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($xml === false || $err) {
        return ["success" => false, "error" => "cURL: $err"];
    }
    if ($code >= 400) {
        return ["success" => false, "error" => "HTTP $code"];
    }

    $parsed = rss_parse_coins($xml, $maxItems);
    if (!$parsed) {
        return ["success" => false, "error" => "XML parse error"];
    }

    $result = [
        "success" => true,
        "source" => $parsed["channel"]["title"] ?? "CoinGecko",
        "count" => $parsed["count"],
        "items" => $parsed["items"],
    ];
    rss_write_cache($cacheFile, $result);
    return $result;
}

/* ── Route ── */
$action = $_GET["action"] ?? "feed";

switch ($action) {
    case "ticker":
    case "ticker_refresh":
        if ($action === "ticker_refresh") {
            @unlink($CACHE_TICKER);
            @unlink(__DIR__ . "/../cache/rss_ticker_gk.json");
            @unlink(__DIR__ . "/../cache/rss_ticker_jv.json");
        }
        $cachedTicker = rss_read_cache($CACHE_TICKER, $TTL);
        if ($cachedTicker !== null) {
            $res = $cachedTicker;
        } else {
            $gk = rss_fetch_and_parse(
                $FEED_URL_GAMING,
                __DIR__ . "/../cache/rss_ticker_gk.json",
                $TTL,
                15,
            );
            $jv = rss_fetch_and_parse(
                $FEED_URL_JV,
                __DIR__ . "/../cache/rss_ticker_jv.json",
                $TTL,
                15,
            );
            $merged = [];
            $seen = [];
            $all = array_merge(
                $gk["success"] ?? false ? $gk["items"] ?? [] : [],
                $jv["success"] ?? false ? $jv["items"] ?? [] : [],
            );
            usort($all, function ($a, $b) {
                return ($b["ts"] ?? 0) - ($a["ts"] ?? 0);
            });
            foreach ($all as $item) {
                $key = $item["title"] ?? "";
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $merged[] = $item;
            }
            $res = [
                "success" => true,
                "source" => "Gamekult + JV",
                "count" => count($merged),
                "items" => $merged,
            ];
            rss_write_cache($CACHE_TICKER, $res);
        }
        break;

    case "refresh":
        if (file_exists($CACHE)) {
            unlink($CACHE);
        }
        $res = rss_fetch_and_parse($FEED_URL_CRYPTO, $CACHE, $TTL, 20);
        break;

    case "coins":
    case "coins_refresh":
        if ($action === "coins_refresh") {
            @unlink($CACHE_COINS);
        }
        $res = rss_fetch_coins($FEED_URL_COINS, $CACHE_COINS, $TTL, 20);
        break;

    case "feed":
    default:
        $res = rss_fetch_and_parse($FEED_URL_CRYPTO, $CACHE, $TTL, 20);
        break;
}

if (isset($res["success"]) && $res["success"] === false) {
    http_response_code(502);
}
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
