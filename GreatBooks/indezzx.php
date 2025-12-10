<?php
// Simple connection (edit user/password/dbname to match XAMPP/MySQL)
$host = "localhost";
$user = "root";
$pass = "Karl09295272324@";          // default is empty in XAMPP
$db   = "donquixote_blog";          // your schema name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load quotes from database
$result = $conn->query("SELECT text, speaker, focus FROM quotes");
$quotes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {

        $quotes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Don Quixote | Knight’s Codex</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg-main: #020617;
      --bg-overlay: rgba(3,7,18,0.92);
      --border-soft: #1f2937;
      --accent: #facc15;
      --accent-2: #38bdf8;
      --text-main: #e5e7eb;
      --text-muted: #9ca3af;
      --danger: #f97373;
      --success: #4ade80;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
    }

    body {
      font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--text-main);
      overflow: hidden;
      background: #000;
    }

    @keyframes glow-pulse {
      0%   { box-shadow: 0 0 0 0 rgba(56,189,248,0.55); }
      70%  { box-shadow: 0 0 0 12px rgba(56,189,248,0); }
      100% { box-shadow: 0 0 0 0 rgba(56,189,248,0); }
    }

    /* ROOT MAP WRAPPER */
    .map-root {
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
      background-color: #020617;
    }

    /* REALISTIC MAP BACKGROUND
       - Replace url('map-texture.jpg') with your own map image.
       - Recommended: a parchment or stylized world map. */
    .map-background {
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 10% 10%, rgba(15,23,42,0.4) 0, transparent 40%),
        radial-gradient(circle at 80% 80%, rgba(15,23,42,0.4) 0, transparent 40%),
        url('maps.jpg') center/cover no-repeat;
      filter: saturate(1.1) contrast(1.1);
      z-index: 0;
    }

    /* Top HUD (logo + mini nav) floating over map */
    .top-hud {
      position: absolute;
      top: 10px;
      left: 10px;
      right: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 10px;
      border-radius: 999px;
      background: radial-gradient(circle at top, rgba(15,23,42,0.9), rgba(3,7,18,0.9));
      border: 1px solid rgba(31,41,55,0.95);
      backdrop-filter: blur(12px);
      z-index: 5;
    }

    .logo-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logo-icon {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background:
        radial-gradient(circle at 20% 15%, #facc15 0, #f97316 25%, transparent 55%),
        radial-gradient(circle at 80% 80%, #22d3ee 0, #0f172a 55%);
      border: 1px solid rgba(148,163,184,0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      box-shadow: 0 0 18px rgba(250,204,21,0.4);
      font-family: "Orbitron", system-ui;
    }

    .logo-text-main {
      font-family: "Orbitron", system-ui;
      font-weight: 600;
      letter-spacing: 0.1em;
      font-size: 0.78rem;
      text-transform: uppercase;
    }

    .logo-text-sub {
      font-size: 0.7rem;
      color: var(--text-muted);
    }

    .top-hud-buttons {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .hud-icon-btn {
      width: 30px;
      height: 30px;
      border-radius: 999px;
      border: 1px solid rgba(55,65,81,0.9);
      background: rgba(15,23,42,0.9);
      color: var(--text-main);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
    }

    .hud-icon-btn.small-label {
      padding: 0 8px;
      width: auto;
      font-size: 0.74rem;
      gap: 4px;
    }

    .hud-icon-btn:hover {
      background: rgba(15,23,42,1);
      transform: translateY(-1px);
      box-shadow: 0 3px 8px rgba(0,0,0,0.6);
    }

    /* PLAYER BAR OVER MAP (bottom center) */
    .player-bar {
      position: absolute;
      left: 50%;
      bottom: 10px;
      transform: translateX(-50%);
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 7px 12px;
      border-radius: 999px;
      background: radial-gradient(circle at top, rgba(15,23,42,0.95), rgba(3,7,18,0.95));
      border: 1px solid rgba(31,41,55,0.95);
      box-shadow: 0 10px 26px rgba(0,0,0,0.9);
      backdrop-filter: blur(12px);
      z-index: 5;
      max-width: 95%;
    }

    .player-avatar {
      width: 38px;
      height: 38px;
      border-radius: 999px;
      border: 2px solid rgba(250,204,21,0.9);
      background:
        radial-gradient(circle at 25% 20%, #facc15 0, #fb923c 28%, transparent 54%),
        radial-gradient(circle at 70% 80%, #38bdf8 0, #020617 60%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 16px rgba(234,179,8,0.4);
      font-size: 1.2rem;
      animation: glow-pulse 3.5s infinite;
      flex-shrink: 0;
    }

    .player-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }

    .player-name {
      font-size: 0.78rem;
      font-weight: 600;
    }

    .player-class {
      font-size: 0.7rem;
      color: var(--text-muted);
    }

    .hud-level-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.7rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .xp-bar {
      width: 100%;
      height: 7px;
      border-radius: 999px;
      background: #020617;
      border: 1px solid #1f2937;
      overflow: hidden;
      margin-top: 2px;
    }

    .xp-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #22c55e, #facc15);
      transition: width 0.3s ease-out;
    }

    .badges-inline {
      display: flex;
      gap: 4px;
      align-items: center;
      margin-top: 2px;
      font-size: 0.68rem;
      color: var(--text-muted);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .badge-pill {
      padding: 1px 6px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.8);
      background: rgba(15,23,42,0.9);
      font-size: 0.68rem;
      color: var(--text-main);
      white-space: nowrap;
    }

    /* MAIN MAP PLAYFIELD (full screen behind HUD) */
    .map-playfield {
      position: absolute;
      inset: 0;
      z-index: 1;
      overflow: hidden;
    }

    /* This inner layer is what we pan/zoom.
       JS changes its transform to simulate camera movement. */
    .map-layer {
      position: absolute;
      width: 160%;
      height: 160%;
      left: -30%;
      top: -30%;
      transition: transform 0.6s ease;
      transform-origin: center;
      /* Slight overlay on top of background to add depth */
      background:
        radial-gradient(circle at 20% 30%, rgba(15,23,42,0.45) 0, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(15,23,42,0.45) 0, transparent 50%);
    }
    
    /* ZONES: clickable islands / buildings */
    .map-zone {
      position: absolute;
      padding: 8px 10px;
      min-width: 130px;
      border-radius: 16px;
      border: 1px solid rgba(55,65,81,0.9);
      background: radial-gradient(circle at top, rgba(15,23,42,0.96), rgba(3,7,18,1));
      color: var(--text-main);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      font-size: 0.82rem;
      box-shadow: 0 10px 24px rgba(0,0,0,0.85);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .map-zone-icon {
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .map-zone-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .map-zone-label {
      font-weight: 600;
      font-size: 0.8rem;
    }

    .map-zone-sub {
      font-size: 0.72rem;
      color: var(--text-muted);
    }

    .map-zone:hover {
      transform: translateY(-5px) scale(1.06);
      border-color: rgba(250,204,21,0.95);
      box-shadow: 0 18px 40px rgba(0,0,0,0.95);
      background: radial-gradient(circle at top, rgba(30,64,175,0.7), rgba(3,7,18,1));
    }

    .map-zone.active {
      border-color: rgba(56,189,248,0.95);
      box-shadow: 0 0 18px rgba(56,189,248,0.7);
    }

    /* On very small screens, zones become stacked at bottom part of map */
    @media (max-width: 700px) {
      .map-layer {
        width: 140%;
        height: 140%;
        left: -20%;
        top: -20%;
      }
      .map-zone {
        font-size: 0.78rem;
        min-width: 120px;
      }
    }

    /* ZONES VISITED LABEL (small text near top-right) */
    .zone-progress-label {
      position: absolute;
      top: 50px;
      right: 16px;
      z-index: 5;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 0.72rem;
      background: radial-gradient(circle at top, rgba(15,23,42,0.9), rgba(3,7,18,0.9));
      border: 1px solid rgba(31,41,55,0.9);
      color: var(--text-muted);
      backdrop-filter: blur(8px);
    }

    /* ZONE CONTENT MODAL (popout over map) */

    .zone-modal {
      position: fixed;
      inset: 0;
      z-index: 10;
      display: none;
    }

    .zone-modal.open {
      display: block;
    }

    .zone-modal-backdrop {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at center, rgba(15,23,42,0.6), rgba(0,0,0,0.8));
      backdrop-filter: blur(6px);
    }

    .zone-modal-panel {
      position: absolute;
      left: 50%;
      top: 50%;
      width: min(900px, 94vw);
      height: min(80vh, 540px);
      transform: translate(-50%, -50%);
      background: radial-gradient(circle at top, rgba(15,23,42,0.98), rgba(3,7,18,0.98));
      border-radius: 18px;
      border: 1px solid rgba(31,41,55,0.95);
      box-shadow: 0 18px 50px rgba(0,0,0,0.9);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .zone-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 10px 14px;
      border-bottom: 1px solid rgba(31,41,55,0.95);
    }

    .zone-modal-title {
      font-family: "Orbitron", system-ui;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.14em;
    }

    .zone-modal-sub {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .zone-modal-header-left {
      display: flex;
      flex-direction: column;
    }

    .zone-modal-actions {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .btn-ghost {
      border-radius: 999px;
      border: 1px solid rgba(55,65,81,0.95);
      background: rgba(15,23,42,0.9);
      color: var(--text-main);
      padding: 5px 9px;
      font-size: 0.76rem;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      cursor: pointer;
      transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
    }

    .btn-ghost:hover {
      background: rgba(15,23,42,1);
      transform: translateY(-1px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.6);
    }

    .zone-modal-body {
      padding: 10px 14px 12px;
      overflow-y: auto;
      font-size: 0.86rem;
    }

    /* SECTION STYLING (reuse from original) */

    main section {
      display: none;
      padding-top: 4px;
    }

    main section.active {
      display: block;
    }

    h2 {
      font-size: 1rem;
      margin-bottom: 8px;
      border-left: 3px solid var(--accent);
      padding-left: 8px;
      font-family: "Orbitron", system-ui;
    }

    p {
      font-size: 0.86rem;
      color: var(--text-main);
    }

    .hint {
      font-size: 0.76rem;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    .card {
      border-radius: 12px;
      border: 1px solid var(--border-soft);
      background: radial-gradient(circle at top, rgba(15,23,42,0.96), rgba(2,6,23,0.98));
      padding: 10px 11px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.7);
      position: relative;
      overflow: hidden;
      transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }

    .card::before {
      content: "";
      position: absolute;
      inset: -40%;
      background:
        conic-gradient(from 180deg, transparent 0 35%, rgba(248,250,252,0.18) 40%, transparent 60%);
      mix-blend-mode: soft-light;
      opacity: 0;
      transition: opacity 0.2s;
      pointer-events: none;
    }

    .card:hover::before {
      opacity: 1;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 40px rgba(0,0,0,0.9);
      border-color: rgba(250,204,21,0.7);
    }

    .card h3 {
      font-size: 0.9rem;
      color: var(--accent);
      margin-bottom: 4px;
    }

    .tag {
      display: inline-block;
      padding: 2px 7px;
      border-radius: 999px;
      border: 1px solid rgba(55,65,81,0.9);
      font-size: 0.7rem;
      color: var(--text-muted);
      margin-right: 4px;
      margin-top: 4px;
    }

    .chip-row {
      margin-top: 8px;
    }

    .chip {
      display: inline-block;
      padding: 4px 9px;
      border-radius: 999px;
      border: 1px solid rgba(34,197,94,0.7);
      font-size: 0.78rem;
      color: #bbf7d0;
      background: rgba(22,163,74,0.12);
      margin: 2px 4px 0 0;
    }

    .timeline-item {
      border-left: 2px solid var(--accent);
      padding-left: 10px;
      margin-left: 4px;
      margin-bottom: 9px;
      position: relative;
      font-size: 0.86rem;
    }

    .timeline-item::before {
      content: "";
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--accent);
      left: -5px;
      top: 2px;
      box-shadow: 0 0 8px rgba(250,204,21,0.8);
    }

    .timeline-year {
      font-size: 0.8rem;
      color: #fde68a;
      font-weight: 600;
      margin-bottom: 2px;
    }

    .quote-box {
      margin-top: 12px;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid rgba(129,140,248,0.9);
      background: radial-gradient(circle at top left, rgba(129,140,248,0.18), rgba(15,23,42,0.98));
      font-size: 0.9rem;
    }

    .quote-meta {
      margin-top: 6px;
      font-size: 0.8rem;
      color: #c7d2fe;
    }

    .btn-primary {
      margin-top: 10px;
      padding: 7px 13px;
      border-radius: 999px;
      border: 1px solid rgba(129,140,248,0.9);
      background: linear-gradient(90deg, #4f46e5, #22d3ee);
      color: #f9fafb;
      cursor: pointer;
      font-size: 0.8rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 10px 24px rgba(15,23,42,0.9);
      transition: transform 0.1s, box-shadow 0.1s;
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 28px rgba(15,23,42,1);
    }

    .quote-stats {
      margin-top: 8px;
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    .quote-stats span {
      margin-right: 10px;
    }

    .quiz-card {
      margin-top: 14px;
      padding: 11px;
      border-radius: 12px;
      border: 1px solid rgba(45,212,191,0.75);
      background: radial-gradient(circle at top, rgba(15,23,42,0.96), rgba(2,6,23,1));
    }

    .quiz-card h3 {
      font-size: 0.92rem;
      margin-bottom: 4px;
      color: #5ef2d9;
    }

    .quiz-q {
      font-size: 0.86rem;
      margin-bottom: 8px;
    }

    .quiz-options {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .quiz-option {
      padding: 5px 10px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.9);
      background: rgba(15,23,42,0.95);
      font-size: 0.78rem;
      color: var(--text-main);
      cursor: pointer;
      transition: background 0.12s, transform 0.08s, border-color 0.12s;
    }

    .quiz-option:hover {
      background: rgba(45,212,191,0.14);
      transform: translateY(-1px);
    }

    .quiz-option.correct-choice {
      border-color: var(--success);
      background: rgba(22,163,74,0.16);
    }

    .quiz-option.wrong-choice {
      border-color: var(--danger);
      background: rgba(248,113,113,0.1);
    }

    .quiz-feedback {
      margin-top: 6px;
      font-size: 0.8rem;
    }

    .quiz-feedback.good {
      color: #bbf7d0;
    }

    .quiz-feedback.bad {
      color: #fecaca;
    }

    footer {
      position: absolute;
      bottom: 4px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 0.7rem;
      color: var(--text-muted);
      text-align: center;
      z-index: 3;
      opacity: 0.7;
      pointer-events: none;
      padding: 0 10px;
    }

    @media (max-width: 600px) {
      .top-hud {
        flex-wrap: wrap;
        row-gap: 4px;
      }
      .zone-modal-panel {
        width: 96vw;
        height: 82vh;
      }
    }
    .map-zone.visited::after {
  content: "✓";
  position: absolute;
  top: -6px;
  right: -6px;
  width: 16px;
  height: 16px;
  border-radius: 999px;
  background: #22c55e;
  color: #022c22;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 8px rgba(34,197,94,0.8);
}

    .xp-float {
  position: absolute;
  left: 50%;
  bottom: 70px;
  transform: translateX(-50%);
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(34,197,94,0.18);
  border: 1px solid rgba(34,197,94,0.7);
  color: #bbf7d0;
  font-size: 0.8rem;
  pointer-events: none;
  opacity: 0;
  transition: transform 0.4s ease-out, opacity 0.4s ease-out;
}

.xp-float.show {
  opacity: 1;
  transform: translate(-50%, -18px);
}

  </style>
</head>
<body>

<div class="map-root">
  <div class="map-background"></div>

  <!-- TOP HUD (logo + mini nav) -->
  <header class="top-hud">
    <div class="logo-wrap">
      <div class="logo-icon">DQ</div>
      <div>
        <div class="logo-text-main">Knight’s Codex</div>
        <div class="logo-text-sub">Don Quixote · Map of Zones</div>
      </div>
    </div>

    <div class="top-hud-buttons">
      <button class="hud-icon-btn" id="hudRecenter" title="Recenter map">🗺️</button>
      <button class="hud-icon-btn small-label" id="hudBackToMap">
        <span>🏠</span><span>World</span>
      </button>
      <button class="hud-icon-btn" title="Settings (placeholder)">⚙️</button>
    </div>
  </header>

  <!-- Zones visited label -->
  <div class="zone-progress-label" id="sectionCountLabel">
    0 zones visited
  </div>

  <!-- MAIN MAP PLAYFIELD -->
  <div class="map-playfield">
    <!-- MAP LAYER:
         - JS pans/zooms this element by changing transform.
         - Each .map-zone is positioned with top/left in %. -->
    <div class="map-layer" id="mapLayer">

      <!-- Overview Zone -->
      <button
        class="map-zone"
        data-zone="overview"
        data-label="Story Zone · Overview"
        data-zoom="1.4"
        data-offset-x="-12"
        data-offset-y="-8"
        style="top: 33%; left: 22%;"
      >
        <span class="map-zone-icon">📜</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Overview</span>
          <span class="map-zone-sub">Main quest</span>
        </span>
      </button>

      <!-- Author Zone -->
      <button
        class="map-zone"
        data-zone="author"
        data-label="Author Zone · Miguel de Cervantes"
        data-zoom="1.5"
        data-offset-x="-18"
        data-offset-y="-2"
        style="top: 35%; left: 59%;"
      >
        <span class="map-zone-icon">✍️</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Author</span>
          <span class="map-zone-sub">Creator lore</span>
        </span>
      </button>

      <!-- Characters Zone -->
      <button
        class="map-zone"
        data-zone="characters"
        data-label="Party Zone · Characters"
        data-zoom="1.5"
        data-offset-x="8"
        data-offset-y="-16"
        style="top: 60%; left: 70%;"
      >
        <span class="map-zone-icon">🧑‍🤝‍🧑</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Characters</span>
          <span class="map-zone-sub">Party roster</span>
        </span>
      </button>

      <!-- Themes Zone -->
      <button
        class="map-zone"
        data-zone="themes"
        data-label="Theme Zone · Mechanics"
        data-zoom="1.5"
        data-offset-x="-4"
        data-offset-y="10"
        style="top: 62%; left: 34%;"
      >
        <span class="map-zone-icon">🎭</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Themes</span>
          <span class="map-zone-sub">Game mechanics</span>
        </span>
      </button>

      <!-- Timeline Zone -->
      <button
        class="map-zone"
        data-zone="timeline"
        data-label="Timeline Zone · Release Log"
        data-zoom="1.45"
        data-offset-x="12"
        data-offset-y="-4"
        style="top: 30%; left: 75%;"
      >
        <span class="map-zone-icon">⏳</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Timeline</span>
          <span class="map-zone-sub">Release log</span>
        </span>
      </button>

      <!-- Impact Zone -->
      <button
        class="map-zone"
        data-zone="impact"
        data-label="Impact Zone · Legacy"
        data-zoom="1.5"
        data-offset-x="-16"
        data-offset-y="14"
        style="top: 57%; left: 50%;"
      >
        <span class="map-zone-icon">🌍</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Impact</span>
          <span class="map-zone-sub">Legacy zone</span>
        </span>
      </button>


      <!-- Quote Quest Zone -->
      <button
        class="map-zone"
        data-zone="quotes"
        data-label="Quote Quest · Random Lines"
        data-zoom="1.2"
        data-offset-x="4"
        data-offset-y="7"
        style="top:39%; left: 43%;"
      >
        <span class="map-zone-icon">💬</span>
        <span class="map-zone-text">
          <span class="map-zone-label">Quote Quest</span>
          <span class="map-zone-sub">XP farming</span>
        </span>
      </button>

    </div>
  </div>

  <!-- PLAYER BAR -->
  <div class="player-bar">
    <div class="player-avatar">🛡️</div>
    <div class="player-info">
      <div class="player-name">Reader: Knight of La Mancha</div>
      <div class="player-class">Class: Literary Adventurer</div>
      <div class="hud-level-row">
        <span>Level <span id="levelValue">1</span></span>
        <span>XP <span id="xpValue">0</span>/<span id="xpMax">100</span></span>
      </div>
      <div class="xp-bar">
        <div class="xp-fill" id="xpFill"></div>
      </div>
      <div class="badges-inline">
        <span>Badges:</span>
        <div id="badgeTray"></div>
      </div>
    </div>
  </div>

  <!-- ZONE CONTENT MODAL -->
  <div class="zone-modal" id="zoneModal" aria-hidden="true">
    <div class="zone-modal-backdrop"></div>
    <div class="zone-modal-panel">
      <div class="zone-modal-header">
        <div class="zone-modal-header-left">
          <div class="zone-modal-title" id="activeZoneLabel">World View</div>
          <div class="zone-modal-sub">Tap a zone on the map to open its quest panel.</div>
        </div>
        <div class="zone-modal-actions">
          <button class="btn-ghost" id="backToMap">
            <span>🏠</span><span>Back to World</span>
          </button>
          <button class="btn-ghost" id="jumpOverview">
            <span>▶</span><span>Story</span>
          </button>
          <button class="btn-ghost" id="jumpQuotes">
            <span>🎲</span><span>Quote Quest</span>
          </button>
        </div>
      </div>
      <div class="zone-modal-body">
        <!-- EXISTING CONTENT (unchanged IDs and structure) -->
        <main>
          <!-- Overview Section -->
          <section id="overview" class="active">
            <h2>Story Zone · Overview</h2>
            <p>
              <strong>Title:</strong> Don Quixote<br>
              <strong>Author:</strong> Miguel de Cervantes<br>
              <strong>Genre:</strong> Novel, precursor to Romanticism and the modern novel<br>
              <strong>Setting:</strong> Early 17th-century Spain, during the Spanish Golden Age
            </p>

            <div class="card" style="margin-top: 10px;">
              <h3>Main Path</h3>
              <p>
                Alonso Quixano, a man from La Mancha, reads many books about knights and begins to see
                the world through those stories. He renames himself <em>Don Quixote de la Mancha</em>,
                chooses a village woman as his lady, and rides out on his horse with his squire
                Sancho Panza to seek adventure. He mistakes inns for castles and windmills for giants,
                is often beaten, yet keeps following his ideals of justice and honor.
              </p>
              <p style="margin-top: 6px;">
                As his fame grows, nobles play tricks on him, and in the end he is defeated by the
                Knight of the White Moon, returns home, lets go of his illusions, and dies peacefully
                as Alonso Quixano.
              </p>
              <p class="hint">Hint: think of this as the game’s main questline.</p>
            </div>

            <div class="chip-row">
              <span class="chip">Idealism vs. Reality</span>
              <span class="chip">Power of Books</span>
              <span class="chip">Identity</span>
              <span class="chip">Humor and Sadness</span>
            </div>
          </section>

          <!-- Author Section -->
          <section id="author">
            <h2>Author Zone · Miguel de Cervantes</h2>
            <p>
              Miguel de Cervantes Saavedra was born around 1547 in Alcalá de Henares, Spain.
              He worked as a soldier, was wounded at the Battle of Lepanto, and spent several
              years as a captive after being taken by pirates. These harsh experiences shaped
              his view of courage, suffering, and human weakness.
            </p>
            <p style="margin-top: 6px;">
              Cervantes wrote plays, poetry, and prose. His early novel <em>La Galatea</em>
              belonged to the pastoral tradition, but <em>Don Quixote</em> became his most
              important work and is now seen as the first modern novel.
            </p>

            <div class="grid" style="margin-top: 10px;">
              <div class="card">
                <h3>Profile Data</h3>
                <ul style="padding-left: 16px; margin-top: 4px; font-size: 0.85rem;">
                  <li>Born: c. September 29, 1547</li>
                  <li>Died: April 22, 1616, Madrid</li>
                  <li>Occupation: Novelist, poet, playwright</li>
                  <li>Family: Fourth of seven children</li>
                </ul>
              </div>
              <div class="card">
                <h3>Life Link to the Novel</h3>
                <p>
                  The mix of hope and hardship in Don Quixote reflects Cervantes’ own struggles
                  with war, captivity, and financial problems. The character’s stubborn dignity
                  matches the author’s effort to keep writing through difficulty.
                </p>
                <p class="hint">Side quest: match events in his life with scenes from the book.</p>
              </div>
            </div>
          </section>

          <!-- Characters Section -->
          <section id="characters">
            <h2>Party Zone · Characters</h2>
            <p class="hint">Think of each as a party member with a specialty.</p>
            <div class="grid">
              <div class="card">
                <h3>Don Quixote</h3>
                <p>
                  An aging man who wants to live as a knight-errant. Noble and hopeful
                  but often out of touch with reality. He stands for strong idealism
                  and faith in justice.
                </p>
                <div class="tag">Role: Idealist Tank</div>
                <div class="tag">Trait: Illusion</div>
              </div>

              <div class="card">
                <h3>Sancho Panza</h3>
                <p>
                  A farmer who becomes Don Quixote’s squire. He is practical, funny,
                  and loyal. He questions Don Quixote’s “giants” and “enchanted” objects,
                  standing for common sense.
                </p>
                <div class="tag">Role: Support / Realist</div>
                <div class="tag">Trait: Loyalty</div>
              </div>

              <div class="card">
                <h3>Dulcinea del Toboso</h3>
                <p>
                  A village woman idealized by Don Quixote as a noble lady.
                  She shows how imagination can turn ordinary people into
                  symbols of beauty and virtue.
                </p>
                <div class="tag">Role: Idealized Target</div>
              </div>

              <div class="card">
                <h3>Duke and Duchess</h3>
                <p>
                  Nobles who hear about Don Quixote’s adventures and invite him
                  to their estate. They enjoy playing tricks on him and Sancho,
                  exposing both cruelty and curiosity in the upper classes.
                </p>
                <div class="tag">Role: Trickster NPCs</div>
              </div>
            </div>
          </section>

          <!-- Themes Section -->
          <section id="themes">
            <h2>Theme Zone · Mechanics</h2>
            <div class="grid">
              <div class="card">
                <h3>Idealism vs. Reality</h3>
                <p>
                  Don Quixote follows chivalric values in a world that no longer lives by them.
                  His viewpoint clashes with everyday life, but it also makes people question
                  their own choices and values.
                </p>
              </div>
              <div class="card">
                <h3>Madness and Perspective</h3>
                <p>
                  The novel asks if Don Quixote is just mad or bravely living by his beliefs.
                  His “madness” often reveals truths others avoid.
                </p>
              </div>
              <div class="card">
                <h3>Power of Literature</h3>
                <p>
                  Books shape how Don Quixote sees himself and the world. The story always
                  plays with authors, fake texts, and stories inside stories.
                </p>
              </div>
              <div class="card">
                <h3>Identity and Change</h3>
                <p>
                  Characters change as they move and suffer. Sancho grows more imaginative,
                  while Don Quixote slowly becomes more aware of his limits.
                </p>
              </div>
            </div>

            <div class="chip-row">
              <span class="chip">Books</span>
              <span class="chip">Windmills</span>
              <span class="chip">Mirrors</span>
              <span class="chip">Armor</span>
            </div>
          </section>

          <!-- Timeline Section -->
          <section id="timeline">
            <h2>Timeline Zone · Release Log</h2>
            <div class="timeline-item">
              <div class="timeline-year">1605</div>
              <p>
                First part of <em>Don Quixote</em> appears as
                <em>El ingenioso hidalgo don Quijote de la Mancha</em>.
              </p>
            </div>
            <div class="timeline-item">
              <div class="timeline-year">1615</div>
              <p>
                Second part released, completing the story and deepening its
                self-aware style and reflection on fame.
              </p>
            </div>
            <div class="timeline-item">
              <div class="timeline-year">17th Century</div>
              <p>
                Read widely in Spain and Europe during the Spanish Golden Age.
              </p>
            </div>
            <div class="timeline-item">
              <div class="timeline-year">Modern Era</div>
              <p>
                Translated into many languages and studied as one of the world’s
                great novels. Writers, painters, and filmmakers keep reusing its
                figures and scenes.
              </p>
            </div>
          </section>

          <!-- Impact Section -->
          <section id="impact">
            <h2>Impact Zone · Legacy</h2>
            <div class="grid">
              <div class="card">
                <h3>Modern Novel Origin</h3>
                <p>
                  The book blends realism, comedy, and reflection on narration itself.
                  It uses different voices, false authors, and stories inside stories.
                </p>
              </div>
              <div class="card">
                <h3>Influence on Writers</h3>
                <p>
                  Later authors studied its deep character work and flexible structure.
                  It opened ways to write about memory, point of view, and broken time.
                </p>
              </div>
              <div class="card">
                <h3>Beyond Literature</h3>
                <p>
                  “Tilting at windmills” is now a phrase for fighting impossible battles.
                  The figure of Don Quixote appears in painting, film, music, and even
                  political discussion.
                </p>
              </div>
            </div>
          </section>

          <!-- Quotes Section -->
          <section id="quotes">
            <h2>Quote Quest · Random Lines</h2>
            <p>
              Draw a quote to see a line linked to illusion, reality, or reading. Each draw
              gives you XP and counts toward badges.
            </p>

            <button id="quoteBtn" class="btn-primary">
              <span>🎲</span> Draw a quote
            </button>

            <div id="quoteBox" class="quote-box" style="display:none;">
              <div id="quoteText"></div>
              <div id="quoteMeta" class="quote-meta"></div>
            </div>

            <div class="quote-stats">
              <span>Quotes drawn: <span id="quoteCount">0</span></span>
              <span>Best streak: <span id="bestStreak">0</span></span>
            </div>

            <div class="quiz-card">
              <h3>Mini Quiz · Theme Check</h3>
              <p class="quiz-q">Which theme best fits Don Quixote attacking the windmills?</p>
              <div class="quiz-options">
                <button class="quiz-option" data-correct="false">Power of literature</button>
                <button class="quiz-option" data-correct="true">Idealism vs. reality</button>
                <button class="quiz-option" data-correct="false">Family conflict</button>
              </div>
              <p id="quizFeedback" class="quiz-feedback"></p>
              <p class="hint">Answer right once to clear this quest and gain bonus XP.</p>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
  
  <footer>
    Don Quixote · Knight’s Codex — map-style classroom concept site. No login, just explore.
    
  </footer>
</div>

<script>
  // -------- MAP + ZONE POPUP LOGIC --------

  const mapLayer = document.getElementById('mapLayer');
  const zoneButtons = document.querySelectorAll('.map-zone');
  const zoneModal = document.getElementById('zoneModal');
  const activeZoneLabel = document.getElementById('activeZoneLabel');
  const backToMapBtn = document.getElementById('backToMap');
  const hudBackToMap = document.getElementById('hudBackToMap');
  const hudRecenter = document.getElementById('hudRecenter');
  const sectionCountLabel = document.getElementById('sectionCountLabel');

  const sections = document.querySelectorAll('main section');
  const jumpOverview = document.getElementById('jumpOverview');
  const jumpQuotes = document.getElementById('jumpQuotes');

  // XP / level system
  let xp = 0;
  let xpMax = 100;
  let level = 1;
  const xpFill = document.getElementById('xpFill');
  const xpValue = document.getElementById('xpValue');
  const xpMaxSpan = document.getElementById('xpMax');
  const levelValue = document.getElementById('levelValue');
  const badgeTray = document.getElementById('badgeTray');
  const visitedZones = new Set();
  let badges = [];

  function addBadge(name) {
    if (!badges.includes(name)) {
      badges.push(name);
      badgeTray.innerHTML = badges
        .map(b => '<span class="badge-pill">' + b + '</span>')
        .join(' ');
    }
  }

  function addXP(amount) {
    xp += amount;
    while (xp >= xpMax) {
      xp -= xpMax;
      level += 1;
      xpMax += 50;
      levelValue.textContent = level;
      xpMaxSpan.textContent = xpMax;
      addBadge('Level ' + level);
    }
    xpValue.textContent = xp;
    const pct = Math.max(0, Math.min(100, (xp / xpMax) * 100));
    xpFill.style.width = pct + '%';

    if (visitedZones.size >= 4) {
      addBadge('Zone Explorer');
    }const xpFloat = document.getElementById('xpFloat');
  if (xpFloat) {
    xpFloat.textContent = `+${amount} XP`;
    xpFloat.classList.add('show');
    setTimeout(() => xpFloat.classList.remove('show'), 500);
  }

  if (visitedZones.size >= 4) {
    addBadge('Zone Explorer');
  }
  }

  function updateVisitedLabel() {
    sectionCountLabel.textContent = visitedZones.size + ' zones visited';
  }

  // Open a given zone: pan/zoom map + show modal + show section
  function openZone(targetId) {
    sections.forEach(sec => {
      sec.classList.toggle('active', sec.id === targetId);
    });

    zoneButtons.forEach(btn => {
      const isActive = btn.dataset.zone === targetId;
      btn.classList.toggle('active', isActive);
      if (isActive) {
        const zoom = parseFloat(btn.dataset.zoom || '1.4');
        const offsetX = parseFloat(btn.dataset.offsetX || '0');
        const offsetY = parseFloat(btn.dataset.offsetY || '0');

        if (mapLayer) {
          mapLayer.style.transform =
            'translate(' + offsetX + '%, ' + offsetY + '%) scale(' + zoom + ')';
        }

        const label = btn.dataset.label ||
          (btn.querySelector('.map-zone-label')?.textContent || targetId);
        activeZoneLabel.textContent = label;
      }
    });

    zoneModal.classList.add('open');
    zoneModal.setAttribute('aria-hidden', 'false');

    visitedZones.add(targetId);
    updateVisitedLabel();
    addXP(5);
  }

  function closeZoneModalAndResetCamera() {
    if (mapLayer) {
      mapLayer.style.transform = 'translate(0,0) scale(1)';
    }
    zoneButtons.forEach(btn => btn.classList.remove('active'));
    zoneModal.classList.remove('open');
    zoneModal.setAttribute('aria-hidden', 'true');
    activeZoneLabel.textContent = 'World View';
  }

  zoneButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.zone;
      if (target) openZone(target);
    });
  });

  if (backToMapBtn) backToMapBtn.addEventListener('click', closeZoneModalAndResetCamera);
  if (hudBackToMap) hudBackToMap.addEventListener('click', closeZoneModalAndResetCamera);
  if (hudRecenter) hudRecenter.addEventListener('click', closeZoneModalAndResetCamera);

  if (jumpOverview) jumpOverview.addEventListener('click', () => openZone('overview'));
  if (jumpQuotes) jumpQuotes.addEventListener('click', () => openZone('quotes'));

  // Mark overview as a visited zone at the start (even before opening modal)
  visitedZones.add('overview');
  updateVisitedLabel();

  // -------- QUOTES (DB) + QUIZ LOGIC (unchanged) --------

  const quotes = <?php echo json_encode($quotes, JSON_UNESCAPED_UNICODE); ?>;

  const quoteBtn = document.getElementById('quoteBtn');
  const quoteBox = document.getElementById('quoteBox');
  const quoteText = document.getElementById('quoteText');
  const quoteMeta = document.getElementById('quoteMeta');
  const quoteCountSpan = document.getElementById('quoteCount');
  const bestStreakSpan = document.getElementById('bestStreak');

  let quoteCount = 0;
  let bestStreak = 0;

  if (quoteBtn) {
    quoteBtn.addEventListener('click', () => {
      if (!quotes || quotes.length === 0) return;
      const q = quotes[Math.floor(Math.random() * quotes.length)];
      quoteText.textContent = '"' + (q.text || '') + '"';
      quoteMeta.textContent = (q.speaker || 'Unknown') +
        (q.focus ? ' · Focus: ' + q.focus : '');
      quoteBox.style.display = 'block';

      quoteCount += 1;
      quoteCountSpan.textContent = quoteCount;

      if (quoteCount > bestStreak) {
        bestStreak = quoteCount;
        bestStreakSpan.textContent = bestStreak;
      }

      addXP(10);
      if (quoteCount >= 3) addBadge('Quote Seeker');
      if (quoteCount >= 7) addBadge('Quote Knight');
    });
  }

  const quizOptions = document.querySelectorAll('.quiz-option');
  const quizFeedback = document.getElementById('quizFeedback');
  let quizCompleted = false;

  quizOptions.forEach(opt => {
    opt.addEventListener('click', () => {
      if (quizCompleted) return;
      const isCorrect = opt.dataset.correct === 'true';

      quizOptions.forEach(o => o.classList.remove('correct-choice', 'wrong-choice'));

      if (isCorrect) {
        opt.classList.add('correct-choice');
        quizFeedback.textContent = 'Correct. The windmills scene shows how ideals clash with real objects.';
        quizFeedback.classList.add('good');
        quizFeedback.classList.remove('bad');
        addXP(20);
        addBadge('Theme Reader');
        quizCompleted = true;
      } else {
        opt.classList.add('wrong-choice');
        quizFeedback.textContent = 'Not quite. Think again about what he sees and what is really there.';
        quizFeedback.classList.add('bad');
        quizFeedback.classList.remove('good');
      }
    });
  });

  // Close modal when clicking the dark backdrop
const modalBackdrop = document.querySelector('.zone-modal-backdrop');
if (modalBackdrop) {
  modalBackdrop.addEventListener('click', closeZoneModalAndResetCamera);
}

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && zoneModal.classList.contains('open')) {
    closeZoneModalAndResetCamera();
  }
});

</script>
</body>
</html>
