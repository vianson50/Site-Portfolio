<?php
/**
 * BLACK_PROTOCOL — SEO & Meta Tags Include
 *
 * Centralise les balises favicon, OpenGraph, Twitter Cards, meta SEO
 * et JSON-LD Schema.org pour Knowledge Graph / RAG.
 *
 * Usage :
 *   require_once __DIR__ . '/seo.php';
 *   echo renderMeta($title, $description, $pageType);
 *
 * @package BLACK_PROTOCOL
 * @version 2.0
 */

// === URL de base du site ===
$siteUrl =
    (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"
        ? "https"
        : "http") .
    "://" .
    ($_SERVER["HTTP_HOST"] ?? "localhost");
$sitePath = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\");
$baseUrl = $siteUrl . $sitePath;

/**
 * Génère toutes les balises <head> SEO + JSON-LD pour une page
 *
 * @param string $title         Titre SEO de la page
 * @param string $description   Meta description persuasive (150-160 cars)
 * @param string $pageType      Type : 'website', 'profile', 'article'
 * @param string|null $ogImage  Chemin relatif vers l'image OG
 * @param array|null $keywords  Mots-clés SEO supplémentaires
 * @return string HTML des balises meta à insérer dans <head>
 */
function renderMeta(
    string $title,
    string $description,
    string $pageType = "website",
    ?string $ogImage = null,
    ?array $keywords = null,
): string {
    global $baseUrl;

    // ── Titre complet ──
    $fullTitle = $title . " | BLACK_PROTOCOL";

    // ── Image OG par défaut ──
    $ogImage = $ogImage ?? "assets/img/og-image.png";
    $ogImageUrl = $baseUrl . "/" . $ogImage;

    // ── URL canonique ──
    $canonicalUrl = $baseUrl . "/" . basename($_SERVER["SCRIPT_NAME"]);

    // ── Description nettoyée ──
    $cleanDesc = htmlspecialchars($description, ENT_QUOTES, "UTF-8");

    // ── Mots-clés par défaut + personnalisés ──
    $defaultKeywords = [
        "BLACK_PROTOCOL",
        "portfolio",
        "développeur full-stack",
        "ethical hacker",
        "cybersécurité",
        'Côte d\'Ivoire',
        "Abidjan",
        "cyberpunk",
        "TypeScript",
        "React",
        "Next.js",
        "Python",
        "Rust",
        "penetration testing",
        "devops",
        "game development",
    ];
    if ($keywords) {
        $defaultKeywords = array_merge($defaultKeywords, $keywords);
    }
    $kwString = htmlspecialchars(
        implode(", ", $defaultKeywords),
        ENT_QUOTES,
        "UTF-8",
    );

    $html = "";

    // ================================================================
    // FAVICON
    // ================================================================
    $html .= <<<HTML

        <!-- ===== FAVICON ===== -->
        <link rel="icon" type="image/png" sizes="32x32" href="{$baseUrl}/Favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="{$baseUrl}/Favicon/favicon-32x32.png">
        <link rel="apple-touch-icon" sizes="180x180" href="{$baseUrl}/Favicon/favicon-32x32.png">
        <link rel="shortcut icon" href="{$baseUrl}/Favicon/favicon-32x32.png">
    HTML;

    // ================================================================
    // SEO — Meta optimisées pour RAG / Knowledge Graph
    // ================================================================
    $html .= <<<HTML

        <!-- ===== SEO META ===== -->
        <meta name="description" content="{$cleanDesc}">
        <meta name="keywords" content="{$kwString}">
        <meta name="author" content="BLACK_PROTOCOL">
        <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
        <meta name="googlebot" content="index, follow">
        <meta name="theme-color" content="#0A0A0B">
        <meta name="category" content="Technology">
        <meta name="coverage" content="Worldwide">
        <meta name="distribution" content="Global">
        <meta name="rating" content="General">
        <meta name="language" content="fr">
        <link rel="canonical" href="{$canonicalUrl}">
    HTML;

    // ================================================================
    // OPEN GRAPH
    // ================================================================
    $html .= <<<HTML

        <!-- ===== OPEN GRAPH ===== -->
        <meta property="og:type" content="{$pageType}">
        <meta property="og:title" content="{$fullTitle}">
        <meta property="og:description" content="{$cleanDesc}">
        <meta property="og:image" content="{$ogImageUrl}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="BLACK_PROTOCOL — Portfolio Cyberpunk Développeur">
        <meta property="og:image:type" content="image/png">
        <meta property="og:url" content="{$canonicalUrl}">
        <meta property="og:site_name" content="BLACK_PROTOCOL">
        <meta property="og:locale" content="fr_FR">
        <meta property="article:section" content="Technology">
        <meta property="article:tag" content="Cybersécurité">
        <meta property="article:tag" content="Full-Stack">
        <meta property="article:tag" content="Ethical Hacking">
    HTML;

    // ================================================================
    // TWITTER CARD
    // ================================================================
    $html .= <<<HTML

        <!-- ===== TWITTER CARD ===== -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{$fullTitle}">
        <meta name="twitter:description" content="{$cleanDesc}">
        <meta name="twitter:image" content="{$ogImageUrl}">
        <meta name="twitter:image:alt" content="BLACK_PROTOCOL — Portfolio Cyberpunk">
        <meta name="twitter:label1" content="Compétences">
        <meta name="twitter:data1" content="Full-Stack, Cybersécurité, DevOps, Game Dev">
        <meta name="twitter:label2" content="Localisation">
        <meta name="twitter:data2" content="Abidjan, Côte d'Ivoire">
    HTML;

    // ================================================================
    // JSON-LD — Schema.org pour Knowledge Graph / RAG
    // ================================================================
    $jsonLd = generateJsonLd(
        $fullTitle,
        $cleanDesc,
        $canonicalUrl,
        $ogImageUrl,
        $pageType,
    );
    $html .= $jsonLd;

    return $html;
}

/**
 * Génère le JSON-LD Schema.org pour Knowledge Graph & RAG
 *
 * Inclut : Person, WebSite, Organization, BreadcrumbList
 * Cela permet aux moteurs de recherche et aux systèmes RAG
 * de comprendre la structure du site et les entités principales.
 */
function generateJsonLd(
    string $title,
    string $description,
    string $url,
    string $image,
    string $pageType,
): string {
    global $baseUrl;

    // ── Person Schema (Profil développeur) ──
    $personLd = [
        "@context" => "https://schema.org",
        "@type" => "Person",
        "name" => "BLACK_PROTOCOL",
        "url" => $baseUrl . "/index.php",
        "image" => $baseUrl . "/assets/img/og-image.png",
        "jobTitle" => "Développeur Full-Stack & Ethical Hacker",
        "description" =>
            'Développeur Full-Stack, Ethical Hacker et Designer basé à Abidjan, Côte d\'Ivoire. Spécialisé en cybersécurité offensive, architecture cloud et game development.',
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => "Abidjan",
            "addressCountry" => "CI",
        ],
        "knowsAbout" => [
            "Cybersécurité",
            "Penetration Testing",
            "Développement Web",
            "TypeScript",
            "React",
            "Next.js",
            "Node.js",
            "Python",
            "Rust",
            "PHP",
            "Docker",
            "Kubernetes",
            "AWS",
            "Terraform",
            "Unreal Engine 5",
            "Game Development",
            "UI/UX Design",
            "Ethical Hacking",
            "Metasploit",
            "Kali Linux",
        ],
        "sameAs" => [$baseUrl . "/index.php"],
        "worksFor" => [
            "@type" => "Organization",
            "name" => "BLACK_PROTOCOL",
            "url" => $baseUrl,
        ],
    ];

    // ── WebSite Schema ──
    $websiteLd = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => "BLACK_PROTOCOL",
        "alternateName" => "BLACK_PROTOCOL Portfolio",
        "url" => $baseUrl,
        "description" =>
            'Portfolio cyberpunk d\'un Développeur Full-Stack et Ethical Hacker. Cybersécurité, Web Design, Game Dev et DevOps.',
        "inLanguage" => "fr",
        "author" => [
            "@type" => "Person",
            "name" => "BLACK_PROTOCOL",
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "BLACK_PROTOCOL",
            "logo" => [
                "@type" => "ImageObject",
                "url" => $baseUrl . "/assets/img/og-image.png",
            ],
        ],
    ];

    // ── WebPage Schema (spécifique à la page) ──
    $webPageLd = [
        "@context" => "https://schema.org",
        "@type" => $pageType === "profile" ? "ProfilePage" : "WebPage",
        "name" => $title,
        "description" => $description,
        "url" => $url,
        "image" => $image,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => "BLACK_PROTOCOL",
            "url" => $baseUrl,
        ],
        "about" => [
            "@type" => "Person",
            "name" => "BLACK_PROTOCOL",
            "jobTitle" => "Développeur Full-Stack & Ethical Hacker",
        ],
        "inLanguage" => "fr",
        "dateModified" => date("Y-m-d"),
    ];

    // ── BreadcrumbList (fil d'Ariane) ──
    $breadcrumbLd = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Accueil",
                "item" => $baseUrl . "/index.php",
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => $title,
                "item" => $url,
            ],
        ],
    ];

    // ── Encodage JSON-LD ──
    $personJson = json_encode(
        $personLd,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );
    $websiteJson = json_encode(
        $websiteLd,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );
    $webPageJson = json_encode(
        $webPageLd,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );
    $breadcrumbJson = json_encode(
        $breadcrumbLd,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );

    return <<<HTML

        <!-- ===== JSON-LD SCHEMA.ORG / KNOWLEDGE GRAPH / RAG ===== -->
        <script type="application/ld+json">{$personJson}</script>
        <script type="application/ld+json">{$websiteJson}</script>
        <script type="application/ld+json">{$webPageJson}</script>
        <script type="application/ld+json">{$breadcrumbJson}</script>
    HTML;
}
