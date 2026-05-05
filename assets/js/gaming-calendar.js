/**
 * ========================================
 * GAMING CALENDAR — Esports Tournament Tracker
 * Organisé par catégories : MOBA, FPS, SPORT, COMBAT
 * ========================================
 */

(function () {
  "use strict";

  /* ── Catégories de jeux ── */
  const CATEGORIES = [
    {
      id: "moba",
      name: "MOBA",
      icon: "strategy",
      color: "#a78bfa",
      glow: "rgba(167,139,250,0.3)",
      games: [
        { id: "lol", name: "League of Legends", slug: "league-of-legends" },
        { id: "dota2", name: "Dota 2", slug: "dota-2" },
      ],
    },
    {
      id: "fps",
      name: "FPS",
      icon: "target",
      color: "#f87171",
      glow: "rgba(248,113,113,0.3)",
      games: [
        { id: "cs2", name: "Counter-Strike 2", slug: "cs2" },
        { id: "valorant", name: "Valorant", slug: "valorant" },
        { id: "cod", name: "Call of Duty", slug: "call-of-duty" },
      ],
    },
    {
      id: "sport",
      name: "SPORT",
      icon: "sports_esports",
      color: "#34d399",
      glow: "rgba(52,211,153,0.3)",
      games: [
        { id: "fc25", name: "FC 25", slug: "fc-25" },
        { id: "nba2k", name: "NBA 2K", slug: "nba-2k" },
      ],
    },
    {
      id: "combat",
      name: "COMBAT",
      icon: "sports_martial_arts",
      color: "#fbbf24",
      glow: "rgba(251,191,36,0.3)",
      games: [
        { id: "sf6", name: "Street Fighter 6", slug: "street-fighter-6" },
        { id: "tekken8", name: "Tekken 8", slug: "tekken-8" },
      ],
    },
    {
      id: "mobile",
      name: "MOBILE",
      icon: "smartphone",
      color: "#38bdf8",
      glow: "rgba(56,189,248,0.3)",
      games: [
        { id: "brawlstars", name: "Brawl Stars", slug: "brawl-stars" },
        { id: "clashroyale", name: "Clash Royale", slug: "clash-royale" },
        { id: "freefire", name: "Free Fire", slug: "free-fire" },
        { id: "pubgm", name: "PUBG Mobile", slug: "pubg-mobile" },
      ],
    },
  ];

  /* ── Données statiques de tournois (fallback & démo) ── */
  const STATIC_TOURNAMENTS = {
    moba: [
      {
        name: "LEC Winter 2025",
        organizer: "Riot Games",
        game: "League of Legends",
        date: "2025-01-17",
        endDate: "2025-03-30",
        format: "LAN",
        prize: "$400,000",
        location: "Berlin, Allemagne",
        tier: "S-Tier",
        status: "upcoming",
        teams: 10,
        logo: null,
        stream: "https://lolesports.com",
        streamPlatform: "LoL Esports",
      },
      {
        name: "LCK Spring 2025",
        organizer: "Riot Games",
        game: "League of Legends",
        date: "2025-01-15",
        endDate: "2025-04-06",
        format: "LAN",
        prize: "$400,000",
        location: "Séoul, Corée du Sud",
        tier: "S-Tier",
        status: "live",
        teams: 10,
        logo: null,
        stream: "https://lolesports.com",
        streamPlatform: "LoL Esports",
      },
      {
        name: "The International 2025",
        organizer: "Valve",
        game: "Dota 2",
        date: "2025-09-15",
        endDate: "2025-09-28",
        format: "LAN",
        prize: "$15,000,000+",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 20,
        logo: null,
        stream: "https://twitch.tv/dota2ti",
        streamPlatform: "Twitch",
      },
      {
        name: "ESL Pro Tour Dota 2",
        organizer: "ESL",
        game: "Dota 2",
        date: "2025-04-10",
        endDate: "2025-04-20",
        format: "En ligne",
        prize: "$1,000,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "upcoming",
        teams: 16,
        logo: null,
        stream: null,
        streamPlatform: null,
      },
    ],
    fps: [
      {
        name: "PGL Major Copenhagen 2025",
        organizer: "PGL",
        game: "Counter-Strike 2",
        date: "2025-03-17",
        endDate: "2025-03-30",
        format: "LAN",
        prize: "$1,250,000",
        location: "Copenhague, Danemark",
        tier: "S-Tier",
        status: "live",
        teams: 24,
        logo: null,
        stream: "https://twitch.tv/pgl",
        streamPlatform: "Twitch",
      },
      {
        name: "VCT Masters 2025",
        organizer: "Riot Games",
        game: "Valorant",
        date: "2025-06-06",
        endDate: "2025-06-22",
        format: "LAN",
        prize: "$1,000,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 12,
        logo: null,
        stream: "https://twitch.tv/valorant",
        streamPlatform: "Twitch",
      },
      {
        name: "CDL Major 2025",
        organizer: "Activision",
        game: "Call of Duty",
        date: "2025-05-02",
        endDate: "2025-05-05",
        format: "LAN",
        prize: "$500,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 12,
        logo: null,
        stream: "https://youtube.com/callofduty",
        streamPlatform: "YouTube",
      },
      {
        name: "IEM Katowice 2025",
        organizer: "ESL",
        game: "Counter-Strike 2",
        date: "2025-02-04",
        endDate: "2025-02-09",
        format: "LAN",
        prize: "$1,000,000",
        location: "Katowice, Pologne",
        tier: "S-Tier",
        status: "completed",
        teams: 24,
        logo: null,
        stream: null,
        streamPlatform: null,
      },
    ],
    sport: [
      {
        name: "eChampions League 2025",
        organizer: "EA Sports",
        game: "FC 25",
        date: "2025-02-01",
        endDate: "2025-05-15",
        format: "En ligne",
        prize: "$280,000",
        location: "En ligne",
        tier: "S-Tier",
        status: "live",
        teams: 64,
        logo: null,
        stream: "https://twitch.tv/ea",
        streamPlatform: "Twitch",
      },
      {
        name: "FC Pro World Championship",
        organizer: "EA Sports",
        game: "FC 25",
        date: "2025-07-10",
        endDate: "2025-07-14",
        format: "LAN",
        prize: "$500,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 32,
        logo: null,
        stream: null,
        streamPlatform: null,
      },
      {
        name: "NBA 2K League Season 8",
        organizer: "NBA / Take-Two",
        game: "NBA 2K",
        date: "2025-04-01",
        endDate: "2025-08-15",
        format: "En ligne",
        prize: "$2,000,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "upcoming",
        teams: 24,
        logo: null,
        stream: "https://twitch.tv/nba2kleague",
        streamPlatform: "Twitch",
      },
    ],
    combat: [
      {
        name: "EVO 2025",
        organizer: "EVO / Sony",
        game: "Street Fighter 6",
        date: "2025-08-01",
        endDate: "2025-08-03",
        format: "LAN",
        prize: "$250,000+",
        location: "Las Vegas, USA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 4096,
        logo: null,
        stream: "https://twitch.tv/evo",
        streamPlatform: "Twitch",
      },
      {
        name: "Tekken World Tour Finals 2025",
        organizer: "Bandai Namco",
        game: "Tekken 8",
        date: "2025-11-15",
        endDate: "2025-11-17",
        format: "LAN",
        prize: "$200,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 32,
        logo: null,
        stream: "https://twitch.tv/tekken",
        streamPlatform: "Twitch",
      },
      {
        name: "Capcom Pro Tour 2025",
        organizer: "Capcom",
        game: "Street Fighter 6",
        date: "2025-03-01",
        endDate: "2025-12-31",
        format: "En ligne",
        prize: "$500,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "live",
        teams: 256,
        logo: null,
        stream: "https://twitch.tv/capcomfighters",
        streamPlatform: "Twitch",
      },
      {
        name: "EVO Japan 2025",
        organizer: "EVO / Sony",
        game: "Tekken 8",
        date: "2025-05-09",
        endDate: "2025-05-11",
        format: "LAN",
        prize: "$100,000",
        location: "Tokyo, Japon",
        tier: "S-Tier",
        status: "upcoming",
        teams: 2048,
        logo: null,
        stream: null,
        streamPlatform: null,
      },
    ],
    mobile: [
      {
        name: "Brawl Stars World Finals 2025",
        organizer: "Supercell",
        game: "Brawl Stars",
        date: "2025-10-24",
        endDate: "2025-10-26",
        format: "LAN",
        prize: "$1,000,000",
        location: "Helsinki, Finlande",
        tier: "S-Tier",
        status: "upcoming",
        teams: 16,
        logo: null,
        stream: "https://twitch.tv/brawlstars",
        streamPlatform: "Twitch",
      },
      {
        name: "Brawl Stars Championship Monthly",
        organizer: "Supercell",
        game: "Brawl Stars",
        date: "2025-04-12",
        endDate: "2025-04-13",
        format: "En ligne",
        prize: "$50,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "upcoming",
        teams: 32,
        logo: null,
        stream: "https://youtube.com/brawlstars",
        streamPlatform: "YouTube",
      },
      {
        name: "Clash Royale League World Finals 2025",
        organizer: "Supercell",
        game: "Clash Royale",
        date: "2025-09-05",
        endDate: "2025-09-07",
        format: "LAN",
        prize: "$500,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 16,
        logo: null,
        stream: "https://twitch.tv/clashroyale",
        streamPlatform: "Twitch",
      },
      {
        name: "Clash Royale League Challenge",
        organizer: "Supercell",
        game: "Clash Royale",
        date: "2025-05-03",
        endDate: "2025-05-04",
        format: "En ligne",
        prize: "$100,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "upcoming",
        teams: 64,
        logo: null,
        stream: null,
        streamPlatform: null,
      },
      {
        name: "Free Fire World Series 2025",
        organizer: "Garena",
        game: "Free Fire",
        date: "2025-06-14",
        endDate: "2025-06-15",
        format: "LAN",
        prize: "$2,000,000",
        location: "Jakarta, Indonésie",
        tier: "S-Tier",
        status: "upcoming",
        teams: 18,
        logo: null,
        stream: "https://youtube.com/freefire",
        streamPlatform: "YouTube",
      },
      {
        name: "Free Fire Continental Series",
        organizer: "Garena",
        game: "Free Fire",
        date: "2025-03-22",
        endDate: "2025-03-23",
        format: "En ligne",
        prize: "$300,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "live",
        teams: 24,
        logo: null,
        stream: "https://youtube.com/freefire",
        streamPlatform: "YouTube",
      },
      {
        name: "PUBG Mobile Global Championship 2025",
        organizer: "Krafton / Tencent",
        game: "PUBG Mobile",
        date: "2025-11-08",
        endDate: "2025-11-16",
        format: "LAN",
        prize: "$3,000,000",
        location: "TBA",
        tier: "S-Tier",
        status: "upcoming",
        teams: 48,
        logo: null,
        stream: "https://twitch.tv/pubgmobile",
        streamPlatform: "Twitch",
      },
      {
        name: "PUBG Mobile Super League",
        organizer: "Krafton / Tencent",
        game: "PUBG Mobile",
        date: "2025-04-18",
        endDate: "2025-04-20",
        format: "En ligne",
        prize: "$500,000",
        location: "En ligne",
        tier: "A-Tier",
        status: "upcoming",
        teams: 24,
        logo: null,
        stream: "https://youtube.com/pubgmobile",
        streamPlatform: "YouTube",
      },
    ],
  };

  /* ── États ── */
  let activeCategory = "all";
  let activeStatus = "all";
  let tournaments = {};

  /* ── Utilitaires de dates ── */
  function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString("fr-FR", {
      day: "numeric",
      month: "short",
      year: "numeric",
    });
  }

  function getStatusInfo(status, startDate, endDate) {
    const now = new Date();
    const start = new Date(startDate);
    const end = new Date(endDate);

    if (status === "completed" || end < now) {
      return {
        label: "Terminé",
        class: "gc-status--completed",
        icon: "check_circle",
      };
    } else if (status === "live" || (start <= now && end >= now)) {
      return { label: "En cours", class: "gc-status--live", icon: "pulse" };
    } else {
      return {
        label: "À venir",
        class: "gc-status--upcoming",
        icon: "schedule",
      };
    }
  }

  function getTierColor(tier) {
    switch (tier) {
      case "S-Tier":
        return {
          bg: "rgba(251,191,36,0.15)",
          border: "#fbbf24",
          text: "#fbbf24",
        };
      case "A-Tier":
        return {
          bg: "rgba(167,139,250,0.15)",
          border: "#a78bfa",
          text: "#a78bfa",
        };
      case "B-Tier":
        return {
          bg: "rgba(52,211,153,0.15)",
          border: "#34d399",
          text: "#34d399",
        };
      default:
        return { bg: "rgba(255,255,255,0.08)", border: "#666", text: "#999" };
    }
  }

  /* ── Chargement des données ── */
  async function loadTournaments() {
    // Essayer de charger via l'API PandaScore
    try {
      const resp = await fetch(
        "includes/pandascore_api.php?action=tournaments",
      );
      if (resp.ok) {
        const data = await resp.json();
        if (data && typeof data === "object" && Object.keys(data).length > 0) {
          // Fusionner avec les données statiques (l'API a priorité)
          tournaments = mergeTournaments(STATIC_TOURNAMENTS, data);
          return;
        }
      }
    } catch (e) {
      console.log(
        "[GamingCalendar] API non disponible, utilisation des données statiques",
      );
    }
    // Fallback : données statiques
    tournaments = STATIC_TOURNAMENTS;
  }

  function mergeTournaments(staticData, apiData) {
    const merged = {};
    for (const cat of Object.keys(staticData)) {
      merged[cat] = [...staticData[cat]];
      if (apiData[cat] && Array.isArray(apiData[cat])) {
        merged[cat] = [...apiData[cat], ...merged[cat]];
      }
    }
    return merged;
  }

  /* ── Rendu ── */
  function render() {
    const container = document.getElementById("gc-tournaments");
    if (!container) return;

    // Filtrer par catégorie
    const cats =
      activeCategory === "all"
        ? CATEGORIES
        : CATEGORIES.filter((c) => c.id === activeCategory);

    let html = "";

    cats.forEach((cat) => {
      const catTournaments = getFilteredTournaments(cat.id);
      if (catTournaments.length === 0) return;

      html += `
            <div class="gc-category" data-category="${cat.id}">
                <div class="gc-category__header">
                    <div class="gc-category__icon" style="--cat-color:${cat.color};--cat-glow:${cat.glow}">
                        <span class="material-symbols-outlined">${cat.icon}</span>
                    </div>
                    <div class="gc-category__info">
                        <h3 class="gc-category__name">${cat.name}</h3>
                        <span class="gc-category__count">${catTournaments.length} tournoi${catTournaments.length > 1 ? "s" : ""}</span>
                    </div>
                    <div class="gc-category__games">
                        ${cat.games.map((g) => `<span class="gc-game-tag">${g.name}</span>`).join("")}
                    </div>
                </div>
                <div class="gc-category__grid">
                    ${catTournaments.map((t) => renderTournamentCard(t, cat)).join("")}
                </div>
            </div>`;
    });

    if (!html) {
      html = `
            <div class="gc-empty">
                <span class="material-symbols-outlined gc-empty__icon">search_off</span>
                <p class="gc-empty__text">Aucun tournoi trouvé pour ces critères</p>
                <button class="gc-empty__reset" onclick="window.GamingCalendar.resetFilters()">
                    Réinitialiser les filtres
                </button>
            </div>`;
    }

    container.innerHTML = html;

    // Animer les cartes
    requestAnimationFrame(() => {
      container.querySelectorAll(".gc-card").forEach((card, i) => {
        card.style.animationDelay = `${i * 60}ms`;
        card.classList.add("gc-card--visible");
      });
    });
  }

  function getFilteredTournaments(catId) {
    let list = tournaments[catId] || [];
    if (activeStatus !== "all") {
      list = list.filter((t) => {
        const info = getStatusInfo(t.status, t.date, t.endDate);
        if (activeStatus === "live") return info.class === "gc-status--live";
        if (activeStatus === "upcoming")
          return info.class === "gc-status--upcoming";
        if (activeStatus === "completed")
          return info.class === "gc-status--completed";
        return true;
      });
    }
    // Trier : live > upcoming > completed
    const order = {
      "gc-status--live": 0,
      "gc-status--upcoming": 1,
      "gc-status--completed": 2,
    };
    list.sort((a, b) => {
      const sa = getStatusInfo(a.status, a.date, a.endDate);
      const sb = getStatusInfo(b.status, b.date, b.endDate);
      return (order[sa.class] || 9) - (order[sb.class] || 9);
    });
    return list;
  }

  function renderTournamentCard(t, cat) {
    const status = getStatusInfo(t.status, t.date, t.endDate);
    const tier = getTierColor(t.tier);
    const dateDisplay =
      t.endDate && t.endDate !== t.date
        ? `${formatDate(t.date)} → ${formatDate(t.endDate)}`
        : formatDate(t.date);

    const formatIcon = t.format === "LAN" ? "lan" : "cloud";
    const formatLabel = t.format || "TBA";
    const formatClass =
      t.format === "LAN" ? "gc-card__format--lan" : "gc-card__format--online";

    const streamIcon =
      t.streamPlatform && t.streamPlatform.toLowerCase().includes("youtube")
        ? "smart_display"
        : "live_tv";

    return `
        <div class="gc-card gc-card--visible" data-status="${status.class}">
            <div class="gc-card__scanline"></div>
            <div class="gc-card__header">
                <div class="gc-card__game-badge" style="background:${cat.glow};color:${cat.color};">
                    ${t.game}
                </div>
                <div class="gc-card__tier" style="background:${tier.bg};border-color:${tier.border};color:${tier.text};">
                    ${t.tier}
                </div>
            </div>
            <h4 class="gc-card__name">${t.name}</h4>
            ${
              t.organizer
                ? `<div class="gc-card__organizer">
                    <span class="material-symbols-outlined">business</span>
                    <span>${t.organizer}</span>
                </div>`
                : ""
            }
            <div class="gc-card__date">
                <span class="material-symbols-outlined">calendar_today</span>
                ${dateDisplay}
            </div>
            <div class="gc-card__meta">
                ${
                  t.format
                    ? `<div class="gc-card__format ${formatClass}">
                    <span class="material-symbols-outlined">${formatIcon}</span>
                    <span>${formatLabel}</span>
                </div>`
                    : ""
                }
                ${
                  t.prize
                    ? `<div class="gc-card__prize">
                    <span class="material-symbols-outlined">payments</span>
                    <span>${t.prize}</span>
                </div>`
                    : ""
                }
                ${
                  t.teams
                    ? `<div class="gc-card__teams">
                    <span class="material-symbols-outlined">groups</span>
                    <span>${t.teams} équipes</span>
                </div>`
                    : ""
                }
            </div>
            <div class="gc-card__footer">
                <div class="gc-card__footer-left">
                    <span class="gc-status ${status.class}">
                        <span class="material-symbols-outlined">${status.icon}</span>
                        ${status.label}
                    </span>
                    ${
                      t.location
                        ? `<span class="gc-card__location">
                            <span class="material-symbols-outlined">location_on</span>
                            ${t.location}
                        </span>`
                        : ""
                    }
                </div>
                ${
                  t.stream
                    ? `<a href="${t.stream}" target="_blank" rel="noopener" class="gc-card__stream">
                        <span class="material-symbols-outlined">${streamIcon}</span>
                        ${t.streamPlatform || "Regarder"}
                    </a>`
                    : ""
                }
            </div>
        </div>`;
  }

  /* ── Navigation catégories ── */
  function renderCategoryTabs() {
    const nav = document.getElementById("gc-category-nav");
    if (!nav) return;

    let html = `
        <button class="gc-tab ${activeCategory === "all" ? "gc-tab--active" : ""}" data-cat="all">
            <span class="material-symbols-outlined">dashboard</span>
            Tous
            <span class="gc-tab__count">${countAll()}</span>
        </button>`;

    CATEGORIES.forEach((cat) => {
      const count = (tournaments[cat.id] || []).length;
      html += `
            <button class="gc-tab ${activeCategory === cat.id ? "gc-tab--active" : ""}" data-cat="${cat.id}" style="--tab-color:${cat.color}">
                <span class="material-symbols-outlined">${cat.icon}</span>
                ${cat.name}
                <span class="gc-tab__count">${count}</span>
            </button>`;
    });

    nav.innerHTML = html;

    // Event listeners
    nav.querySelectorAll(".gc-tab").forEach((tab) => {
      tab.addEventListener("click", () => {
        activeCategory = tab.dataset.cat;
        renderCategoryTabs();
        render();
      });
    });
  }

  function renderStatusFilters() {
    const nav = document.getElementById("gc-status-nav");
    if (!nav) return;

    const statuses = [
      { id: "all", label: "Tous", icon: "list" },
      { id: "live", label: "En cours", icon: "pulse" },
      { id: "upcoming", label: "À venir", icon: "schedule" },
      { id: "completed", label: "Terminés", icon: "check_circle" },
    ];

    nav.innerHTML = statuses
      .map(
        (s) => `
            <button class="gc-filter-btn ${activeStatus === s.id ? "gc-filter-btn--active" : ""}" data-status="${s.id}">
                <span class="material-symbols-outlined">${s.icon}</span>
                ${s.label}
            </button>
        `,
      )
      .join("");

    nav.querySelectorAll(".gc-filter-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        activeStatus = btn.dataset.status;
        renderStatusFilters();
        render();
      });
    });
  }

  function countAll() {
    return Object.values(tournaments).reduce((sum, arr) => sum + arr.length, 0);
  }

  /* ── Statistiques en haut ── */
  function renderStats() {
    const container = document.getElementById("gc-stats");
    if (!container) return;

    let live = 0,
      upcoming = 0,
      totalPrize = 0;
    Object.values(tournaments).forEach((list) => {
      list.forEach((t) => {
        const info = getStatusInfo(t.status, t.date, t.endDate);
        if (info.class === "gc-status--live") live++;
        if (info.class === "gc-status--upcoming") upcoming++;
        const prizeNum = parseInt((t.prize || "0").replace(/[^0-9]/g, ""), 10);
        totalPrize += prizeNum;
      });
    });

    container.innerHTML = `
            <div class="gc-stat">
                <span class="gc-stat__value gc-stat__value--live">${live}</span>
                <span class="gc-stat__label">En cours</span>
            </div>
            <div class="gc-stat">
                <span class="gc-stat__value">${upcoming}</span>
                <span class="gc-stat__label">À venir</span>
            </div>
            <div class="gc-stat">
                <span class="gc-stat__value">${countAll()}</span>
                <span class="gc-stat__label">Tournois</span>
            </div>
            <div class="gc-stat">
                <span class="gc-stat__value gc-stat__value--prize">$${(totalPrize / 1000000).toFixed(1)}M+</span>
                <span class="gc-stat__label">Prize Pool Total</span>
            </div>
        `;
  }

  /* ── Initialisation ── */
  async function init() {
    const section = document.getElementById("gaming-calendar");
    if (!section) return;

    await loadTournaments();
    renderStats();
    renderCategoryTabs();
    renderStatusFilters();
    render();
  }

  /* ── API publique ── */
  window.GamingCalendar = {
    init,
    resetFilters() {
      activeCategory = "all";
      activeStatus = "all";
      renderCategoryTabs();
      renderStatusFilters();
      render();
    },
  };

  // Auto-init quand le DOM est prêt
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
