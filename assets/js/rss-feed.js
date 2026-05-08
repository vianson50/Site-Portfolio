/**
 * ========================================
 * RSS FEED — Journal du Coin / Crypto News
 * Native RSS parser with cyberpunk design
 * ========================================
 */

(function () {
  "use strict";

  let feedData = null;
  let visibleCount = 6;
  const PER_PAGE = 6;

  /* ── Time ago helper ── */
  function timeAgo(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return "à l'instant";
    if (diff < 3600) return `${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}j`;
    return d.toLocaleDateString("fr-FR", { day: "numeric", month: "short" });
  }

  /* ── Format date ── */
  function formatDate(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString("fr-FR", {
      day: "numeric",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  /* ── Render a single feed card ── */
  function renderItem(item, index) {
    const hasImage = item.image && item.image.length > 10;
    const domain = item.link
      ? new URL(item.link).hostname.replace("www.", "")
      : "";

    return `
      <a href="${item.link || "#"}" target="_blank" rel="noopener" class="rf-card" style="animation-delay:${index * 60}ms">
        <div class="rf-card__scanline"></div>
        ${
          hasImage
            ? `
        <div class="rf-card__thumb">
          <img src="${item.image}" alt="" loading="lazy" onerror="this.parentElement.style.display='none'">
        </div>`
            : ""
        }
        <div class="rf-card__body">
          <div class="rf-card__meta">
            <span class="rf-card__time">
              <span class="material-symbols-outlined">schedule</span>
              ${timeAgo(item.date)}
            </span>
            ${domain ? `<span class="rf-card__domain">${domain}</span>` : ""}
          </div>
          <h4 class="rf-card__title">${item.title}</h4>
          ${item.desc ? `<p class="rf-card__desc">${item.desc}</p>` : ""}
        </div>
        <div class="rf-card__arrow">
          <span class="material-symbols-outlined">arrow_forward</span>
        </div>
      </a>`;
  }

  /* ── Render the full feed section ── */
  function renderFeed() {
    const container = document.getElementById("rf-feed");
    if (!container || !feedData) return;

    const items = feedData.items || [];
    const showing = items.slice(0, visibleCount);
    const hasMore = visibleCount < items.length;

    container.innerHTML = showing
      .map((item, i) => renderItem(item, i))
      .join("");

    // Load more button
    const moreBtn = document.getElementById("rf-load-more");
    if (moreBtn) {
      moreBtn.style.display = hasMore ? "flex" : "none";
    }

    // Update counter
    const counter = document.getElementById("rf-count");
    if (counter) {
      counter.textContent = `${Math.min(visibleCount, items.length)}/${items.length}`;
    }

    // Animate cards
    requestAnimationFrame(() => {
      container.querySelectorAll(".rf-card").forEach((card) => {
        card.classList.add("rf-card--visible");
      });
    });
  }

  /* ── Load more items ── */
  function loadMore() {
    visibleCount += PER_PAGE;
    renderFeed();
  }

  /* ── Fetch from proxy ── */
  async function fetchFeed(forceRefresh = false) {
    const container = document.getElementById("rf-feed");
    if (!container) return;

    // Loading state
    container.innerHTML = `
      <div class="rf-loading">
        <span class="material-symbols-outlined rf-loading__icon">sync</span>
        <span>Chargement du flux…</span>
      </div>`;

    try {
      const url = forceRefresh
        ? "/includes/rss_proxy.php?action=refresh"
        : "/includes/rss_proxy.php?action=feed";
      const resp = await fetch(url);
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();

      if (data.success === false) {
        throw new Error(data.error || "Erreur API");
      }

      feedData = data;
      visibleCount = 6;
      renderFeed();

      // Update source label
      const sourceEl = document.getElementById("rf-source");
      if (sourceEl && data.source) {
        sourceEl.textContent = data.source;
      }

      console.log(`[RSS Feed] ✅ ${data.count} articles chargés`);
    } catch (err) {
      console.error("[RSS Feed] ❌ Erreur:", err);
      container.innerHTML = `
        <div class="rf-error">
          <span class="material-symbols-outlined" style="font-size:2rem;color:#ff6b6b;">cloud_off</span>
          <p>Flux indisponible</p>
          <p style="font-size:12px;color:var(--outline-variant);">${err.message}</p>
          <button onclick="window.RssFeed.refresh()" class="rf-retry-btn">
            <span class="material-symbols-outlined" style="font-size:16px;">refresh</span>
            Réessayer
          </button>
        </div>`;
    }
  }

  /* ── Init ── */
  async function init() {
    const section = document.getElementById("rss-feed-section");
    if (!section) return;
    await fetchFeed();
  }

  /* ── Public API ── */
  window.RssFeed = {
    init,
    loadMore,
    refresh() {
      console.log("[RSS Feed] 🔄 Rafraîchissement…");
      fetchFeed(true);
    },
  };

  // Auto-init
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

/**
 * ========================================
 * GAMING NEWS TICKER — Gamekult
 * Scrolling ticker bar under the header
 * ========================================
 */
(function () {
  "use strict";

  let tickerItems = [];

  async function initTicker() {
    const el = document.getElementById("ticker-content");
    if (!el) return;

    try {
      const resp = await fetch("/includes/rss_proxy.php?action=ticker");
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();

      if (data.success === false || !data.items || data.items.length === 0) {
        throw new Error(data.error || "No items");
      }

      tickerItems = data.items;
      renderTicker();
      console.log(`[Ticker] ✅ ${data.count} actus Gamekult chargées`);
    } catch (err) {
      console.warn("[Ticker] ❌ Erreur:", err);
      el.innerHTML =
        '<span class="news-ticker__item">Gamekult — Flux indisponible</span>';
    }
  }

  function renderTicker() {
    const el = document.getElementById("ticker-content");
    if (!el || tickerItems.length === 0) return;

    // Build items HTML — duplicate 3x for seamless loop
    const items = tickerItems
      .map((item) => {
        const title = item.title || "";
        const link = item.link || "#";
        const source = (item.link || "").includes("gamekult")
          ? "Gamekult"
          : (item.link || "").includes("jeuxvideo")
            ? "JV"
            : "";
        return `<a href="${link}" target="_blank" rel="noopener" class="news-ticker__item">${source ? `<span class="news-ticker__src">${source}</span>` : ""}${title}</a>`;
      })
      .join("");

    el.innerHTML = items + items + items;
    el.classList.add("news-ticker__content--scroll");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTicker);
  } else {
    initTicker();
  }
})();

/**
 * ========================================
 * COIN TRACKER — CoinGecko RSS
 * Displays top crypto coins as cards
 * Data pre-parsed by PHP (symbol, mcap, volume, change, ath)
 * ========================================
 */
(function () {
  "use strict";

  let coinsData = null;

  /* ── Render a single coin card ── */
  function renderCoin(item, index) {
    const symbol = item.symbol || "???";
    const name = item.title || symbol;
    const rank = item.rank || "";
    const mcap = item.mcap || "";
    const vol = item.volume || "";
    const volDir = item.volDir || "";
    const change = item.change || "";
    const ath = item.ath || "";
    const vsAth = item.vsAth || "";
    const supply = item.supply || "";
    const maxSupply = item.maxSupply || "";
    const exchange = item.exchange || "";
    const pair = item.pair || "";
    const desc = item.desc || "";
    const hasImage = item.image && item.image.length > 10;

    const isPositive = change.startsWith("+");
    const changeColor = isPositive ? "#61dd98" : "#ff6b6b";
    const changeBg = isPositive
      ? "rgba(97,221,152,0.1)"
      : "rgba(255,107,107,0.1)";
    const changeIcon = isPositive ? "trending_up" : "trending_down";

    const isVolPositive = volDir.startsWith("+");
    const volColor = isVolPositive ? "#61dd98" : "#ff6b6b";

    return `
      <a href="${item.link || "#"}" target="_blank" rel="noopener" class="ct-card" style="animation-delay:${index * 80}ms">
        <div class="ct-card__scanline"></div>

        <!-- Top bar: rank + exchange -->
        <div class="ct-card__topbar">
          ${rank ? `<span class="ct-card__rank">#${rank}</span>` : ""}
          <span class="ct-card__spacer"></span>
          ${exchange ? `<span class="ct-card__exchange">${exchange}</span>` : ""}
        </div>

        <!-- Header: icon + identity + change -->
        <div class="ct-card__header">
          ${hasImage ? `<img src="${item.image}" alt="${symbol}" class="ct-card__icon" onerror="this.parentElement.innerHTML='<div class=\'ct-card__icon-fallback\'>${symbol.substring(0, 2)}</div>'">` : `<div class="ct-card__icon-fallback">${symbol.substring(0, 2)}</div>`}
          <div class="ct-card__identity">
            <span class="ct-card__name">${name}</span>
            <span class="ct-card__symbol">${symbol}${pair ? ` / ${pair.split("/")[1]}` : ""}</span>
          </div>
          ${
            change
              ? `<span class="ct-card__change" style="color:${changeColor};background:${changeBg}">
            <span class="material-symbols-outlined" style="font-size:14px;">${changeIcon}</span>
            ${change} <small>7d</small>
          </span>`
              : ""
          }
        </div>

        <!-- Description -->
        ${desc ? `<p class="ct-card__desc">${desc}</p>` : ""}

        <!-- Stats grid -->
        <div class="ct-card__stats">
          ${
            mcap
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">Market Cap</span>
            <span class="ct-card__cell-value">${mcap}</span>
          </div>`
              : ""
          }
          ${
            vol
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">Volume 24h</span>
            <span class="ct-card__cell-value">${vol} ${volDir ? `<span style="color:${volColor};font-size:9px;margin-left:4px;">${volDir}</span>` : ""}</span>
          </div>`
              : ""
          }
          ${
            ath
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">ATH</span>
            <span class="ct-card__cell-value">${ath}</span>
          </div>`
              : ""
          }
          ${
            vsAth
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">vs ATH</span>
            <span class="ct-card__cell-value" style="color:#ff6b6b;">${vsAth}</span>
          </div>`
              : ""
          }
          ${
            supply
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">Circulation</span>
            <span class="ct-card__cell-value">${supply}${maxSupply ? ` / ${maxSupply}` : ""}</span>
          </div>`
              : ""
          }
          ${
            pair
              ? `<div class="ct-card__cell">
            <span class="ct-card__cell-label">Paire active</span>
            <span class="ct-card__cell-value ct-card__cell-value--pair">${pair}</span>
          </div>`
              : ""
          }
        </div>

        <!-- Footer -->
        <div class="ct-card__footer">
          <span class="ct-card__cta">Voir sur CoinGecko
            <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
          </span>
        </div>
      </a>`;
  }

  /* ── Render all coins ── */
  function renderCoins() {
    const container = document.getElementById("ct-coins");
    if (!container || !coinsData) return;

    const items = coinsData.items || [];
    container.innerHTML = items.map((item, i) => renderCoin(item, i)).join("");

    // Update counter
    const counter = document.getElementById("ct-count");
    if (counter) {
      counter.textContent = items.length;
    }

    // Animate
    requestAnimationFrame(() => {
      container.querySelectorAll(".ct-card").forEach((card) => {
        card.classList.add("ct-card--visible");
      });
    });
  }

  /* ── Fetch coins ── */
  async function fetchCoins(forceRefresh = false) {
    const container = document.getElementById("ct-coins");
    if (!container) return;

    container.innerHTML = `
      <div class="rf-loading">
        <span class="material-symbols-outlined rf-loading__icon">sync</span>
        <span>Chargement des cours…</span>
      </div>`;

    try {
      const url = forceRefresh
        ? "/includes/rss_proxy.php?action=coins_refresh"
        : "/includes/rss_proxy.php?action=coins";
      const resp = await fetch(url);
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();

      if (data.success === false) {
        throw new Error(data.error || "Erreur API");
      }

      coinsData = data;
      renderCoins();

      const sourceEl = document.getElementById("ct-source");
      if (sourceEl && data.source) {
        sourceEl.textContent = data.source;
      }

      console.log(`[CoinTracker] \u2705 ${data.count} coins charg\u00e9s`);
    } catch (err) {
      console.error("[CoinTracker] \u274c Erreur:", err);
      container.innerHTML = `
        <div class="rf-error">
          <span class="material-symbols-outlined" style="font-size:2rem;color:#ff6b6b;">cloud_off</span>
          <p>Donn\u00e9es indisponibles</p>
          <p style="font-size:12px;color:var(--outline-variant);">${err.message}</p>
          <button onclick="window.CoinTracker.refresh()" class="rf-retry-btn">
            <span class="material-symbols-outlined" style="font-size:16px;">refresh</span>
            R\u00e9essayer
          </button>
        </div>`;
    }
  }

  /* ── Init ── */
  async function init() {
    const section = document.getElementById("coin-tracker-section");
    if (!section) return;
    await fetchCoins();
  }

  /* ── Public API ── */
  window.CoinTracker = {
    init,
    refresh() {
      console.log("[CoinTracker] \U0001f504 Rafra\u00eechissement…");
      fetchCoins(true);
    },
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
