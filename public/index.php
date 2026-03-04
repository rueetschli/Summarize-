<?php
$config = require __DIR__ . '/config.php';

$title       = htmlspecialchars($config['title'] ?? 'Vorlesungs-Zusammenfassungen', ENT_QUOTES, 'UTF-8');
$className   = htmlspecialchars($config['class_name'] ?? '', ENT_QUOTES, 'UTF-8');
$subtitle    = htmlspecialchars($config['subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
$dlEnabled   = !empty($config['enable_audio_download'] ?? $config['enable_mp3_download'] ?? false);
$uploadOn    = !empty($config['enable_upload']);
$accent      = htmlspecialchars($config['accent_color'] ?? '#6366f1', ENT_QUOTES, 'UTF-8');
$footer      = htmlspecialchars($config['footer_text'] ?? '', ENT_QUOTES, 'UTF-8');
$lectures    = $config['lectures'] ?? [];
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <style>:root{--accent:<?= $accent ?>;--accent-light:<?= $accent ?>22;--accent-glow:<?= $accent ?>44;}</style>
</head>
<body>
    <div class="bg-effects" aria-hidden="true">
        <div class="bg-orb bg-orb--1"></div>
        <div class="bg-orb bg-orb--2"></div>
        <div class="bg-orb bg-orb--3"></div>
    </div>

    <div class="container" role="main">
        <!-- Hero Header -->
        <header class="hero">
            <div class="hero__badge"><?= $className ?></div>
            <h1 class="hero__title"><?= $title ?></h1>
            <?php if ($subtitle): ?>
                <p class="hero__subtitle"><?= $subtitle ?></p>
            <?php endif; ?>
        </header>

        <?php if (empty($lectures)): ?>
            <p class="empty-msg">Noch keine Module vorhanden.</p>
        <?php else: ?>

        <!-- Module Selection -->
        <section class="module-section">
            <h2 class="section-label">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                Modul wählen
            </h2>
            <nav class="module-list" aria-label="Module">
                <?php foreach ($lectures as $i => $lec): ?>
                    <button class="module-card<?= $i === 0 ? ' is-active' : '' ?>"
                            data-index="<?= $i ?>"
                            aria-current="<?= $i === 0 ? 'true' : 'false' ?>">
                        <span class="module-card__index"><?= $i + 1 ?></span>
                        <span class="module-card__body">
                            <span class="module-card__title"><?= htmlspecialchars($lec['title'] ?? 'Modul ' . ($i + 1), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($lec['date'])): ?>
                                <span class="module-card__meta"><?= htmlspecialchars($lec['date'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="module-card__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </span>
                    </button>
                <?php endforeach; ?>
            </nav>
        </section>

        <!-- Audio Player -->
        <section class="player" aria-label="Audio-Player">
            <div class="player__header">
                <div class="player__visualizer" id="visualizer" aria-hidden="true">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
                <div class="player__now-playing" id="nowPlaying">
                    <?= htmlspecialchars($lectures[0]['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
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
                <button class="btn btn--icon" id="btnPrev" aria-label="Vorheriges Modul" title="Vorheriges Modul">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/>
                    </svg>
                </button>

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

                <button class="btn btn--icon" id="btnNext" aria-label="Nächstes Modul" title="Nächstes Modul">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
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
            <div class="player__actions" id="playerActions">
                <!-- Filled dynamically by JS -->
            </div>
        </section>

        <?php endif; ?>

        <?php if ($uploadOn): ?>
            <p class="admin-link"><a href="admin/upload.php">Admin: Dateien hochladen</a></p>
        <?php endif; ?>

        <footer class="footer"><?= $footer ?></footer>
    </div>

    <script>
        window.PLAYER_CONFIG = {
            lectures: <?= json_encode(array_map(function ($lec) {
                return [
                    'title'      => $lec['title'] ?? '',
                    'audio_path' => $lec['audio_path'] ?? $lec['mp3_path'] ?? '',
                    'pdf_path'   => $lec['pdf_path'] ?? '',
                    'date'       => $lec['date'] ?? '',
                ];
            }, $lectures)) ?>,
            enableDownload: <?= json_encode($dlEnabled) ?>
        };
    </script>
    <script src="js/player.js"></script>
</body>
</html>
