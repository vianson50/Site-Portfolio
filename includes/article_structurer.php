<?php
/**
 * BLACK_PROTOCOL — Article Structurer
 * Génère un article structuré à partir d'un lien ou d'une info brute.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

// Helpers compatibles sans mbstring
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

/**
 * Récupère le contenu d'une URL et extrait les informations clés
 */
function fetchUrlContent(string $url): ?array
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $ctx = stream_context_create([
        "http" => [
            "timeout" => 10,
            "user_agent" =>
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "follow_location" => true,
            "max_redirects" => 3,
        ],
    ]);

    $html = @file_get_contents($url, false, $ctx);
    if (!$html) {
        return null;
    }

    // Extraire le titre
    $title = "";
    if (preg_match("/<title[^>]*>(.*?)<\/title>/is", $html, $m)) {
        $title = html_entity_decode(trim($m[1]), ENT_QUOTES, "UTF-8");
    }

    // Extraire la meta description
    $description = "";
    if (
        preg_match(
            '/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is',
            $html,
            $m,
        )
    ) {
        $description = html_entity_decode(trim($m[1]), ENT_QUOTES, "UTF-8");
    }
    if (
        empty($description) &&
        preg_match(
            '/<meta[^>]+content=["\'](.*?)["\'][^>]+name=["\']description["\']/is',
            $html,
            $m,
        )
    ) {
        $description = html_entity_decode(trim($m[1]), ENT_QUOTES, "UTF-8");
    }

    // Extraire les titres H1, H2, H3
    $headings = [];
    preg_match_all("/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is", $html, $matches);
    foreach ($matches[1] as $h) {
        $clean = trim(strip_tags($h));
        if (!empty($clean) && safeStrlen($clean) < 200) {
            $headings[] = $clean;
        }
    }

    // Extraire les paragraphes significatifs
    $paragraphs = [];
    preg_match_all("/<p[^>]*>(.*?)<\/p>/is", $html, $matches);
    foreach ($matches[1] as $p) {
        $clean = trim(strip_tags($p));
        if (safeStrlen($clean) > 50) {
            $paragraphs[] = $clean;
        }
    }

    // Extraire les mots-clés potentiels (mots fréquents)
    $text = strip_tags($html);
    $text = preg_replace("/\s+/", " ", $text);
    $words = str_word_count(
        strtolower(remove_accents($text)),
        1,
        "àâäéèêëïîôùûüÿçœæ",
    );
    $stopWords = [
        "le",
        "la",
        "les",
        "de",
        "des",
        "du",
        "un",
        "une",
        "et",
        "en",
        "est",
        "que",
        "qui",
        "dans",
        "pour",
        "pas",
        "sur",
        "ce",
        "avec",
        "ne",
        "se",
        "son",
        "il",
        "au",
        "plus",
        "par",
        "this",
        "the",
        "and",
        "for",
        "are",
        "but",
        "not",
        "you",
        "all",
        "can",
        "had",
        "her",
        "was",
        "one",
        "our",
        "out",
        "has",
        "have",
        "that",
        "with",
        "from",
        "they",
        "been",
        "said",
        "each",
        "which",
        "their",
        "will",
        "other",
        "about",
        "many",
        "then",
        "them",
        "these",
        "some",
        "would",
        "make",
        "like",
        "into",
        "time",
        "very",
        "when",
        "come",
        "could",
        "more",
        "made",
        "after",
        "also",
        "did",
        "much",
    ];
    $freq = [];
    foreach ($words as $w) {
        if (safeStrlen($w) > 3 && !in_array($w, $stopWords)) {
            $freq[$w] = ($freq[$w] ?? 0) + 1;
        }
    }
    arsort($freq);
    $keywords = array_slice(array_keys($freq), 0, 15);

    return [
        "title" => $title,
        "description" => $description,
        "headings" => array_slice($headings, 0, 20),
        "paragraphs" => array_slice($paragraphs, 0, 10),
        "keywords" => $keywords,
        "url" => $url,
    ];
}

/**
 * Supprime les accents d'une chaîne
 */
function remove_accents(string $str): string
{
    // Méthode moderne (PHP 8.2+) — utilise l'extension intl
    if (function_exists("transliterator_transliterate")) {
        $transliterated = transliterator_transliterate(
            "Any-Latin; Latin-ASCII; [:Punctuation:] Remove",
            $str,
        );
        return $transliterated !== false ? $transliterated : $str;
    }

    // Fallback manuel si intl n'est pas disponible
    $map = [
        "à" => "a",
        "â" => "a",
        "ä" => "a",
        "ã" => "a",
        "á" => "a",
        "ç" => "c",
        "é" => "e",
        "è" => "e",
        "ê" => "e",
        "ë" => "e",
        "í" => "i",
        "ì" => "i",
        "î" => "i",
        "ï" => "i",
        "ñ" => "n",
        "ó" => "o",
        "ò" => "o",
        "ô" => "o",
        "ö" => "o",
        "õ" => "o",
        "ú" => "u",
        "ù" => "u",
        "û" => "u",
        "ü" => "u",
        "ý" => "y",
        "ÿ" => "y",
    ];
    return strtr($str, $map);
}

/**
 * Génère un plan d'article structuré à partir des données extraites
 */
function generateArticle(array $data): array
{
    $source = $data["source"] ?? "text";
    $rawText = $data["raw_text"] ?? "";
    $urlData = $data["url_data"] ?? null;
    $topic = trim($data["topic"] ?? "");

    // Déterminer le sujet principal
    $subject = $topic;
    if (!$subject && $urlData) {
        $subject = $urlData["title"] ?? "";
    }
    if (!$subject) {
        $subject = "Sujet non identifié";
    }

    // Collecter le contenu source
    $sourceParagraphs = $urlData["paragraphs"] ?? [];
    $sourceHeadings = $urlData["headings"] ?? [];
    if (!empty($rawText)) {
        $rawParas = array_filter(
            array_map("trim", preg_split("/\n{2,}/", $rawText)),
        );
        foreach ($rawParas as $rp) {
            if (safeStrlen($rp) > 30) {
                $sourceParagraphs[] = $rp;
            }
        }
    }

    // Extraire les mots-clés
    $keywords = $urlData["keywords"] ?? [];
    if (empty($keywords) && !empty($rawText)) {
        $text = strtolower(remove_accents($rawText));
        $words = str_word_count($text, 1, "àâäéèêëïîôùûüÿçœæ");
        $stopWords = [
            "le",
            "la",
            "les",
            "de",
            "des",
            "du",
            "un",
            "une",
            "et",
            "en",
            "est",
            "que",
            "qui",
            "dans",
            "pour",
            "pas",
            "sur",
            "ce",
            "avec",
            "ne",
            "se",
            "son",
            "il",
            "au",
            "plus",
            "par",
            "this",
            "the",
            "and",
            "for",
            "are",
            "but",
            "not",
            "you",
            "all",
            "can",
            "had",
            "her",
            "was",
            "one",
            "our",
            "out",
            "has",
            "have",
            "that",
            "with",
            "from",
            "they",
            "been",
            "said",
            "each",
            "which",
            "their",
            "will",
            "other",
            "about",
            "many",
            "then",
            "them",
            "these",
            "some",
            "would",
            "make",
            "like",
            "into",
            "time",
            "very",
            "when",
            "come",
            "could",
            "more",
            "made",
            "after",
            "also",
            "did",
            "much",
        ];
        $freq = [];
        foreach ($words as $w) {
            if (safeStrlen($w) > 3 && !in_array($w, $stopWords)) {
                $freq[$w] = ($freq[$w] ?? 0) + 1;
            }
        }
        arsort($freq);
        $keywords = array_slice(array_keys($freq), 0, 10);
    }

    // Détecter le domaine / catégorie
    $category = detectCategory($subject . " " . implode(" ", $keywords));

    // Générer le titre SEO
    $seoTitle = generateSeoTitle($subject, $category);

    // Générer la méta description SEO
    $metaDesc = generateMetaDesc($subject, $category, $keywords);

    // ===== Générer le RÉSUMÉ DÉTAILLÉ =====
    $introduction = generateDetailedIntro(
        $subject,
        $category,
        $keywords,
        $sourceParagraphs,
    );

    // ===== Générer le PLAN avec vrai contenu =====
    $plan = generateRichPlan(
        $subject,
        $category,
        $keywords,
        $sourceHeadings,
        $sourceParagraphs,
    );

    // ===== Générer le résumé complet (corps de l'article) =====
    $fullSummary = generateFullSummary(
        $subject,
        $category,
        $sourceParagraphs,
        $sourceHeadings,
        $plan,
    );

    // Tags
    $tags = array_slice($keywords, 0, 8);

    return [
        "subject" => $subject,
        "category" => $category,
        "seo_title" => $seoTitle,
        "meta_description" => $metaDesc,
        "introduction" => $introduction,
        "plan" => $plan,
        "tags" => $tags,
        "full_summary" => $fullSummary,
        "source_url" => $urlData["url"] ?? "",
    ];
}

/**
 * Détecte la catégorie thématique
 */
function detectCategory(string $text): string
{
    $text = strtolower(remove_accents($text));

    $categories = [
        "Cybersécurité" => [
            "securite",
            "cyber",
            "hack",
            "vulnerabil",
            "malware",
            "pentest",
            "ransomware",
            "phishing",
            "firewall",
            "encryption",
            "cryptograph",
            "zero-day",
            "ssi",
            "rgpd",
            "owasp",
        ],
        "DevOps & Cloud" => [
            "docker",
            "kubernetes",
            "terraform",
            "aws",
            "azure",
            "gcp",
            "ci-cd",
            "pipeline",
            "deploy",
            "container",
            "orchestrat",
            "microservice",
            "devops",
            "cloud",
            "kubernetes",
            "jenkins",
            "gitlab",
        ],
        "Développement Web" => [
            "flutter",
            "react",
            "vue",
            "angular",
            "nextjs",
            "typescript",
            "javascript",
            "html",
            "css",
            "nodejs",
            "php",
            "laravel",
            "symfony",
            "api",
            "rest",
            "graphql",
            "frontend",
            "backend",
            "fullstack",
            "framework",
            "dart",
            "widget",
        ],
        "Intelligence Artificielle" => [
            "ia",
            "intelligence artificielle",
            "machine learning",
            "deep learning",
            "neural",
            "chatgpt",
            "gpt",
            "llm",
            "model",
            "ia",
            "ml",
            "ai",
            "nlp",
            "computer vision",
            "tensorflow",
            "pytorch",
        ],
        "Mobile" => [
            "mobile",
            "android",
            "ios",
            "swift",
            "kotlin",
            "react native",
            "flutter",
            "app",
            "smartphone",
            "tablette",
            "pwa",
        ],
        "Game Dev" => [
            "unreal",
            "unity",
            "godot",
            "game",
            "jeu video",
            "c++",
            "shader",
            "3d",
            "moteur",
            "gameplay",
            "level design",
        ],
        "Base de données" => [
            "sql",
            "mysql",
            "postgresql",
            "mongodb",
            "redis",
            "database",
            "requete",
            "index",
            "nosql",
            "migration",
            "schema",
        ],
        "Système & Réseau" => [
            "linux",
            "windows server",
            "reseau",
            "tcp",
            "dns",
            "dhcp",
            "vpn",
            "proxy",
            "ssh",
            "nginx",
            "apache",
            "monitoring",
        ],
    ];

    foreach ($categories as $cat => $terms) {
        foreach ($terms as $term) {
            if (strpos($text, $term) !== false) {
                return $cat;
            }
        }
    }
    return "Technologie";
}

/**
 * Génère un titre SEO optimisé
 */
function generateSeoTitle(string $subject, string $category): string
{
    $patterns = [
        "Cybersécurité" => [
            "{subject} : Analyse Complète des Enjeux de Sécurité",
            "Sécurité Informatique — Comprendre {subject}",
            "{subject} : Menaces, Vulnérabilités et Solutions de Protection",
        ],
        "DevOps & Cloud" => [
            "{subject} : Guide Complet d'Architecture Cloud",
            "Maîtriser {subject} — De Zéro à la Production",
            "{subject} : Bonnes Pratiques et Retours d'Expérience",
        ],
        "Développement Web" => [
            "{subject} : Le Guide Ultime pour les Développeurs",
            "Comprendre {subject} en 2025 — Ce qui Change Vraiment",
            "{subject} : Fonctionnalités, Impact et Guide de Migration",
        ],
        "Intelligence Artificielle" => [
            "{subject} : L'IA qui Révolutionne le Secteur",
            "Intelligence Artificielle — {subject} Expliqué Simplement",
            "{subject} : Opportunités, Risques et Perspectives",
        ],
        "Mobile" => [
            "{subject} : L'Avenir du Développement Mobile",
            "Builder des Apps avec {subject} — Guide Pratique",
            "{subject} vs la Concurrence : Analyse Détaillée",
        ],
        "Game Dev" => [
            "{subject} : Techniques Avancées pour Game Dev",
            "Créer avec {subject} — Du Concept au Jeu Final",
            "{subject} : Ce que Tout Développeur Doit Savoir",
        ],
    ];

    $opts = $patterns[$category] ?? $patterns["Développement Web"];
    return str_replace("{subject}", trim($subject), $opts[array_rand($opts)]);
}

/**
 * Génère une introduction captivante
 */
function generateIntroduction(
    string $subject,
    string $category,
    array $keywords,
): string {
    $kw = !empty($keywords)
        ? implode(", ", array_slice($keywords, 0, 4))
        : "technologie";

    $hooks = [
        "Imaginez un monde où **{subject}** redéfinit les règles du jeu. Ce n'est plus de la science-fiction — c'est maintenant.",
        "Le paysage technologique évolue à une vitesse vertigineuse. **{subject}** s'impose comme un tournant majeur que personne ne peut ignorer.",
        "Silencieusement, **{subject}** transforme notre façon de penser, de coder et de créer. Si vous ne l'avez pas encore vu venir, il est temps d'ouvrir les yeux.",
        "Chaque année, quelques ruptures changent tout. **{subject}** en fait partie — et son impact va bien au-delà de ce que l'on imagine.",
        "**{subject}** : trois mots qui suscitent passion, curiosité et parfois controverse. Décryptage d'un phénomène qui touche {kw} et au-delà.",
    ];

    $contexts = [
        "Cybersécurité" =>
            "Dans un contexte où les cyberattaques se multiplient et les menaces deviennent toujours plus sophistiquées, comprendre les enjeux de {subject} n'est plus une option — c'est une nécessité absolue pour toute organisation.",
        "DevOps & Cloud" =>
            "L'infrastructure moderne exige des solutions agiles, scalables et résilientes. {subject} s'inscrit au cœur de cette transformation, offrant aux équipes les outils nécessaires pour rester compétitives.",
        "Développement Web" =>
            "Le développement web ne cesse de se réinventer. Avec l'émergence de {subject}, les développeurs disposent de nouvelles possibilités qui bouleversent les approches traditionnelles.",
        "Intelligence Artificielle" =>
            "L'intelligence artificielle franchit chaque jour de nouveaux paliers. {subject} représente la dernière frontière de cette révolution, avec des implications profondes pour tous les secteurs.",
        "Mobile" =>
            "Le marché mobile explose. Des milliards d'utilisateurs attendent des applications toujours plus performantes. {subject} pourrait bien être la clé de la prochaine génération d'apps.",
        "Game Dev" =>
            "L'industrie du jeu vidéo pèse désormais plus que le cinéma et la musique réunis. {subject} donne aux créateurs des outils sans précédent pour repousser les limites de l'imaginaire.",
    ];

    $transitions = [
        "Dans cet article, nous allons décortiquer chaque aspect de {subject} : de ses fondements techniques à son impact sur l'écosystème, en passant par les bonnes pratiques et les pièges à éviter.",
        "Préparez-vous — on plonge dans les entrailles de {subject}. Du concept à l'implémentation, voici tout ce que vous devez savoir.",
        "Accrochez-vous. Ce guide exhaustif va transformer votre compréhension de {subject} et vous donner les clés pour rester en avance.",
    ];

    $hook = str_replace(
        ["{subject}", "{kw}"],
        [trim($subject), $kw],
        $hooks[array_rand($hooks)],
    );
    $context = str_replace(
        "{subject}",
        trim($subject),
        $contexts[$category] ?? $contexts["Développement Web"],
    );
    $transition = str_replace(
        "{subject}",
        trim($subject),
        $transitions[array_rand($transitions)],
    );

    return $hook . "\n\n" . $context . "\n\n" . $transition;
}

/**
 * Génère un plan détaillé
 */
function generatePlan(
    string $subject,
    string $category,
    array $keywords,
    ?array $urlData,
): array {
    $plan = [];

    // Section 1 : Contexte
    $plan[] = [
        "level" => 1,
        "title" => "Contexte & Enjeux",
        "desc" =>
            "Poser le décor. Pourquoi {subject} est pertinent aujourd'hui ? Quels problèmes résout-il ?",
        "subsections" => [
            [
                "title" => "L'état de l'art avant {subject}",
                "desc" =>
                    "Situation actuelle, limitations et frustrations existantes.",
            ],
            [
                "title" => "Les déclencheurs de {subject}",
                "desc" =>
                    "Événements, besoins ou innovations qui ont rendu {subject} nécessaire.",
            ],
            [
                "title" => "Les acteurs clés",
                "desc" =>
                    "Qui est impliqué ? Entreprises, communautés, contributeurs majeurs.",
            ],
        ],
    ];

    // Section 2 : Fonctionnement
    $plan[] = [
        "level" => 1,
        "title" => "Architecture & Fonctionnement",
        "desc" =>
            "Comment {subject} fonctionne sous le capot. Vision technique mais accessible.",
        "subsections" => [
            [
                "title" => "Principes fondamentaux",
                "desc" => "Concepts clés à comprendre avant d'aller plus loin.",
            ],
            [
                "title" => "Architecture technique",
                "desc" =>
                    "Schéma d'architecture, flux de données, composants principaux.",
            ],
            [
                "title" => "Points forts techniques",
                "desc" =>
                    "Ce qui distingue {subject} des alternatives existantes.",
            ],
        ],
    ];

    // Section 3 : Mise en pratique
    $plan[] = [
        "level" => 1,
        "title" => "Guide Pratique d'Implémentation",
        "desc" =>
            "Passer à l'action. Setup, configuration, premiers pas concrets.",
        "subsections" => [
            [
                "title" => "Prérequis & Installation",
                "desc" =>
                    "Environnement nécessaire et étapes de configuration.",
            ],
            [
                "title" => "Cas d'usage concret (Hands-on)",
                "desc" =>
                    "Tutoriel pas-à-pas avec un exemple réel et fonctionnel.",
            ],
            [
                "title" => "Bonnes pratiques",
                "desc" =>
                    "Patterns recommandés, conventions et astuces de pro.",
            ],
        ],
    ];

    // Section 4 : Analyse comparative
    $plan[] = [
        "level" => 1,
        "title" => "Comparaison & Alternatives",
        "desc" =>
            "Positionner {subject} face à la concurrence de manière objective.",
        "subsections" => [
            [
                "title" => "{subject} vs Concurrents",
                "desc" =>
                    "Tableau comparatif : forces, faiblesses, cas d'usage optimaux.",
            ],
            [
                "title" => "Quand choisir {subject}",
                "desc" =>
                    "Scénarios où {subject} est le meilleur choix — et quand l'éviter.",
            ],
        ],
    ];

    // Section 5 : Impact
    $plan[] = [
        "level" => 1,
        "title" => "Impact & Perspectives",
        "desc" => "Vers où va {subject} ? Vision à court, moyen et long terme.",
        "subsections" => [
            [
                "title" => "Impact sur l'industrie",
                "desc" => "Secteurs touchés, transformations attendues.",
            ],
            [
                "title" => "Tendances futures",
                "desc" => "Ce qui arrive dans les 12-24 prochains mois.",
            ],
            [
                "title" => "Opportunités pour les développeurs",
                "desc" => "Compétences à acquérir, marchés porteurs.",
            ],
        ],
    ];

    // Section 6 : Conclusion
    $plan[] = [
        "level" => 1,
        "title" => "Conclusion & Ressources",
        "desc" =>
            "Synthèse, call-to-action et liens utiles pour aller plus loin.",
        "subsections" => [
            [
                "title" => "Résumé des points clés",
                "desc" => "TL;DR en 5 points essentiels.",
            ],
            [
                "title" => "Ressources complémentaires",
                "desc" =>
                    "Documentation officielle, tutos, communautés, dépôts GitHub.",
            ],
        ],
    ];

    // Remplacer les placeholders
    foreach ($plan as &$section) {
        $section["title"] = str_replace(
            "{subject}",
            trim($subject),
            $section["title"],
        );
        $section["desc"] = str_replace(
            "{subject}",
            trim($subject),
            $section["desc"],
        );
        foreach ($section["subsections"] as &$sub) {
            $sub["title"] = str_replace(
                "{subject}",
                trim($subject),
                $sub["title"],
            );
            $sub["desc"] = str_replace(
                "{subject}",
                trim($subject),
                $sub["desc"],
            );
        }
    }

    return $plan;
}

/**
 * Génère une introduction détaillée basée sur le contenu réel
 */
function generateDetailedIntro(
    string $subject,
    string $category,
    array $keywords,
    array $sourceParagraphs,
): string {
    $kw = !empty($keywords)
        ? implode(", ", array_slice($keywords, 0, 4))
        : "technologie";

    $hook =
        "**" .
        trim($subject) .
        "** est un sujet qui suscite un intérêt croissant dans l'écosystème technologique. Que vous soyez développeur, architecte ou simplement curieux, comprendre ses enjeux est devenu essentiel.";

    // Si on a du contenu source, en extraire un aperçu
    if (!empty($sourceParagraphs)) {
        $bestPara = "";
        $bestLen = 0;
        foreach (array_slice($sourceParagraphs, 0, 5) as $p) {
            $clean = trim(strip_tags($p));
            $len = safeStrlen($clean);
            if ($len > $bestLen && $len > 80 && $len < 500) {
                $bestPara = $clean;
                $bestLen = $len;
            }
        }
        if ($bestPara) {
            $hook .= "\n\nVoici ce qu'il faut retenir :\n\n> " . $bestPara;
        }
    }

    $transition =
        "Dans ce résumé complet, nous allons explorer " .
        trim($subject) .
        " sous tous ses angles : contexte, fonctionnement technique, cas d'usage concrets, avantages, limites et perspectives d'avenir. Accrochez-vous — c'est dense, mais c'est essentiel.";

    return $hook . "\n\n" . $transition;
}

/**
 * Génère un plan riche basé sur les headings et paragraphes extraits
 */
function generateRichPlan(
    string $subject,
    string $category,
    array $keywords,
    array $sourceHeadings,
    array $sourceParagraphs,
): array {
    $plan = [];

    // Si on a des headings source, les utiliser pour construire le plan
    if (!empty($sourceHeadings)) {
        $usedHeadings = array_slice($sourceHeadings, 0, 8);
        foreach ($usedHeadings as $i => $heading) {
            $clean = trim(strip_tags($heading));
            if (empty($clean)) {
                continue;
            }

            // Trouver les paragraphes pertinents pour cette section
            $desc = generateSectionContent($clean, $sourceParagraphs);

            $plan[] = [
                "level" => 1,
                "title" => $clean,
                "desc" => $desc,
                "subsections" => [],
            ];
        }
    }

    // Si pas assez de headings, générer un plan par défaut enrichi
    if (count($plan) < 3) {
        $plan = generateDefaultRichPlan($subject, $category, $sourceParagraphs);
    }

    return $plan;
}

/**
 * Génère le contenu d'une section à partir des paragraphes source
 */
function generateSectionContent(
    string $heading,
    array $sourceParagraphs,
): string {
    $headingWords = array_filter(
        str_word_count(strtolower(remove_accents($heading)), 1),
        function ($w) {
            return safeStrlen($w) > 3;
        },
    );

    $bestParagraphs = [];
    foreach (array_slice($sourceParagraphs, 0, 15) as $p) {
        $clean = strtolower(remove_accents(strip_tags($p)));
        $matchScore = 0;
        foreach ($headingWords as $hw) {
            if (strpos($clean, $hw) !== false) {
                $matchScore++;
            }
        }
        if ($matchScore >= 1) {
            $bestParagraphs[] = [
                "score" => $matchScore,
                "text" => trim(strip_tags($p)),
            ];
        }
    }

    // Trier par pertinence
    usort($bestParagraphs, function ($a, $b) {
        return $b["score"] <=> $a["score"];
    });

    // Prendre les 2 meilleurs paragraphes
    $selected = array_slice($bestParagraphs, 0, 2);
    if (!empty($selected)) {
        $texts = array_map(function ($item) {
            $t = $item["text"];
            if (safeStrlen($t) > 300) {
                $t = safeSubstr($t, 0, 297) . "...";
            }
            return $t;
        }, $selected);
        return implode("\n\n", $texts);
    }

    // Fallback : description générique
    return "Analyse détaillée de : " . $heading . ".";
}

/**
 * Génère un plan par défaut enrichi avec contenu réel
 */
function generateDefaultRichPlan(
    string $subject,
    string $category,
    array $sourceParagraphs,
): array {
    $sections = [
        [
            "title" => "Contexte & Présentation",
            "fallback" =>
                "Présentation de " .
                $subject .
                ", son origine et les problèmes qu'il résout dans le paysage actuel.",
        ],
        [
            "title" => "Fonctionnement & Caractéristiques",
            "fallback" =>
                "Comment " .
                $subject .
                " fonctionne techniquement. Principales caractéristiques et innovations.",
        ],
        [
            "title" => "Points forts & Avantages",
            "fallback" =>
                "Les avantages concrets de " .
                $subject .
                " par rapport aux solutions existantes.",
        ],
        [
            "title" => "Limites & Points de vigilance",
            "fallback" =>
                "Ce qu'il faut savoir avant d'adopter " .
                $subject .
                ". Limites connues et précautions.",
        ],
        [
            "title" => "Cas d'usage & Mise en pratique",
            "fallback" =>
                "Exemples concrets d'utilisation de " .
                $subject .
                " dans des projets réels.",
        ],
        [
            "title" => "Conclusion & Perspectives",
            "fallback" =>
                "Synthèse des points clés et vision de l'avenir pour " .
                $subject .
                ".",
        ],
    ];

    $plan = [];
    foreach ($sections as $section) {
        $desc = generateSectionContent($section["title"], $sourceParagraphs);
        if (safeStrlen($desc) < 40) {
            $desc = $section["fallback"];
        }
        $plan[] = [
            "level" => 1,
            "title" => str_replace(
                "{subject}",
                trim($subject),
                $section["title"],
            ),
            "desc" => str_replace("{subject}", trim($subject), $desc),
            "subsections" => [],
        ];
    }
    return $plan;
}

/**
 * Génère le résumé complet de l'article (corps détaillé)
 */
function generateFullSummary(
    string $subject,
    string $category,
    array $sourceParagraphs,
    array $sourceHeadings,
    array $plan,
): string {
    $summary = "";

    // 1. Synthèse des points clés (résumé executive)
    $summary .= "## Résumé exécutif\n\n";
    if (!empty($sourceParagraphs)) {
        // Prendre les 3 paragraphes les plus informatifs
        $topParas = array_slice($sourceParagraphs, 0, 5);
        $selected = [];
        foreach ($topParas as $p) {
            $clean = trim(strip_tags($p));
            $len = safeStrlen($clean);
            if ($len > 60 && $len < 600) {
                $selected[] = $clean;
            }
            if (count($selected) >= 3) {
                break;
            }
        }
        if (!empty($selected)) {
            $summary .= implode("\n\n", $selected);
        } else {
            $summary .=
                trim($subject) .
                " représente un développement majeur dans le domaine de " .
                $category .
                ". Ce résumé compile les informations essentielles à connaître.";
        }
    } else {
        $summary .=
            trim($subject) .
            " est un sujet d'actualité dans le domaine de " .
            $category .
            ". Bien que les sources soient limitées, les éléments clés sont présentés ci-dessous.";
    }

    // 2. Points clés
    $summary .= "\n\n## Points clés à retenir\n\n";
    $keyPoints = extractKeyPoints($sourceParagraphs, $subject);
    foreach ($keyPoints as $i => $point) {
        $summary .= "**" . ($i + 1) . ".** " . $point . "\n\n";
    }

    // 3. Analyse détaillée par section
    if (!empty($plan)) {
        $summary .= "## Analyse détaillée\n\n";
        foreach ($plan as $i => $section) {
            $summary .= "### " . $section["title"] . "\n\n";
            $desc = $section["desc"] ?? "";
            if (!empty($desc) && safeStrlen($desc) > 40) {
                $summary .= $desc . "\n\n";
            }
        }
    }

    // 4. Conclusion
    $summary .= "## En conclusion\n\n";
    $summary .=
        trim($subject) .
        " s'inscrit dans une tendance de fond du secteur " .
        $category .
        ". Les développements récents montrent un écosystème en pleine mutation, avec des opportunités concrètes pour les professionnels qui sauront s'adapter rapidement. La veille technologique reste essentielle pour rester compétitif dans ce domaine en évolution permanente.";

    return $summary;
}

/**
 * Extrait les points clés du contenu source
 */
function extractKeyPoints(array $sourceParagraphs, string $subject): array
{
    $points = [];

    if (empty($sourceParagraphs)) {
        return [
            "Le sujet concerne directement les développeurs et professionnels tech.",
            "Les enjeux de sécurité et de performance sont au cœur du dispositif.",
            "L'adoption est en croissance rapide dans l'industrie.",
            "Des ressources et documentations sont disponibles pour démarrer.",
            "L'impact sur les pratiques existantes est significatif.",
        ];
    }

    // Analyser les phrases pour trouver les plus informatives
    $sentences = [];
    foreach (array_slice($sourceParagraphs, 0, 10) as $para) {
        $clean = trim(strip_tags($para));
        $parts = preg_split("/(?<=[.!?])\s+/", $clean);
        foreach ($parts as $s) {
            $s = trim($s);
            $len = safeStrlen($s);
            if ($len > 40 && $len < 250) {
                $score = 0;
                // Score basé sur la longueur (phrases moyennes = mieux)
                if ($len > 60 && $len < 180) {
                    $score += 2;
                }
                // Score basé sur les mots-clés indicateurs
                $indicators = [
                    "important",
                    "essentiel",
                    "clé",
                    "majeur",
                    "nouveau",
                    "permet",
                    "offre",
                    "solution",
                    "innovation",
                    "améliore",
                    "performance",
                    "sécurité",
                    "résout",
                    "transforme",
                ];
                $lower = strtolower($s);
                foreach ($indicators as $ind) {
                    if (strpos($lower, $ind) !== false) {
                        $score += 1;
                    }
                }
                $sentences[] = ["score" => $score, "text" => $s];
            }
        }
    }

    // Trier par score
    usort($sentences, function ($a, $b) {
        return $b["score"] <=> $a["score"];
    });

    // Prendre les 5 meilleures phrases uniques
    $used = [];
    foreach ($sentences as $s) {
        $short = safeSubstr($s["text"], 0, 40);
        $isDupe = false;
        foreach ($used as $u) {
            if (similar_text($short, $u) > 25) {
                $isDupe = true;
                break;
            }
        }
        if (!$isDupe) {
            $points[] = $s["text"];
            $used[] = $short;
        }
        if (count($points) >= 5) {
            break;
        }
    }

    // Fallback si pas assez de points
    while (count($points) < 3) {
        $fallbacks = [
            "L'impact sur les pratiques de développement est significatif.",
            "Les professionnels du secteur suivent cette évolution de près.",
            "L'écosystème continue de se développer rapidement.",
        ];
        $points[] = $fallbacks[count($points) % 3];
    }

    return $points;
}

/**
 * Génère une méta description SEO
 */
function generateMetaDesc(
    string $subject,
    string $category,
    array $keywords,
): string {
    $descs = [
        "Découvrez tout ce que vous devez savoir sur {subject} : fonctionnement, guide pratique, comparatifs et perspectives d'avenir.",
        "{subject} expliqué en détail. Architecture, mise en œuvre et impact sur l'écosystème tech en 2025.",
        "Guide complet sur {subject} : des fondamentaux à l'implémentation, en passant par les bonnes pratiques et les alternatives.",
    ];
    return str_replace("{subject}", trim($subject), $descs[array_rand($descs)]);
}
