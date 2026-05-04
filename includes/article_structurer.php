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
    $s = trim($subject);
    $kw = !empty($keywords)
        ? implode(", ", array_slice($keywords, 0, 4))
        : "technologie";

    // ── Hooks par catégorie ──
    $hooksByCat = [
        "Cybersécurité" => [
            "Dans un monde numérique où les menaces évoluent plus vite que les défenses, **{$s}** s'impose comme un enjeu critique pour toute organisation. Des attaques sophistiquées aux vulnérabilités zero-day, comprendre cette problématique est la première ligne de défense.",
            "Chaque jour, des milliers de cyberattaques ciblent des infrastructures critiques. **{$s}** est au cœur de cette guerre invisible où la moindre faille peut coûter des millions. Décryptage d'une menace qui touche {$kw} et bien au-delà.",
            "La cybersécurité n'est plus un luxe — c'est une survie numérique. **{$s}** illustre parfaitement comment les menaces modernes exploitent les moindres faiblesses de nos systèmes. Voici pourquoi chaque développeur et administrateur doit s'y intéresser d'urgence.",
        ],
        "DevOps & Cloud" => [
            "L'infrastructure moderne ne pardonne pas à ceux qui restent statiques. **{$s}** représente une évolution majeure dans la façon dont les équipes conçoivent, déploient et maintiennent leurs systèmes. Du container au cluster, chaque couche est concernée.",
            "Dans l'écosystème DevOps actuel, **{$s}** est bien plus qu'une tendance — c'est un fondement architectural. Les équipes qui l'adoptent gagnent en vélocité, en fiabilité et en capacité de scaling. Voici comment et pourquoi.",
        ],
        "Développement Web" => [
            "Le développement web traverse une période de mutation sans précédent. **{$s}** incarne cette évolution en offrant aux développeurs des capacités qui redéfinissent les standards de création. Si vous codez en 2025, vous ne pouvez pas ignorer cela.",
            "Chaque génération de frameworks apporte son lot de ruptures. **{$s}** fait partie de celles qui changent durablement la manière de concevoir des applications web modernes. Performance, DX, architecture — tout est repensé.",
        ],
        "Intelligence Artificielle" => [
            "L'intelligence artificielle ne cesse de repousser les limites du possible. **{$s}** se positionne à la pointe de cette révolution, avec des implications qui dépassent largement le cadre technique pour toucher l'économie, l'éthique et la société toute entière.",
            "Des modèles de langage à la vision par ordinateur, l'IA progresse à une vitesse vertigineuse. **{$s}** illustre cette dynamique avec des avancées concrètes qui transforment déjà des secteurs entiers.",
        ],
        "Game Dev" => [
            "L'industrie du jeu vidéo génère plus de revenus que le cinéma et la musique réunis. **{$s}** donne aux créateurs des outils d'une puissance inédite pour façonner des mondes immersifs et des expériences qui captivent des milliards de joueurs.",
            "Derrière chaque jeu qui vous marque, il y a une technologie invisible qui fait vivre le rêve. **{$s}** est l'une de ces technologies qui permet aux studios et aux indés de repousser les frontières de l'interactif.",
        ],
        "Mobile" => [
            "Avec plus de 6 milliards d'utilisateurs de smartphones dans le monde, le mobile est le premier point de contact digital. **{$s}** redéfinit les standards de développement d'applications et ouvre de nouvelles possibilités pour les développeurs.",
            "Le développement mobile évolue à toute vitesse. **{$s}** apporte des réponses concrètes aux défis de performance, d'UX et de cross-platform qui freinaient jusqu'ici les équipes mobile.",
        ],
    ];

    $hooks = $hooksByCat[$category] ?? [
        "**{$s}** est un sujet qui ne cesse de gagner en importance dans le paysage technologique contemporain. Comprendre ses enjeux, ses mécanismes et son impact est devenu essentiel pour tout professionnel du numérique.",
        "À l'ère de la transformation digitale, **{$s}** se distingue comme une thématique incontournable. Son influence s'étend bien au-delà du domaine technique pour toucher les stratégies d'entreprise et les usages quotidiens.",
    ];

    $hook = $hooks[array_rand($hooks)];

    // Si on a du contenu source, en extraire un aperçu pertinent
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
            $hook .= "\n\n> " . $bestPara;
        }
    }

    // ── Transitions enrichies par catégorie ──
    $transitions = [
        "Ce dossier approfondi examine **{$s}** sous tous ses angles : de ses fondements à son impact sur l'écosystème, en passant par les bonnes pratiques, les pièges courants et les perspectives d'évolution. Que vous soyez débutant ou expert, vous y trouverez des informations actionnables.",
        "Dans cette analyse détaillée de **{$s}**, nous couvrirons le contexte historique, le fonctionnement technique, les cas d'usage concrets, les alternatives disponibles et les tendances à venir. Chaque section est conçue pour vous donner une compréhension solide et immédiatement applicable.",
        "De la théorie à la pratique, ce guide complet sur **{$s}** vous accompagne étape par étape. Nous analyserons les enjeux, décortiquerons l'architecture, comparerons les solutions et projeterons les évolutions futures. Préparez-vous — c'est dense mais essentiel.",
    ];

    $transition = $transitions[array_rand($transitions)];

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
    $s = trim($subject);

    // ── Sections enrichies par catégorie ──
    $sectionsByCat = [
        "Cybersécurité" => [
            [
                "title" => "Paysage des menaces & Contexte",
                "desc" => "L'écosystème des cybermenaces a radicalement changé ces dernières années. **{$s}** s'inscrit dans un contexte où les attaques sont de plus en plus sophistiquées, automatisées et difficiles à détecter. Les groupes APT (Advanced Persistent Threats) exploitent des chaines d'attaque complexes, combinant phishing, élévation de privilèges et mouvement latéral. En parallèle, la surface d'attaque s'élargit avec le cloud, l'IoT et le télétravail. Comprendre les vecteurs d'attaque liés à **{$s}** est la première étape pour construire une défense efficace.",
            ],
            [
                "title" => "Mécanismes d'attaque & Vulnérabilités",
                "desc" => "Les vulnérabilités exploitées dans le cadre de **{$s}** reposent souvent sur des failles connues mais sous-estimées : injections SQL, cross-site scripting (XSS), broken access control, et misconfigurations d'infrastructure. Les attaquants utilisent des outils automatisés pour scanner massivement les cibles potentielles. L'analyse des TTP (Tactics, Techniques & Procedures) observées révèle des patterns récurrents qu'il est possible de contrer avec les bonnes mesures.",
            ],
            [
                "title" => "Stratégies de défense & Bonnes pratiques",
                "desc" => "Se protéger efficacement contre les menaces liées à **{$s}** nécessite une approche multicouche. Le modèle Zero Trust, le chiffrement de bout en bout, l'authentification multi-facteurs (MFA) et le monitoring en temps réel constituent le socle d'une posture de sécurité solide. Les audits réguliers, les tests de pénétration et la formation continue des équipes sont tout aussi cruciaux que les outils techniques déployés.",
            ],
            [
                "title" => "Outils & Frameworks de référence",
                "desc" => "L'écosystème de la cybersécurité offre une panoplie d'outils pour faire face à **{$s}**. Du côté offensif : Nmap, Metasploit, Burp Suite, Wireshark. Du côté défensif : SIEM (Splunk, ELK), EDR (CrowdStrike, SentinelOne), WAF et solutions de threat intelligence. Chaque outil a ses forces et ses limites — le choix dépend du contexte, du budget et du niveau de maturité de l'organisation.",
            ],
            [
                "title" => "Cas réels & Retours d'expérience",
                "desc" => "L'histoire récente regorge d'incidents liés à **{$s}** qui ont eu des conséquences désastreuses : fuites de données de millions d'utilisateurs, paralysie d'hopitaux par ransomware, vols de propriété intellectuelle. L'analyse de ces cas concrets permet d'identifier les failles récurrentes et d'en tirer des leçons applicable à toute organisation. La détection précoce et la réponse incidentielle rapide font la différence entre un incident mineur et une catastrophe.",
            ],
            [
                "title" => "Perspectives & Évolution des menaces",
                "desc" => "L'avenir de **{$s}** sera marqué par l'arrivée des attaques alimentées par l'IA (deepfakes, phishing généré par LLM, malware polymorphe), l'expansion des attaques supply chain, et les menaces quantiques sur la cryptographie. Les professionnels de la sécurité doivent anticiper ces évolutions en investissant dans la formation, la R&D et la collaboration inter-organisationnelle.",
            ],
        ],
        "DevOps & Cloud" => [
            [
                "title" => "Architecture & Fondamentaux",
                "desc" => "**{$s}** repose sur des principes architecturaux qui transforment la façon dont les applications sont construites et déployées. L'approche Infrastructure as Code (IaC), la conteneurisation, le service mesh et l'observabilité forment le socle d'une plateforme cloud-native robuste. Comprendre ces fondements est indispensable avant toute mise en oeuvre.",
            ],
            [
                "title" => "Pipeline CI/CD & Automatisation",
                "desc" => "L'automatisation est le coeur battant du DevOps. Avec **{$s}**, les pipelines CI/CD deviennent plus rapides, plus sûrs et plus reproductibles. Du commit au déploiement en production, chaque étape peut être automatisée : tests unitaires, integration tests, scans de sécurité, build d'images container, blue-green deployment, canary releases. L'objectif : délivrer de la valeur plus vite sans sacrifier la qualité.",
            ],
            [
                "title" => "Scalabilité & Résilience",
                "desc" => "Les systèmes modernes doivent absorber des pics de charge sans fléchir. **{$s}** offre des mécanismes de scaling horizontal, de load balancing intelligent et de circuit breaking qui garantissent la disponibilité même sous stress. La résilience ne s'improvise pas — elle se conçoit dès l'architecture avec du chaos engineering, des health checks et des stratégies de rollback automatisées.",
            ],
            [
                "title" => "Monitoring, Observabilité & SRE",
                "desc" => "Un système qu'on ne peut pas observer est un système qu'on ne peut pas améliorer. **{$s}** intègre des pratiques d'observabilité avancées : métriques (Prometheus, Grafana), logs structurés (ELK, Loki), traces distribuées (Jaeger, Zipkin). Les SRE (Site Reliability Engineers) utilisent ces données pour définir des SLO/SLI et garantir la fiabilité du service.",
            ],
            [
                "title" => "Sécurité & Compliance (DevSecOps)",
                "desc" => "La sécurité ne peut plus être un afterthought dans le cycle DevOps. **{$s}** intègre les contrôles de sécurité dès le début du pipeline : scan de vulnérabilités dans les images container, analyse statique du code (SAST), analyse des dépendances (SCA), policy-as-code avec Open Policy Agent. Le DevSecOps garantit que chaque release respecte les standards de sécurité sans ralentir la vélocité.",
            ],
            [
                "title" => "Retours d'expérience & Roadmap",
                "desc" => "Les organisations qui ont adopté **{$s}** rapportent des gains significatifs : réduction du time-to-market, diminution des incidents de production, meilleure collaboration entre équipes dev et ops. Cependant, la transition demande un investissement en formation, une refonte des processus et un accompagnement au changement. Les prochaines évolutions porteront sur l'IA dans l'Ops, le platform engineering et le GitOps.",
            ],
        ],
        "Développement Web" => [
            [
                "title" => "État de l'art & Contexte technique",
                "desc" => "Le paysage du développement web évolue à un rythme effréné. **{$s}** s'inscrit dans cette évolution en adressant des problématiques que les générations précédentes de technologies laissaient en suspens : performance perçue, expérience développeur, architecture modulaire et SEO technique. Avant de plonger dans les détails, il est essentiel de comprendre le contexte qui a rendu cette approche nécessaire et les limites des solutions antérieures.",
            ],
            [
                "title" => "Architecture & Principes de conception",
                "desc" => "L'architecture derrière **{$s}** repose sur des principes éprouvés : composantisation, rendering optimisé (SSR, SSG, ISR), gestion d'état prédictible et routage basé sur le système de fichiers. Ces choix architecturaux ne sont pas anodins — ils déterminent la maintenabilité, les performances et la capacité à scaler de l'application. Chaque décision technique a un impact direct sur l'expérience utilisateur final.",
            ],
            [
                "title" => "Mise en pratique : Du setup au déploiement",
                "desc" => "Passer de la théorie à la pratique avec **{$s}** demande une approche méthodique. Le setup initial, la configuration du tooling (linting, formatting, testing), la structuration du projet et les conventions d'équipe sont autant d'étapes qui conditionnent la réussite. Ce guide pas-à-pas couvre chaque étape avec des exemples concrets et des configurations prêtes à l'emploi.",
            ],
            [
                "title" => "Performance & Optimisation",
                "desc" => "La performance web n'est pas un détail — c'est un facteur de conversion. **{$s}** offre des leviers d'optimisation puissants : code splitting automatique, lazy loading des composants, optimisation des images, caching agressif et minimisation du JavaScript bloquant. Les Core Web Vitals (LCP, FID, CLS) deviennent des métriques de première importance pour le référencement comme pour l'UX.",
            ],
            [
                "title" => "Écosystème & Comparatifs",
                "desc" => "**{$s}** ne vit pas en isolation — il s'inscrit dans un écosystème riche de bibliothèques, plugins, templates et outils complémentaires. Comment se positionne-t-il face aux alternatives ? Quels sont ses cas d'usage optimaux et ses limites ? Cette analyse comparative objective aide à faire le bon choix technologique en fonction du contexte du projet.",
            ],
            [
                "title" => "Bonnes pratiques & Perspectives",
                "desc" => "Adopter **{$s}** efficacement, c'est suivre les patterns éprouvés par la communauté : gestion propre des side effects, tests unitaires et d'integration systématiques, documentation vivante, revues de code rigoureuses. Les tendances à venir incluent l'IA-assisted development, les edge functions, les architectures islands et le progressive enhancement.",
            ],
        ],
        "Intelligence Artificielle" => [
            [
                "title" => "Fondements scientifiques & Contexte",
                "desc" => "**{$s}** s'appuie sur des décennies de recherche en apprentissage automatique, réseaux de neurones et traitement du langage naturel. Les percées récentes en deep learning, la disponibilité de puissance de calcul massive et l'explosion des données ont créé les conditions parfaites pour l'émergence de solutions comme **{$s}**. Comprendre les fondements mathématiques et informatiques permet de mieux apprécier les capacités et les limites du système.",
            ],
            [
                "title" => "Architecture technique du modèle",
                "desc" => "Sous le capot, **{$s}** utilise une architecture de transformer avec des mécanismes d'attention qui lui permettent de capturer des dépendances complexes dans les données. L'entrainement implique des milliards de paramètres, des techniques d'optimisation avancées (Adam, LoRA) et des infrastructures GPU/TPU distribuées. L'architecture détermine directement la qualité des résultats et les ressources nécessaires.",
            ],
            [
                "title" => "Cas d'usage & Applications concrètes",
                "desc" => "Les applications de **{$s}** couvrent un spectre impressionnant : génération de texte, analyse de sentiments, traduction automatique, résumé de documents, assistance au code, création de contenu, diagnostic médical, conduite autonome. Chaque cas d'usage a ses spécificités en termes de fine-tuning, de prompt engineering et de validation des résultats.",
            ],
            [
                "title" => "Éthique, Biais & Régulation",
                "desc" => "Le déploiement de **{$s}** soulève des questions éthiques fondamentales : biais dans les données d'entrainement, hallucinations, désinformation, impact sur l'emploi, vie privée. L'EU AI Act et les initiatives de responsible AI imposent des cadres de plus en plus stricts. Les développeurs et organisations doivent intégrer ces dimensions dès la conception (privacy by design, fairness audits).",
            ],
            [
                "title" => "Implémentation & Outils",
                "desc" => "Mettre en oeuvre **{$s}** demande de maîtriser un stack technique spécifique : frameworks (PyTorch, TensorFlow, Hugging Face), orchestration (MLflow, Kubeflow), serving (vLLM, TGI), monitoring (Weights & Biases). Du prototypage en notebook à la production à l'échelle, le chemin est jalonné de défis techniques et opérationnels.",
            ],
            [
                "title" => "Avenir & Tendances émergentes",
                "desc" => "L'avenir de **{$s}** s'oriente vers des modèles multimodaux (texte + image + audio + vidéo), l'IA agentic (agents autonomes capable de planifier et exécuter), le small language models (SLM) pour l'edge computing, et l'IA open-source qui démocratise l'accès. Les 12-24 prochains mois seront déterminants pour l'adoption massive et la maturité industrielle.",
            ],
        ],
        "Game Dev" => [
            [
                "title" => "Moteur & Outils de développement",
                "desc" => "Le choix du moteur est la décision la plus impactante dans un projet de jeu. **{$s}** implique une compréhension profonde des capacités du moteur : système de rendu, pipeline graphique, physique, audio, IA NPCs, networking. Unreal Engine 5 avec Nanite et Lumen, Unity avec son écosystème, ou Godot pour l'indé — chaque option a ses forces et ses compromis.",
            ],
            [
                "title" => "Gameplay & Game Design",
                "desc" => "Le gameplay est l'âme du jeu. **{$s}** touche à la conception de mécaniques engaging, de boucles de feedback satisfaisantes et de courbes de difficulté bien dosées. Le level design, la progression du joueur, les systèmes de récompense et l'équilibrage sont des éléments qui demandent itération et playtesting rigoureux.",
            ],
            [
                "title" => "Graphismes, Shaders & Audio",
                "desc" => "**{$s}** exploite les dernières avancées en rendu temps réel : PBR (Physically Based Rendering), raytracing, shaders custom, particules volumétriques. L'audio spatial et la musique adaptative complètent l'immersion. L'optimisation est cruciale — maintenir 60 FPS stable tout en offrant un rendu de qualité demande un savoir-faire technique pointu.",
            ],
            [
                "title" => "Performance & Optimisation",
                "desc" => "La performance est non négociable dans le jeu vidéo. **{$s}** demande une maitrise du profiling GPU/CPU, de la gestion mémoire, du LOD (Level of Detail), du culling et du streaming de textures. Les techniques d'optimisation avancées (GPU instancing, compute shaders, asset bundling) font la différence entre un jeu fluide et un slideshow.",
            ],
            [
                "title" => "Publication & Distribution",
                "desc" => "Sortir un jeu est un projet en soi. **{$s}** implique de maîtriser les processus de soumission sur Steam, consoles (certification TRC/XRc/Lotcheck), mobile (App Store/Play Store). Le marketing, la communauté, le post-launch content et le support technique sont tout aussi importants que le développement lui-même.",
            ],
            [
                "title" => "Tendances & Avenir du Game Dev",
                "desc" => "L'industrie du jeu évolue rapidement : IA procédurale pour la génération de contenu, cloud gaming, VR/AR mainstream, cross-platform play. **{$s}** s'inscrit dans ces tendances qui redéfinissent ce que signifie créer des expériences interactives. Les développeurs qui anticipent ces évolutions auront un avantage compétitif majeur.",
            ],
        ],
    ];

    // Sections par défaut (pour catégories sans templates dédiés)
    $defaultSections = [
        [
            "title" => "Contexte & Présentation générale",
            "desc" => "**{$s}** s'inscrit dans un contexte technologique en mutation rapide. Cette section pose les bases en explorant l'origine du sujet, les problèmes qu'il adresse et les raisons pour lesquelles il gagne en pertinence aujourd'hui. Comprendre le contexte historique et les facteurs déclencheurs est essentiel pour apprécier pleinement les enjeux techniques et stratégiques qui sous-tendent cette thématique.",
        ],
        [
            "title" => "Architecture & Fonctionnement",
            "desc" => "Sous la surface, **{$s}** repose sur des principes de conception et une architecture qui déterminent sa robustesse et ses capacités. Cette section décortique les mécanismes fondamentaux, les composants clés et les flux de données qui font tourner le système. De l'infrastructure backend aux interfaces utilisateur, chaque couche mérite une analyse approfondie.",
        ],
        [
            "title" => "Avantages & Points forts",
            "desc" => "Les forces de **{$s}** sont multiples et méritent d'être détaillées. Performance, scalabilité, facilité d'utilisation, écosystème, communauté active — chaque atout contribue à rendre la solution attractive. Nous analysons objectivement les bénéfices concrets que les utilisateurs et les organisations peuvent en tirer, avec des données et des exemples à l'appui.",
        ],
        [
            "title" => "Limites & Points de vigilance",
            "desc" => "Aucune technologie n'est parfaite. **{$s}** présente des limites qu'il faut connaître avant de s'engager : courbe d'apprentissage, contraintes techniques, coûts cachés, dépendances vendor lock-in, maturité de l'écosystème. Cette analyse honnèse des faiblesses permet de prendre des décisions éclairées et d'anticiper les risques potentiels.",
        ],
        [
            "title" => "Cas d'usage & Mise en pratique",
            "desc" => "La théorie ne vaut rien sans la pratique. Cette section présente des cas d'usage concrets de **{$s}** dans des contextes réels : startup, entreprise, projet personnel, use case spécifique. Des exemples de code, des schémas d'architecture et des retours d'expérience illustrent comment tirer le meilleur parti de cette technologie dans des situations variées.",
        ],
        [
            "title" => "Perspectives & Évolutions futures",
            "desc" => "L'avenir de **{$s}** s'annonce riche en développements. Les tendances émergentes, les roadmap des mainteneurs, les signaux faibles de la communauté et les avancées de la recherche dessinent les contours de ce qui nous attend. Les professionnels qui anticipent ces évolutions se donneront un avantage stratégique décisif dans les mois et années à venir.",
        ],
    ];

    $sections = $sectionsByCat[$category] ?? $defaultSections;

    $plan = [];
    foreach ($sections as $section) {
        // Essayer de trouver du contenu source pertinent
        $sourceContent = generateSectionContent(
            $section["title"],
            $sourceParagraphs,
        );
        $desc = $section["desc"];

        // Si le contenu source est pertinent, l'ajouter à la description
        if (
            safeStrlen($sourceContent) > 60 &&
            strpos($sourceContent, "Analyse détaillée de") === false
        ) {
            $desc .= "\n\n" . $sourceContent;
        }

        $plan[] = [
            "level" => 1,
            "title" => str_replace("{subject}", $s, $section["title"]),
            "desc" => str_replace("{subject}", $s, $desc),
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
    $conclusions = [
        "En definitive, **{$subject}** represente bien plus qu'une simple evolution technologique — c'est un changement de paradigme qui redéfinit les règles du jeu dans le secteur {$category}. Les organisations qui investissent dans la compréhension et l'adoption de cette thématique aujourd'hui se positionneront en leaders demain. La clé du succès réside dans l'anticipation, la formation continue et l'expérimentation concrète. Le moment d'agir est maintenant.",
        "Pour conclure, **{$subject}** nous rappelle que l'innovation technologique ne ralentit jamais — c'est à nous d'accélérer pour ne pas être laissés pour compte. Les enjeux analysés dans ce dossier montrent clairement que les opportunités sont immenses pour ceux qui osent. La veille technologique, l'apprentissage continu et la mise en pratique restent les meilleurs investissements.",
        "En somme, **{$subject}** illustre parfaitement la vitesse à laquelle le secteur {$category} évolue. Les concepts, outils et pratiques présentés dans cette analyse constituent une base solide pour tout professionnel souhaitant rester compétitif. L'important n'est pas de tout maîtriser immédiatement, mais de commencer — un projet, un prototype, une exploration. Chaque pas compte.",
    ];
    $summary .= $conclusions[array_rand($conclusions)];

    return $summary;
}

/**
 * Extrait les points clés du contenu source
 */
function extractKeyPoints(array $sourceParagraphs, string $subject): array
{
    $points = [];

    if (empty($sourceParagraphs)) {
        $s = trim($subject);
        return [
            "**{$s}** représente un enjeu stratégique majeur pour les professionnels du secteur tech en 2025.",
            "L'adoption de cette technologie/approche est en croissance accélérée, portée par des cas d'usage concrets et démontrés.",
            "Les défis techniques (performance, sécurité, scalabilité) sont réels mais surmontables avec les bonnes pratiques.",
            "L'écosystème autour de {$s} est dynamique : communauté active, documentation croissante, outils matures.",
            "Investir dans la compréhension de {$s} maintenant offre un avantage compétitif significatif à court et moyen terme.",
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
