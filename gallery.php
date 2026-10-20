<?php
if (!defined('RUANG_CERITA_AUTHENTICATED') || RUANG_CERITA_AUTHENTICATED !== true) {
    http_response_code(404);
    exit('Not found.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f6f2eb">
  <meta name="description" content="Sebuah galeri kecil berisi cerita, tawa, dan kenangan kita berdua.">
  <title>Ruang Cerita — Album Kita</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: light;
      --paper: #f6f2eb;
      --ink: #28221f;
      --muted: #8e8379;
      --rose: #a85d55;
      --line: rgba(52, 43, 38, .13);
      --serif: "Playfair Display", Georgia, serif;
      --sans: "DM Sans", Arial, sans-serif;
    }

    * { box-sizing: border-box; }

    body {
      min-width: 320px;
      min-height: 100vh;
      margin: 0;
      overflow-x: hidden;
      background:
        radial-gradient(ellipse at 50% 38%, rgba(232, 216, 197, .46), transparent 42%),
        var(--paper);
      color: var(--ink);
      font-family: var(--sans);
      -webkit-font-smoothing: antialiased;
    }

    .sakura-canvas {
      position: fixed;
      z-index: 0;
      inset: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }

    button { font: inherit; }

    .page-shell {
      position: relative;
      z-index: 1;
      width: min(1260px, calc(100% - 88px));
      margin: 0 auto;
    }

    .topbar {
      display: flex;
      height: 86px;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--line);
    }

    .brand {
      display: inline-flex;
      gap: 11px;
      align-items: center;
      color: var(--ink);
      font-size: 14px;
      font-weight: 700;
      letter-spacing: -.04em;
      text-decoration: none;
    }

    .brand-mark {
      display: grid;
      width: 34px;
      height: 34px;
      place-items: center;
      border: 1px solid rgba(168, 93, 85, .3);
      border-radius: 50%;
      color: var(--rose);
      font-size: 16px;
    }

    .topbar-note {
      color: var(--muted);
      font-size: 11px;
      letter-spacing: .16em;
      text-transform: uppercase;
    }

    .account-controls {
      display: inline-flex;
      align-items: center;
      gap: 12px;
    }

    .account-name {
      color: var(--muted);
      font-size: 10px;
    }

    .account-name span { margin-right: 3px; color: var(--rose); }

    .logout-form { margin: 0; }

    .logout-button {
      padding: 7px 11px;
      border: 1px solid var(--line);
      border-radius: 20px;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      font: inherit;
      font-size: 9px;
      transition: border-color 180ms ease, color 180ms ease;
    }

    .logout-button:hover { border-color: var(--rose); color: var(--rose); }
    .logout-button:focus-visible { outline: 2px solid var(--rose); outline-offset: 3px; }

    main { padding: 52px 0 32px; }

    .intro {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: var(--rose);
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .2em;
      text-transform: uppercase;
    }

    .eyebrow::before,
    .eyebrow::after {
      width: 22px;
      height: 1px;
      background: currentColor;
      content: "";
      opacity: .55;
    }

    h1 {
      margin: 15px 0 10px;
      font-family: var(--serif);
      font-size: clamp(38px, 5.1vw, 65px);
      font-weight: 500;
      letter-spacing: -.055em;
      line-height: 1.12;
    }

    h1 em { color: var(--rose); font-weight: 500; }

    .subtitle {
      margin: 0;
      color: var(--muted);
      font-size: 13px;
      line-height: 1.7;
    }

    .gallery {
      position: relative;
      overflow: hidden;
      margin-top: 22px;
      perspective: 1400px;
    }

    .stage {
      position: relative;
      display: grid;
      height: clamp(380px, 43vw, 510px);
      place-items: center;
      overflow: hidden;
      perspective: 1400px;
      perspective-origin: 50% 43%;
      touch-action: pan-y;
    }

    .photo-card {
      position: absolute;
      width: clamp(285px, 47vw, 560px);
      height: clamp(320px, 37vw, 430px);
      overflow: hidden;
      border: 5px solid rgba(255, 255, 255, .88);
      border-radius: 5px;
      background: #d8c9bb;
      box-shadow: 0 22px 55px rgba(58, 42, 32, .17), 0 4px 12px rgba(58, 42, 32, .1);
      cursor: pointer;
      transform:
        translateX(calc(var(--offset) * 68%))
        translateZ(calc(var(--depth) * -1px))
        rotateY(calc(var(--offset) * -9deg))
        scale(var(--scale));
      opacity: var(--opacity);
      transition:
        transform 850ms cubic-bezier(.2, .72, .22, 1),
        opacity 650ms ease,
        box-shadow 650ms ease;
      will-change: transform;
    }

    .photo-card.is-active {
      cursor: default;
      box-shadow: 0 28px 70px rgba(58, 42, 32, .23), 0 4px 12px rgba(58, 42, 32, .12);
    }

    .photo-edit-button {
      position: absolute;
      z-index: 3;
      top: 12px;
      right: 12px;
      display: inline-flex;
      min-height: 35px;
      align-items: center;
      gap: 7px;
      padding: 0 11px;
      border: 1px solid rgba(255, 255, 255, .64);
      border-radius: 20px;
      background: rgba(40, 34, 31, .56);
      color: white;
      cursor: pointer;
      font-size: 10px;
      font-weight: 500;
      opacity: 0;
      pointer-events: none;
      visibility: hidden;
      transition: opacity 180ms ease, background 180ms ease, visibility 180ms ease;
      backdrop-filter: blur(8px);
    }

    .photo-card.is-active .photo-edit-button {
      opacity: 1;
      pointer-events: auto;
      visibility: visible;
    }

    .photo-edit-button:hover { background: rgba(40, 34, 31, .82); }
    .photo-edit-button:focus-visible { outline: 2px solid white; outline-offset: 3px; }
    .photo-edit-button svg { width: 15px; height: 15px; }

    .photo-card img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 1.1s cubic-bezier(.2, .72, .22, 1);
    }

    .photo-card.is-active:hover img { transform: scale(1.035); }

    .photo-card::after {
      position: absolute;
      inset: 25% 0 0;
      background: linear-gradient(180deg, transparent 0%, rgba(24, 20, 19, .11) 32%, rgba(24, 20, 19, .72) 100%);
      content: "";
      pointer-events: none;
    }

    .photo-caption {
      position: absolute;
      z-index: 1;
      right: 28px;
      bottom: 27px;
      left: 28px;
      color: #fff;
      pointer-events: none;
    }

    .photo-caption span {
      display: block;
      margin-bottom: 7px;
      color: rgba(255, 255, 255, .77);
      font-size: 9px;
      font-weight: 600;
      letter-spacing: .18em;
      text-transform: uppercase;
    }

    .photo-caption h2 {
      margin: 0;
      font-family: var(--serif);
      font-size: clamp(24px, 3vw, 34px);
      font-weight: 500;
      letter-spacing: -.035em;
    }

    .photo-caption p {
      margin: 6px 0 0;
      color: rgba(255, 255, 255, .8);
      font-size: 11px;
    }

    .arrow {
      position: absolute;
      z-index: 3;
      top: 50%;
      display: grid;
      width: 46px;
      height: 46px;
      place-items: center;
      border: 1px solid rgba(52, 43, 38, .16);
      border-radius: 50%;
      background: rgba(246, 242, 235, .9);
      color: var(--ink);
      cursor: pointer;
      transform: translateY(-50%);
      transition: color 180ms ease, background 180ms ease, border-color 180ms ease, transform 180ms ease;
      backdrop-filter: blur(8px);
    }

    .arrow:hover {
      border-color: var(--ink);
      background: var(--ink);
      color: #fff;
      transform: translateY(-50%) scale(1.06);
    }

    .arrow:focus-visible,
    .play-button:focus-visible,
    .thumbnail:focus-visible {
      outline: 2px solid var(--rose);
      outline-offset: 4px;
    }

    .photo-file-input { display: none; }

    .arrow svg { width: 17px; height: 17px; }
    .arrow-prev { left: max(2px, calc(50% - 390px)); }
    .arrow-next { right: max(2px, calc(50% - 390px)); }

    .gallery-footer {
      display: flex;
      width: min(790px, 100%);
      min-height: 76px;
      align-items: center;
      justify-content: space-between;
      gap: 22px;
      margin: -2px auto 0;
    }

    .memory-count {
      display: flex;
      min-width: 125px;
      align-items: baseline;
      gap: 6px;
      color: var(--muted);
      font-size: 11px;
      letter-spacing: .1em;
    }

    .memory-count strong {
      color: var(--ink);
      font-family: var(--serif);
      font-size: 20px;
      font-weight: 500;
      letter-spacing: 0;
    }

    .memory-count span:last-child { margin-left: 2px; }

    .thumbnails {
      display: flex;
      flex: 1;
      justify-content: center;
      gap: 9px;
    }

    .thumbnail {
      width: 45px;
      height: 45px;
      padding: 3px;
      overflow: hidden;
      border: 1px solid transparent;
      border-radius: 50%;
      background: transparent;
      cursor: pointer;
      transition: border-color 200ms ease, transform 200ms ease;
    }

    .thumbnail:hover { transform: translateY(-2px); }
    .thumbnail.is-active { border-color: var(--rose); }

    .thumbnail img {
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    .photo-status {
      min-height: 13px;
      margin: -3px auto 0;
      color: var(--muted);
      font-size: 9px;
      line-height: 1.5;
      text-align: center;
    }

    .photo-status.is-error { color: #a13e37; }

    .play-button {
      display: inline-flex;
      min-width: 125px;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
      padding: 9px 0 9px 9px;
      border: 0;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      font-size: 10px;
      transition: color 180ms ease;
    }

    .play-button:hover { color: var(--rose); }

    .play-icon {
      display: grid;
      width: 30px;
      height: 30px;
      place-items: center;
      border: 1px solid var(--line);
      border-radius: 50%;
      color: var(--ink);
    }

    .play-icon svg { width: 11px; height: 11px; }

    .music-player {
      display: grid;
      width: min(480px, 100%);
      grid-template-columns: auto minmax(0, 1fr) auto;
      align-items: center;
      gap: 15px;
      margin: 28px auto 0;
      padding: 13px 17px;
      border: 1px solid var(--line);
      border-radius: 9px;
      background: rgba(255, 255, 255, .42);
    }

    .music-play-button {
      display: grid;
      width: 39px;
      height: 39px;
      flex: 0 0 auto;
      place-items: center;
      border: 0;
      border-radius: 50%;
      background: var(--ink);
      color: #fff;
      cursor: pointer;
      transition: background 180ms ease, transform 180ms ease;
    }

    .music-play-button:not(:disabled):hover {
      background: var(--rose);
      transform: scale(1.05);
    }

    .music-play-button:disabled { cursor: not-allowed; opacity: .45; }

    .music-play-button svg { width: 15px; height: 15px; }

    .music-details { min-width: 0; }

    .music-title {
      overflow: hidden;
      margin: 0 0 7px;
      color: var(--ink);
      font-size: 11px;
      font-weight: 600;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .music-controls {
      display: grid;
      grid-template-columns: 68px minmax(30px, 1fr) 13px 62px;
      align-items: center;
      gap: 8px;
    }

    .music-time {
      min-width: 68px;
      color: var(--muted);
      font-size: 9px;
      font-variant-numeric: tabular-nums;
    }

    .music-seek,
    .music-volume {
      height: 4px;
      margin: 0;
      accent-color: var(--rose);
      cursor: pointer;
    }

    .music-seek,
    .music-volume { width: 100%; }

    .volume-icon { width: 13px; height: 13px; color: var(--muted); }

    .music-upload {
      display: inline-flex;
      min-height: 34px;
      align-items: center;
      justify-content: center;
      padding: 0 11px;
      border: 1px solid var(--line);
      border-radius: 5px;
      color: var(--muted);
      cursor: pointer;
      font-size: 10px;
      white-space: nowrap;
      transition: border-color 180ms ease, color 180ms ease;
    }

    .music-upload:hover { border-color: var(--rose); color: var(--rose); }
    .music-upload:focus-within { outline: 2px solid var(--rose); outline-offset: 3px; }
    .music-upload input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }

    .music-status {
      grid-column: 2 / 4;
      min-height: 12px;
      margin: -9px 0 0;
      color: var(--muted);
      font-size: 9px;
    }

    .music-status.is-error { color: #a13e37; }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    .wishlist {
      width: min(660px, 100%);
      margin: 44px auto 0;
      padding: 24px;
      border: 1px solid var(--line);
      border-radius: 12px;
      background: rgba(255, 255, 255, .47);
      box-shadow: 0 12px 35px rgba(58, 42, 32, .045);
    }

    .wishlist-heading {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 20px;
    }

    .wishlist-kicker {
      margin: 0 0 5px;
      color: var(--rose);
      font-size: 9px;
      font-weight: 700;
      letter-spacing: .16em;
      text-transform: uppercase;
    }

    .wishlist-title {
      margin: 0;
      font-family: var(--serif);
      font-size: clamp(22px, 3vw, 29px);
      font-weight: 500;
      letter-spacing: -.035em;
    }

    .wishlist-intro {
      margin: 7px 0 0;
      color: var(--muted);
      font-size: 11px;
      line-height: 1.6;
    }

    .wishlist-progress {
      display: grid;
      min-width: 70px;
      justify-items: end;
      gap: 3px;
      padding-top: 3px;
      color: var(--muted);
      font-size: 9px;
    }

    .wishlist-progress strong {
      color: var(--rose);
      font-family: var(--serif);
      font-size: 24px;
      font-weight: 500;
      line-height: 1;
    }

    .wishlist-form {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 8px;
    }

    .wishlist-input {
      width: 100%;
      min-width: 0;
      min-height: 42px;
      padding: 0 13px;
      border: 1px solid var(--line);
      border-radius: 6px;
      outline: none;
      background: rgba(255, 255, 255, .72);
      color: var(--ink);
      font: inherit;
      font-size: 11px;
      transition: border-color 180ms ease, box-shadow 180ms ease;
    }

    .wishlist-input::placeholder { color: #a3988f; }

    .wishlist-input:focus {
      border-color: rgba(168, 93, 85, .68);
      box-shadow: 0 0 0 3px rgba(168, 93, 85, .1);
    }

    .wishlist-add {
      display: inline-flex;
      min-height: 42px;
      align-items: center;
      justify-content: center;
      gap: 7px;
      padding: 0 15px;
      border: 0;
      border-radius: 6px;
      background: var(--ink);
      color: #fff;
      cursor: pointer;
      font: inherit;
      font-size: 10px;
      font-weight: 600;
      transition: background 180ms ease, transform 180ms ease;
    }

    .wishlist-add:hover { background: var(--rose); transform: translateY(-1px); }
    .wishlist-add svg { width: 13px; height: 13px; }

    .wishlist-suggestions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 7px;
      margin-top: 11px;
    }

    .wishlist-suggestions-label {
      margin-right: 2px;
      color: var(--muted);
      font-size: 9px;
    }

    .wishlist-suggestion {
      padding: 5px 9px;
      border: 1px solid rgba(168, 93, 85, .19);
      border-radius: 20px;
      background: rgba(255, 255, 255, .52);
      color: #94605a;
      cursor: pointer;
      font-size: 9px;
      transition: border-color 180ms ease, background 180ms ease;
    }

    .wishlist-suggestion:hover {
      border-color: rgba(168, 93, 85, .48);
      background: rgba(255, 255, 255, .9);
    }

    .wishlist-list {
      display: grid;
      gap: 7px;
      margin: 19px 0 0;
      padding: 0;
      list-style: none;
    }

    .wishlist-item {
      display: flex;
      min-width: 0;
      min-height: 47px;
      align-items: center;
      gap: 11px;
      padding: 8px 9px 8px 12px;
      border: 1px solid rgba(52, 43, 38, .075);
      border-radius: 7px;
      background: rgba(255, 255, 255, .56);
      transition: border-color 180ms ease, background 180ms ease;
    }

    .wishlist-item:hover {
      border-color: rgba(168, 93, 85, .2);
      background: rgba(255, 255, 255, .84);
    }

    .wishlist-check {
      width: 17px;
      height: 17px;
      flex: 0 0 auto;
      margin: 0;
      accent-color: var(--rose);
      cursor: pointer;
    }

    .wishlist-item-text {
      flex: 1;
      overflow-wrap: anywhere;
      color: var(--ink);
      font-size: 11px;
      line-height: 1.5;
    }

    .wishlist-item.is-done .wishlist-item-text {
      color: var(--muted);
      text-decoration: line-through;
      text-decoration-color: rgba(142, 131, 121, .65);
    }

    .wishlist-delete {
      display: grid;
      width: 30px;
      height: 30px;
      flex: 0 0 auto;
      place-items: center;
      border: 0;
      border-radius: 50%;
      background: transparent;
      color: #a3988f;
      cursor: pointer;
      transition: background 180ms ease, color 180ms ease;
    }

    .wishlist-delete:hover { background: rgba(168, 93, 85, .1); color: var(--rose); }
    .wishlist-delete svg { width: 14px; height: 14px; }

    .wishlist-empty {
      margin: 18px 0 0;
      padding: 19px 10px;
      border: 1px dashed rgba(168, 93, 85, .22);
      border-radius: 7px;
      color: var(--muted);
      font-size: 10px;
      line-height: 1.7;
      text-align: center;
    }

    .wishlist-status {
      min-height: 12px;
      margin: 12px 0 0;
      color: var(--muted);
      font-size: 9px;
      line-height: 1.6;
    }

    .wishlist-status.is-error { color: #a13e37; }

    .wishlist-clear {
      display: none;
      margin: 8px 0 0;
      padding: 5px 0;
      border: 0;
      background: transparent;
      color: var(--rose);
      cursor: pointer;
      font-size: 9px;
    }

    .wishlist-clear.is-visible { display: inline-block; }
    .wishlist button:focus-visible { outline: 2px solid var(--rose); outline-offset: 3px; }

    .bottom-note {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 22px;
      color: #a3988f;
      font-size: 10px;
      letter-spacing: .04em;
    }

    .bottom-note span { color: var(--rose); font-size: 12px; }

    @media (max-width: 700px) {
      .page-shell { width: calc(100% - 36px); }
      .topbar { height: 68px; }
      .topbar-note { font-size: 9px; letter-spacing: .11em; }
      .account-controls { gap: 7px; }
      .account-controls .topbar-note { display: none; }
      .account-name { max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .logout-button { padding: 6px 9px; }
      main { padding-top: 43px; }
      h1 { max-width: 520px; margin: 14px auto 9px; }
      .subtitle { max-width: 330px; margin: 0 auto; font-size: 12px; }
      .gallery { margin-top: 18px; }
      .stage { height: 390px; perspective-origin: 50% 45%; }
      .photo-card { width: min(72vw, 370px); height: 330px; }
      .photo-card:not(.is-active) { opacity: var(--opacity); }
      .photo-caption { right: 21px; bottom: 21px; left: 21px; }
      .photo-edit-button { top: 9px; right: 9px; min-height: 32px; padding: 0 9px; font-size: 9px; }
      .arrow { width: 39px; height: 39px; }
      .arrow-prev { left: 1px; }
      .arrow-next { right: 1px; }
      .gallery-footer { min-height: 68px; gap: 8px; }
      .memory-count { min-width: 59px; gap: 4px; font-size: 9px; }
      .memory-count strong { font-size: 17px; }
      .play-button { min-width: 66px; gap: 0; font-size: 0; }
      .play-icon { width: 29px; height: 29px; }
      .thumbnails { gap: 5px; }
      .thumbnail { width: 38px; height: 38px; padding: 2px; }
      .music-player { gap: 10px; margin-top: 20px; padding: 12px; }
      .music-controls { grid-template-columns: 62px minmax(25px, 1fr) 13px 46px; gap: 6px; }
      .wishlist { margin-top: 31px; padding: 19px 15px; }
      .wishlist-heading { margin-bottom: 16px; }
      .wishlist-form { grid-template-columns: minmax(0, 1fr) auto; gap: 7px; }
      .wishlist-add { gap: 5px; padding: 0 11px; }
      .wishlist-suggestions { gap: 6px; }
      .bottom-note { margin-top: 23px; font-size: 9px; }
    }

    @media (max-width: 380px) {
      .page-shell { width: calc(100% - 28px); }
      .topbar-note { max-width: 155px; text-align: right; }
      .stage { height: 350px; }
      .photo-card { width: 74vw; height: 295px; }
      .thumbnail { width: 33px; height: 33px; }
      .thumbnails { gap: 3px; }
      .music-player { grid-template-columns: auto minmax(0, 1fr); }
      .music-upload { grid-column: 1 / 3; grid-row: 2; justify-self: end; }
      .music-status { grid-column: 1 / 3; margin-top: 0; }
      .music-time { min-width: 61px; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
      }
    }
  </style>
</head>
<body>
  <canvas class="sakura-canvas" id="sakura-canvas" aria-hidden="true"></canvas>
  <div class="page-shell">
    <header class="topbar">
      <a class="brand" href="#" aria-label="Ruang Cerita">
        <span class="brand-mark" aria-hidden="true">♡</span>
        <span>ruang cerita</span>
      </a>
      <div class="account-controls">
        <span class="topbar-note">album kecil, cerita besar</span>
        <span class="account-name"><span aria-hidden="true">♡</span> <?= $authenticatedAccountName ?></span>
        <form class="logout-form" method="post" action="./">
          <input type="hidden" name="csrf" value="<?= $authenticatedCsrfToken ?>">
          <input type="hidden" name="action" value="logout">
          <button class="logout-button" type="submit">Keluar</button>
        </form>
      </div>
    </header>

    <main>
      <section class="intro" aria-labelledby="page-title">
        <div class="eyebrow">untuk kita, selalu</div>
        <h1 id="page-title">Kita, dalam cerita<br><em>yang tak pernah selesai.</em></h1>
        <p class="subtitle">Potongan kecil dari hari-hari yang terasa lebih indah karena dijalani berdua.</p>
      </section>

      <section class="gallery" aria-label="Galeri kenangan kita">
        <div class="stage" id="stage" aria-live="polite" aria-atomic="true"></div>
        <button class="arrow arrow-prev" id="previous" type="button" aria-label="Foto sebelumnya">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button class="arrow arrow-next" id="next" type="button" aria-label="Foto berikutnya">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="gallery-footer">
          <div class="memory-count" aria-label="Nomor foto">
            <strong id="current-count">01</strong><span>/</span><span id="total-count">05</span>
          </div>
          <div class="thumbnails" id="thumbnails" aria-label="Pilih foto"></div>
          <button class="play-button" id="autoplay" type="button" aria-label="Jeda pergantian foto otomatis">
            <span class="play-icon" id="play-icon" aria-hidden="true"></span>
            <span id="play-label">Jeda otomatis</span>
          </button>
        </div>
        <p class="photo-status" id="photo-status" role="status">Pilih foto lalu tekan “Ganti foto” untuk memasang kenangan kalian.</p>
        <input class="photo-file-input" id="photo-file" type="file" accept="image/*" aria-label="Pilih foto pengganti">
      </section>

      <section class="music-player" aria-label="Pemutar lagu">
        <button class="music-play-button" id="music-play" type="button" aria-label="Putar lagu" disabled>
          <svg id="music-play-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M5 3.5a.75.75 0 0 1 1.14-.64l6 4a.75.75 0 0 1 0 1.28l-6 4A.75.75 0 0 1 5 11.5z"/></svg>
        </button>
        <div class="music-details">
          <p class="music-title" id="music-title">Pilih lagu untuk kenangan kita</p>
          <div class="music-controls">
            <span class="music-time" id="music-time">0:00 / 0:00</span>
            <input class="music-seek" id="music-seek" type="range" min="0" max="100" value="0" aria-label="Posisi lagu" disabled>
            <svg class="volume-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M7.5 3.2a.75.75 0 0 1 1.28-.53l2.5 2.5a.75.75 0 0 1 .22.53v4.6a.75.75 0 0 1-.22.53l-2.5 2.5a.75.75 0 0 1-1.28-.53V11H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h2.5zM13 5a.75.75 0 0 1 1.06 0 4.24 4.24 0 0 1 0 6 .75.75 0 1 1-1.06-1.06 2.74 2.74 0 0 0 0-3.88A.75.75 0 0 1 13 5"/></svg>
            <input class="music-volume" id="music-volume" type="range" min="0" max="1" step="0.01" value="0.7" aria-label="Volume lagu">
          </div>
        </div>
        <label class="music-upload">Pilih lagu
          <input id="music-file" type="file" accept="audio/*" aria-label="Pilih file lagu dari perangkat">
        </label>
        <p class="music-status" id="music-status" role="status">Pilih file MP3, WAV, atau audio lain dari perangkatmu.</p>
        <audio id="music-audio" preload="metadata"></audio>
      </section>

      <section class="wishlist" aria-labelledby="wishlist-title">
        <div class="wishlist-heading">
          <div>
            <p class="wishlist-kicker">hal-hal yang ingin kita lakukan</p>
            <h2 class="wishlist-title" id="wishlist-title">Wishlist berdua</h2>
            <p class="wishlist-intro">Tulis mimpi kecil kalian, lalu wujudkan satu per satu.</p>
          </div>
          <div class="wishlist-progress" aria-live="polite">
            <strong id="wishlist-progress">0/0</strong>
            <span>terwujud</span>
          </div>
        </div>
        <form class="wishlist-form" id="wishlist-form">
          <label class="sr-only" for="wishlist-input">Rencana yang ingin dilakukan bersama</label>
          <input class="wishlist-input" id="wishlist-input" name="wish" type="text" maxlength="100" placeholder="Contoh: melihat matahari terbenam bersama" autocomplete="off" required>
          <button class="wishlist-add" type="submit">
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            Tambah
          </button>
        </form>
        <div class="wishlist-suggestions" aria-label="Ide rencana bersama">
          <span class="wishlist-suggestions-label">Ide kecil:</span>
          <button class="wishlist-suggestion" type="button" data-wish="Jalan-jalan ke tempat baru">Jalan-jalan bareng</button>
          <button class="wishlist-suggestion" type="button" data-wish="Masak makanan favorit bersama">Masak bareng</button>
          <button class="wishlist-suggestion" type="button" data-wish="Menonton matahari terbenam bersama">Menonton sunset</button>
        </div>
        <ul class="wishlist-list" id="wishlist-list" aria-label="Daftar wishlist bersama"></ul>
        <p class="wishlist-empty" id="wishlist-empty">Belum ada rencana di daftar kalian. Tambahkan mimpi kecil pertama bersama ♡</p>
        <button class="wishlist-clear" id="wishlist-clear" type="button">Hapus semua yang sudah terwujud</button>
        <p class="wishlist-status" id="wishlist-status" role="status">Daftar tersimpan otomatis di browser ini dan bisa dibuka bersama di perangkat yang sama.</p>
      </section>

      <div class="bottom-note"><span aria-hidden="true">✳</span> setiap foto punya cerita, setiap cerita punya kita</div>
    </main>
  </div>

  <script>
    const memories = [
      {
        title: "Sore yang terasa rumah",
        detail: "Di mana pun, asal sama kamu.",
        date: "Satu sore di bulan Mei",
        image: "https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=1200&q=85",
        alt: "Sepasang kekasih berbagi momen hangat bersama"
      },
      {
        title: "Dunia terasa pelan",
        detail: "Tak buru-buru, selama bersamamu.",
        date: "Hari libur kesukaan",
        image: "https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=1200&q=85",
        alt: "Pasangan menikmati hari istimewa bersama"
      },
      {
        title: "Tawa tanpa alasan",
        detail: "Hal-hal kecil yang paling dirindukan.",
        date: "Kenangan yang selalu diulang",
        image: "https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=1200&q=85",
        alt: "Dua orang tertawa bahagia bersama"
      },
      {
        title: "Pergi, lalu pulang",
        detail: "Petualangan terbaik adalah berdua.",
        date: "Perjalanan pertama kita",
        image: "https://images.unsplash.com/photo-1532712938310-34cb3982ef74?auto=format&fit=crop&w=1200&q=85",
        alt: "Pasangan menikmati perjalanan bersama"
      },
      {
        title: "Selalu memilih kamu",
        detail: "Hari ini, esok, dan seterusnya.",
        date: "Cerita kita yang masih berjalan",
        image: "https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&w=1200&q=85",
        alt: "Pasangan berpelukan dalam suasana hangat"
      }
    ];

    const stage = document.querySelector("#stage");
    const photoFile = document.querySelector("#photo-file");
    const photoStatus = document.querySelector("#photo-status");
    const thumbnails = document.querySelector("#thumbnails");
    const currentCount = document.querySelector("#current-count");
    const totalCount = document.querySelector("#total-count");
    const autoplayButton = document.querySelector("#autoplay");
    const playIcon = document.querySelector("#play-icon");
    const playLabel = document.querySelector("#play-label");
    const musicAudio = document.querySelector("#music-audio");
    const musicFile = document.querySelector("#music-file");
    const musicPlayButton = document.querySelector("#music-play");
    const musicPlayIcon = document.querySelector("#music-play-icon");
    const musicTitle = document.querySelector("#music-title");
    const musicSeek = document.querySelector("#music-seek");
    const musicTime = document.querySelector("#music-time");
    const musicVolume = document.querySelector("#music-volume");
    const musicStatus = document.querySelector("#music-status");
    const wishlistForm = document.querySelector("#wishlist-form");
    const wishlistInput = document.querySelector("#wishlist-input");
    const wishlistList = document.querySelector("#wishlist-list");
    const wishlistEmpty = document.querySelector("#wishlist-empty");
    const wishlistProgress = document.querySelector("#wishlist-progress");
    const wishlistStatus = document.querySelector("#wishlist-status");
    const wishlistClear = document.querySelector("#wishlist-clear");
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const sakuraCanvas = document.querySelector("#sakura-canvas");
    const sakuraContext = sakuraCanvas.getContext("2d");
    let petals = [];
    let animationFrame;
    let lastFrameTime = 0;
    let wishes = [];
    let activeIndex = 0;
    let isPlaying = !reducedMotion.matches;
    let timer;
    let touchStartX = 0;
    let photoDatabasePromise;
    let photoSelectionIndex = null;
    const customPhotoUrls = new Map();

    const iconMarkup = {
      play: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M5 3.5a.75.75 0 0 1 1.14-.64l6 4a.75.75 0 0 1 0 1.28l-6 4A.75.75 0 0 1 5 11.5z"/></svg>',
      pause: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M4.5 3.5A.5.5 0 0 1 5 3h1.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5zm5 0A.5.5 0 0 1 10 3h1.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H10a.5.5 0 0 1-.5-.5z"/></svg>'
    };

    function resizeSakuraCanvas() {
      const pixelRatio = Math.min(window.devicePixelRatio || 1, 1.5);
      const width = window.innerWidth;
      const height = window.innerHeight;
      sakuraCanvas.width = Math.round(width * pixelRatio);
      sakuraCanvas.height = Math.round(height * pixelRatio);
      sakuraContext.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
      petals = Array.from({ length: width < 700 ? 24 : 42 }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        size: 5 + Math.random() * 7,
        speed: .18 + Math.random() * .42,
        drift: .22 + Math.random() * .58,
        angle: Math.random() * Math.PI * 2,
        spin: (Math.random() - .5) * .012,
        sway: Math.random() * Math.PI * 2,
        opacity: .42 + Math.random() * .33,
        color: Math.random() > .5 ? "#d987a1" : "#e9a6b5"
      }));
      drawSakura();
    }

    function drawSakura() {
      const width = window.innerWidth;
      const height = window.innerHeight;
      sakuraContext.clearRect(0, 0, width, height);

      for (const petal of petals) {
        sakuraContext.save();
        sakuraContext.translate(petal.x, petal.y);
        sakuraContext.rotate(petal.angle);
        sakuraContext.globalAlpha = petal.opacity;
        sakuraContext.fillStyle = petal.color;

        for (let index = 0; index < 5; index += 1) {
          sakuraContext.save();
          sakuraContext.rotate((Math.PI * 2 * index) / 5);
          sakuraContext.beginPath();
          sakuraContext.ellipse(0, -petal.size * .48, petal.size * .31, petal.size * .54, 0, 0, Math.PI * 2);
          sakuraContext.fill();
          sakuraContext.restore();
        }

        sakuraContext.beginPath();
        sakuraContext.arc(0, 0, petal.size * .12, 0, Math.PI * 2);
        sakuraContext.fillStyle = "#fff0d9";
        sakuraContext.fill();
        sakuraContext.restore();
      }
    }

    function animateSakura(timestamp) {
      if (document.hidden) {
        animationFrame = undefined;
        return;
      }

      const delta = lastFrameTime ? Math.min(timestamp - lastFrameTime, 32) : 16;
      lastFrameTime = timestamp;
      const width = window.innerWidth;
      const height = window.innerHeight;

      for (const petal of petals) {
        petal.y += petal.speed * delta;
        petal.x += Math.sin(timestamp * .00045 + petal.sway) * petal.drift * (delta / 16);
        petal.angle += petal.spin * (delta / 16);

        if (petal.y - petal.size > height) {
          petal.y = -petal.size;
          petal.x = Math.random() * width;
        }
        if (petal.x < -petal.size) petal.x = width + petal.size;
        if (petal.x > width + petal.size) petal.x = -petal.size;
      }

      drawSakura();
      animationFrame = window.requestAnimationFrame(animateSakura);
    }

    function startSakuraAnimation() {
      if (reducedMotion.matches || document.hidden || animationFrame !== undefined) return;
      lastFrameTime = 0;
      animationFrame = window.requestAnimationFrame(animateSakura);
    }

    function updateSakuraMotionPreference() {
      if (reducedMotion.matches) {
        if (animationFrame !== undefined) window.cancelAnimationFrame(animationFrame);
        animationFrame = undefined;
        drawSakura();
      } else {
        startSakuraAnimation();
      }
    }

    function formatTime(seconds) {
      if (!Number.isFinite(seconds)) return "0:00";
      const minutes = Math.floor(seconds / 60);
      const remainder = Math.floor(seconds % 60).toString().padStart(2, "0");
      return `${minutes}:${remainder}`;
    }

    function openPhotoDatabase() {
      if (!("indexedDB" in window)) {
        return Promise.reject(new Error("Browser ini tidak mendukung penyimpanan foto."));
      }
      if (photoDatabasePromise) return photoDatabasePromise;

      photoDatabasePromise = new Promise((resolve, reject) => {
        const request = window.indexedDB.open("ruang-cerita-photos", 1);
        request.onupgradeneeded = () => {
          if (!request.result.objectStoreNames.contains("photos")) {
            request.result.createObjectStore("photos", { keyPath: "index" });
          }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error || new Error("Penyimpanan foto tidak dapat dibuka."));
        request.onblocked = () => reject(new Error("Penyimpanan foto sedang diblokir oleh tab browser lain."));
      }).catch((error) => {
        photoDatabasePromise = null;
        throw error;
      });
      return photoDatabasePromise;
    }

    async function loadSavedPhotos() {
      const database = await openPhotoDatabase();
      return new Promise((resolve, reject) => {
        const transaction = database.transaction("photos", "readonly");
        const request = transaction.objectStore("photos").getAll();
        let loadedCount = 0;
        request.onsuccess = () => {
          for (const record of request.result) {
            if (!Number.isInteger(record.index) || !memories[record.index] || !(record.blob instanceof Blob)) {
              transaction.abort();
              reject(new Error("Data foto yang tersimpan tidak valid."));
              return;
            }
            const imageUrl = URL.createObjectURL(record.blob);
            customPhotoUrls.set(record.index, imageUrl);
            memories[record.index].image = imageUrl;
            memories[record.index].alt = record.name
              ? `Foto kenangan: ${record.name}`
              : memories[record.index].alt;
            loadedCount += 1;
          }
        };
        transaction.oncomplete = () => {
          database.close();
          photoDatabasePromise = null;
          resolve(loadedCount);
        };
        transaction.onerror = () => {
          database.close();
          photoDatabasePromise = null;
          reject(transaction.error || new Error("Foto tersimpan tidak dapat dibaca."));
        };
        transaction.onabort = () => {
          database.close();
          photoDatabasePromise = null;
          reject(transaction.error || new Error("Pembacaan foto tersimpan dibatalkan."));
        };
      });
    }

    async function storePhoto(index, file) {
      const database = await openPhotoDatabase();
      return new Promise((resolve, reject) => {
        const transaction = database.transaction("photos", "readwrite");
        transaction.objectStore("photos").put({
          index,
          blob: file,
          name: file.name
        });
        transaction.oncomplete = () => {
          database.close();
          photoDatabasePromise = null;
          resolve();
        };
        transaction.onerror = () => {
          database.close();
          photoDatabasePromise = null;
          reject(transaction.error || new Error("Foto tidak dapat disimpan."));
        };
        transaction.onabort = () => {
          database.close();
          photoDatabasePromise = null;
          reject(transaction.error || new Error("Penyimpanan foto dibatalkan."));
        };
      });
    }

    async function initializeGallery() {
      try {
        const loadedCount = await loadSavedPhotos();
        if (loadedCount > 0) {
          photoStatus.textContent = `${loadedCount} foto pilihan tersimpan di browser ini. Pilih foto lalu tekan “Ganti foto” untuk memasang foto lain.`;
        }
      } catch (error) {
        photoStatus.textContent = "Foto bawaan ditampilkan, tetapi foto tersimpan tidak dapat dimuat. Periksa izin penyimpanan browser.";
        photoStatus.classList.add("is-error");
        console.error("Gagal memuat foto galeri:", error);
      }
      buildGallery();
    }

    function updateMusicTime() {
      musicTime.textContent = `${formatTime(musicAudio.currentTime)} / ${formatTime(musicAudio.duration)}`;
      musicSeek.value = Number.isFinite(musicAudio.duration) && musicAudio.duration > 0
        ? String((musicAudio.currentTime / musicAudio.duration) * 100)
        : "0";
    }

    function setMusicPlaying(playing) {
      musicPlayIcon.innerHTML = playing ? iconMarkup.pause : iconMarkup.play;
      musicPlayButton.setAttribute("aria-label", playing ? "Jeda lagu" : "Putar lagu");
    }

    function saveWishes() {
      try {
        localStorage.setItem("ruang-cerita-wishlist-v1", JSON.stringify(wishes));
        wishlistStatus.textContent = "Daftar tersimpan otomatis di browser ini dan bisa dibuka bersama di perangkat yang sama.";
        wishlistStatus.classList.remove("is-error");
      } catch (error) {
        wishlistStatus.textContent = "Perubahan tersimpan selama halaman terbuka, tetapi browser tidak mengizinkan penyimpanan permanen.";
        wishlistStatus.classList.add("is-error");
        console.error("Gagal menyimpan wishlist ke browser:", error);
      }
    }

    function createWishId() {
      return `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
    }

    function renderWishes() {
      wishlistList.replaceChildren();

      wishes.forEach((wish) => {
        const item = document.createElement("li");
        item.className = `wishlist-item${wish.done ? " is-done" : ""}`;

        const checkbox = document.createElement("input");
        checkbox.className = "wishlist-check";
        checkbox.type = "checkbox";
        checkbox.checked = wish.done;
        checkbox.setAttribute("aria-label", `Tandai "${wish.text}" sebagai ${wish.done ? "belum terwujud" : "terwujud"}`);
        checkbox.addEventListener("change", () => {
          wish.done = checkbox.checked;
          saveWishes();
          renderWishes();
        });

        const text = document.createElement("span");
        text.className = "wishlist-item-text";
        text.textContent = wish.text;

        const remove = document.createElement("button");
        remove.className = "wishlist-delete";
        remove.type = "button";
        remove.setAttribute("aria-label", `Hapus rencana "${wish.text}"`);
        remove.innerHTML = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3.5 4.5h9M6 4.5V3h4v1.5m1.5 0-.5 8h-6l-.5-8m2 2v4m2-4v4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        remove.addEventListener("click", () => {
          wishes = wishes.filter((entry) => entry.id !== wish.id);
          saveWishes();
          renderWishes();
        });

        item.append(checkbox, text, remove);
        wishlistList.append(item);
      });

      const completed = wishes.filter((wish) => wish.done).length;
      wishlistProgress.textContent = `${completed}/${wishes.length}`;
      wishlistEmpty.hidden = wishes.length > 0;
      wishlistClear.classList.toggle("is-visible", completed > 0);
    }

    function addWish(text) {
      const normalizedText = text.trim().replace(/\s+/g, " ");
      if (!normalizedText) return;

      wishes.unshift({ id: createWishId(), text: normalizedText, done: false });
      saveWishes();
      renderWishes();
      wishlistInput.value = "";
      wishlistInput.focus();
    }

    function loadWishes() {
      try {
        const storedWishes = localStorage.getItem("ruang-cerita-wishlist-v1");
        if (storedWishes) {
          const parsedWishes = JSON.parse(storedWishes);
          if (!Array.isArray(parsedWishes) || parsedWishes.some((wish) =>
            !wish || typeof wish.id !== "string" || typeof wish.text !== "string" || typeof wish.done !== "boolean"
          )) {
            throw new TypeError("Format wishlist yang tersimpan tidak valid.");
          }
          wishes = parsedWishes;
        }
      } catch (error) {
        wishlistStatus.textContent = "Wishlist sebelumnya tidak dapat dibaca. Tambahkan rencana baru; jika masalah berlanjut, periksa penyimpanan browser.";
        wishlistStatus.classList.add("is-error");
        console.error("Gagal memuat wishlist dari browser:", error);
      }
      renderWishes();
    }

    function buildGallery() {
      totalCount.textContent = String(memories.length).padStart(2, "0");
      memories.forEach((memory, index) => {
        const card = document.createElement("article");
        card.className = "photo-card";
        card.setAttribute("aria-label", memory.title);

        const image = document.createElement("img");
        image.src = memory.image;
        image.alt = memory.alt;
        image.loading = index === 0 ? "eager" : "lazy";
        image.draggable = false;

        const editButton = document.createElement("button");
        editButton.className = "photo-edit-button";
        editButton.type = "button";
        editButton.setAttribute("aria-label", `Ganti foto: ${memory.title}`);
        editButton.innerHTML = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3.5 7.25A1.75 1.75 0 0 1 5.25 5.5h1.07l.8-1.2h5.76l.8 1.2h1.07a1.75 1.75 0 0 1 1.75 1.75v6.5a1.75 1.75 0 0 1-1.75 1.75h-9.5a1.75 1.75 0 0 1-1.75-1.75z" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/><circle cx="10" cy="10.5" r="2.5" stroke="currentColor" stroke-width="1.25"/></svg><span>Ganti foto</span>';
        editButton.addEventListener("click", (event) => {
          event.stopPropagation();
          photoSelectionIndex = index;
          photoFile.click();
        });

        const caption = document.createElement("div");
        caption.className = "photo-caption";
        const date = document.createElement("span");
        date.textContent = memory.date;
        const title = document.createElement("h2");
        title.textContent = memory.title;
        const detail = document.createElement("p");
        detail.textContent = memory.detail;
        caption.append(date, title, detail);
        card.append(image, editButton, caption);
        stage.append(card);

        const thumbnail = document.createElement("button");
        thumbnail.className = "thumbnail";
        thumbnail.type = "button";
        thumbnail.setAttribute("aria-label", `Tampilkan foto ${index + 1}: ${memory.title}`);
        const thumbnailImage = document.createElement("img");
        thumbnailImage.src = memory.image.startsWith("blob:")
          ? memory.image
          : memory.image.replace("w=1200", "w=120");
        thumbnailImage.alt = "";
        thumbnailImage.loading = "lazy";
        thumbnail.append(thumbnailImage);
        thumbnail.addEventListener("click", () => showMemory(index));
        thumbnails.append(thumbnail);
      });
      showMemory(0);
    }

    function showMemory(index) {
      activeIndex = (index + memories.length) % memories.length;
      const cards = stage.querySelectorAll(".photo-card");
      const buttons = thumbnails.querySelectorAll(".thumbnail");

      cards.forEach((card, cardIndex) => {
        let offset = cardIndex - activeIndex;
        if (offset > memories.length / 2) offset -= memories.length;
        if (offset < -memories.length / 2) offset += memories.length;
        const distance = Math.abs(offset);
        card.style.setProperty("--offset", offset);
        card.style.setProperty("--depth", distance * 90);
        card.style.setProperty("--scale", distance === 0 ? 1 : distance === 1 ? .82 : .67);
        card.style.setProperty("--opacity", distance === 0 ? 1 : distance === 1 ? .7 : .3);
        card.style.zIndex = String(memories.length - distance);
        card.classList.toggle("is-active", distance === 0);
        card.setAttribute("aria-hidden", String(distance !== 0));
        card.tabIndex = distance === 0 ? 0 : -1;
        const editButton = card.querySelector(".photo-edit-button");
        editButton.tabIndex = distance === 0 ? 0 : -1;
        editButton.setAttribute("aria-hidden", String(distance !== 0));
      });

      buttons.forEach((button, buttonIndex) => {
        const selected = buttonIndex === activeIndex;
        button.classList.toggle("is-active", selected);
        button.setAttribute("aria-pressed", String(selected));
      });

      currentCount.textContent = String(activeIndex + 1).padStart(2, "0");
    }

    function setAutoplay(playing) {
      isPlaying = playing;
      window.clearInterval(timer);
      playIcon.innerHTML = isPlaying ? iconMarkup.pause : iconMarkup.play;
      playLabel.textContent = isPlaying ? "Jeda otomatis" : "Putar otomatis";
      autoplayButton.setAttribute("aria-label", isPlaying ? "Jeda pergantian foto otomatis" : "Putar pergantian foto otomatis");
      if (isPlaying) timer = window.setInterval(() => showMemory(activeIndex + 1), 5000);
    }

    document.querySelector("#previous").addEventListener("click", () => showMemory(activeIndex - 1));
    document.querySelector("#next").addEventListener("click", () => showMemory(activeIndex + 1));
    autoplayButton.addEventListener("click", () => setAutoplay(!isPlaying));
    photoFile.addEventListener("change", async () => {
      const file = photoFile.files[0];
      const index = photoSelectionIndex;
      photoFile.value = "";
      photoSelectionIndex = null;
      if (!file || index === null) return;
      if (!file.type.startsWith("image/")) {
        photoStatus.textContent = "File yang dipilih bukan gambar. Pilih foto dalam format yang didukung.";
        photoStatus.classList.add("is-error");
        return;
      }

      photoStatus.textContent = "Menyimpan foto baru...";
      photoStatus.classList.remove("is-error");
      try {
        await storePhoto(index, file);
        const imageUrl = URL.createObjectURL(file);
        const previousUrl = customPhotoUrls.get(index);
        if (previousUrl) URL.revokeObjectURL(previousUrl);
        customPhotoUrls.set(index, imageUrl);
        memories[index].image = imageUrl;
        memories[index].alt = `Foto kenangan: ${file.name}`;

        const card = stage.querySelectorAll(".photo-card")[index];
        card.querySelector("img").src = imageUrl;
        card.querySelector("img").alt = memories[index].alt;
        thumbnails.querySelectorAll(".thumbnail img")[index].src = imageUrl;
        photoStatus.textContent = `Foto "${file.name}" berhasil diganti dan disimpan di browser ini.`;
      } catch (error) {
        photoStatus.textContent = "Foto tidak dapat disimpan. Periksa ruang penyimpanan browser, lalu coba lagi.";
        photoStatus.classList.add("is-error");
        console.error("Gagal menyimpan foto pengganti:", error);
      }
    });
    wishlistForm.addEventListener("submit", (event) => {
      event.preventDefault();
      addWish(wishlistInput.value);
    });
    document.querySelectorAll(".wishlist-suggestion").forEach((button) => {
      button.addEventListener("click", () => addWish(button.dataset.wish));
    });
    wishlistClear.addEventListener("click", () => {
      wishes = wishes.filter((wish) => !wish.done);
      saveWishes();
      renderWishes();
    });
    window.addEventListener("storage", (event) => {
      if (event.key !== "ruang-cerita-wishlist-v1") return;
      loadWishes();
      wishlistStatus.textContent = "Wishlist diperbarui dari tab lain di browser ini.";
      wishlistStatus.classList.remove("is-error");
    });
    musicFile.addEventListener("change", () => {
      const file = musicFile.files[0];
      if (!file) return;
      if (!file.type.startsWith("audio/")) {
        musicStatus.textContent = "File tidak dikenali sebagai audio. Silakan pilih file lagu.";
        musicStatus.classList.add("is-error");
        musicFile.value = "";
        return;
      }

      const previousUrl = musicAudio.dataset.objectUrl;
      if (previousUrl) URL.revokeObjectURL(previousUrl);
      const objectUrl = URL.createObjectURL(file);
      musicAudio.dataset.objectUrl = objectUrl;
      musicAudio.src = objectUrl;
      musicAudio.load();
      musicAudio.pause();
      setMusicPlaying(false);
      musicPlayButton.disabled = false;
      musicSeek.disabled = false;
      musicTitle.textContent = file.name;
      musicStatus.textContent = "Lagu siap diputar. Pemutaran hanya menggunakan file di perangkatmu.";
      musicStatus.classList.remove("is-error");
      updateMusicTime();
    });
    musicPlayButton.addEventListener("click", async () => {
      if (musicAudio.paused) {
        try {
          await musicAudio.play();
          musicStatus.textContent = "Sedang memutar lagu.";
          musicStatus.classList.remove("is-error");
        } catch (error) {
          musicStatus.textContent = "Lagu tidak dapat diputar. Coba pilih file audio lain.";
          musicStatus.classList.add("is-error");
          console.error("Gagal memutar file lagu:", error);
        }
      } else {
        musicAudio.pause();
      }
    });
    musicAudio.addEventListener("play", () => setMusicPlaying(true));
    musicAudio.addEventListener("pause", () => setMusicPlaying(false));
    musicAudio.addEventListener("timeupdate", updateMusicTime);
    musicAudio.addEventListener("loadedmetadata", updateMusicTime);
    musicAudio.volume = Number(musicVolume.value);
    musicVolume.addEventListener("input", () => {
      musicAudio.volume = Number(musicVolume.value);
    });
    musicAudio.addEventListener("ended", () => {
      setMusicPlaying(false);
      musicStatus.textContent = "Lagu selesai diputar.";
    });
    musicAudio.addEventListener("error", () => {
      musicStatus.textContent = "File lagu tidak dapat dibaca. Coba format audio lain.";
      musicStatus.classList.add("is-error");
    });
    musicSeek.addEventListener("input", () => {
      if (Number.isFinite(musicAudio.duration)) {
        musicAudio.currentTime = (Number(musicSeek.value) / 100) * musicAudio.duration;
      }
    });
    stage.addEventListener("click", (event) => {
      const card = event.target.closest(".photo-card");
      if (!card || card.classList.contains("is-active")) return;
      showMemory(Array.from(stage.children).indexOf(card));
    });
    stage.addEventListener("touchstart", (event) => {
      touchStartX = event.changedTouches[0].clientX;
    }, { passive: true });
    stage.addEventListener("touchend", (event) => {
      const distance = event.changedTouches[0].clientX - touchStartX;
      if (Math.abs(distance) > 45) showMemory(activeIndex + (distance < 0 ? 1 : -1));
    }, { passive: true });
    document.addEventListener("keydown", (event) => {
      if (event.altKey || event.ctrlKey || event.metaKey) return;
      if (event.key === "ArrowLeft") showMemory(activeIndex - 1);
      if (event.key === "ArrowRight") showMemory(activeIndex + 1);
    });

    window.addEventListener("resize", resizeSakuraCanvas);
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        if (animationFrame !== undefined) window.cancelAnimationFrame(animationFrame);
        animationFrame = undefined;
      } else {
        startSakuraAnimation();
      }
    });
    reducedMotion.addEventListener("change", updateSakuraMotionPreference);
    resizeSakuraCanvas();
    startSakuraAnimation();
    initializeGallery();
    setAutoplay(isPlaying);
    loadWishes();
  </script>
</body>
</html>
