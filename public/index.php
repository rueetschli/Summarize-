<?php
$config = require __DIR__ . '/config.php';

$title       = htmlspecialchars($config['title'] ?? 'Vorlesungs-Zusammenfassungen', ENT_QUOTES, 'UTF-8');
$subtitle    = htmlspecialchars($config['subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
$dlEnabled   = !empty($config['enable_mp3_download']);
$uploadOn    = !empty($config['enable_upload']);
$accent      = htmlspecialchars($config['accent_color'] ?? '#005a8c', ENT_QUOTES, 'UTF-8');
$footer      = htmlspecialchars($config['footer_text'] ?? '', ENT_QUOTES, 'UTF-8');
$lectures    = $config['lectures'] ?? [];
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

        <?php if (empty($lectures)): ?>
            <p class="empty-msg">Noch keine Vorlesungen vorhanden.</p>
        <?php else: ?>

        <!-- Lecture List -->
        <nav class="lecture-list" aria-label="Vorlesungen">
            <?php foreach ($lectures as $i => $lec): ?>
                <button class="lecture-item<?= $i === 0 ? ' is-active' : '' ?>"
                        data-index="<?= $i ?>"
                        aria-current="<?= $i === 0 ? 'true' : 'false' ?>">
                    <span class="lecture-item__number"><?= $i + 1 ?></span>
                    <span class="lecture-item__info">
                        <span class="lecture-item__title"><?= htmlspecialchars($lec['title'] ?? 'Vorlesung ' . ($i + 1), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (!empty($lec['date'])): ?>
                            <span class="lecture-item__date"><?= htmlspecialchars($lec['date'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="lecture-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="6,3 20,12 6,21" fill="currentColor"/>
                        </svg>
                    </span>
                </button>
            <?php endforeach; ?>
        </nav>

        <!-- Audio Player -->
        <section class="player" aria-label="Audio-Player">
            <div class="player__now-playing" id="nowPlaying">
                <?= htmlspecialchars($lectures[0]['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
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
                <button class="btn btn--icon" id="btnPrev" aria-label="Vorherige Vorlesung" title="Vorherige Vorlesung">
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

                <button class="btn btn--icon" id="btnNext" aria-label="Nächste Vorlesung" title="Nächste Vorlesung">
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
                    'title'    => $lec['title'] ?? '',
                    'mp3_path' => $lec['mp3_path'] ?? '',
                    'pdf_path' => $lec['pdf_path'] ?? '',
                    'date'     => $lec['date'] ?? '',
                ];
            }, $lectures)) ?>,
            enableDownload: <?= json_encode($dlEnabled) ?>
        };
    </script>
    <script src="js/player.js"></script>
</body>
</html>
