<?php
$config = require __DIR__ . '/config.php';

$title       = htmlspecialchars($config['title'] ?? 'Audio & Handout', ENT_QUOTES, 'UTF-8');
$subtitle    = htmlspecialchars($config['subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
$mp3         = htmlspecialchars($config['mp3_path'] ?? '', ENT_QUOTES, 'UTF-8');
$pdf         = htmlspecialchars($config['pdf_path'] ?? '', ENT_QUOTES, 'UTF-8');
$dlEnabled   = !empty($config['enable_mp3_download']);
$uploadOn    = !empty($config['enable_upload']);
$accent      = htmlspecialchars($config['accent_color'] ?? '#005a8c', ENT_QUOTES, 'UTF-8');
$footer      = htmlspecialchars($config['footer_text'] ?? '', ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <style>:root{--accent:<?= $accent ?>;--accent-light:<?= $accent ?>22;}</style>
</head>
<body>
    <div class="container" role="main">
        <!-- Header -->
        <header class="header">
            <h1 class="header__title"><?= $title ?></h1>
            <?php if ($subtitle): ?>
                <p class="header__subtitle"><?= $subtitle ?></p>
            <?php endif; ?>
        </header>

        <!-- Audio Player -->
        <section class="player" aria-label="Audio-Player">
            <div class="player__status" id="playerStatus">Audio wird geladen…</div>

            <!-- Waveform / Progress -->
            <div class="player__progress-wrap" id="progressWrap" role="slider"
                 aria-label="Wiedergabeposition" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
                <div class="player__buffered" id="buffered"></div>
                <div class="player__progress" id="progress"></div>
                <div class="player__thumb" id="thumb"></div>
            </div>

            <!-- Time -->
            <div class="player__time">
                <span id="currentTime">0:00</span>
                <span id="duration">0:00</span>
            </div>

            <!-- Controls -->
            <div class="player__controls">
                <button class="btn btn--icon" id="btnBack15" aria-label="15 Sekunden zurück" title="15 s zurück">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                        <text x="12" y="16" text-anchor="middle" fill="currentColor" stroke="none" font-size="7" font-weight="bold">15</text>
                    </svg>
                </button>

                <button class="btn btn--play" id="btnPlay" aria-label="Abspielen" title="Abspielen / Pause">
                    <svg id="iconPlay" viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
                        <polygon points="6,3 20,12 6,21"/>
                    </svg>
                    <svg id="iconPause" viewBox="0 0 24 24" width="36" height="36" fill="currentColor" class="hidden">
                        <rect x="5" y="3" width="4" height="18"/><rect x="15" y="3" width="4" height="18"/>
                    </svg>
                </button>

                <button class="btn btn--icon" id="btnFwd15" aria-label="15 Sekunden vor" title="15 s vor">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.13-9.36L23 10"/>
                        <text x="12" y="16" text-anchor="middle" fill="currentColor" stroke="none" font-size="7" font-weight="bold">15</text>
                    </svg>
                </button>
            </div>

            <!-- Volume -->
            <div class="player__volume">
                <button class="btn btn--icon btn--small" id="btnMute" aria-label="Stumm schalten" title="Stumm / Laut">
                    <svg id="iconVol" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"/>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                    </svg>
                    <svg id="iconMute" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"/>
                        <line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/>
                    </svg>
                </button>
                <input type="range" id="volumeSlider" class="player__volume-slider"
                       min="0" max="1" step="0.01" value="1"
                       aria-label="Lautstärke">
            </div>

            <!-- Download buttons -->
            <div class="player__actions">
                <?php if ($dlEnabled): ?>
                    <a href="<?= $mp3 ?>" download class="btn btn--action" aria-label="MP3 herunterladen">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        MP3 herunterladen
                    </a>
                <?php endif; ?>

                <?php if ($pdf): ?>
                    <a href="<?= $pdf ?>" download class="btn btn--action btn--accent" aria-label="Handout herunterladen">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Handout herunterladen
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($uploadOn): ?>
            <p class="admin-link"><a href="admin/upload.php">Admin: Dateien hochladen</a></p>
        <?php endif; ?>

        <footer class="footer"><?= $footer ?></footer>
    </div>

    <script>
        // MP3-Pfad für den Player bereitstellen
        window.PLAYER_CONFIG = { mp3: <?= json_encode($config['mp3_path'] ?? '') ?> };
    </script>
    <script src="js/player.js"></script>
</body>
</html>
