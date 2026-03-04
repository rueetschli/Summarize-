<?php
/**
 * Admin Upload – Audio (MP3/M4A) und PDF für einzelne Module hochladen
 * Session-basierte Passwort-Authentifizierung
 */
session_start();

$config = require dirname(__DIR__) . '/config.php';

// Upload deaktiviert?
if (empty($config['enable_upload'])) {
    http_response_code(403);
    exit('Upload ist deaktiviert.');
}

$password   = $config['upload_password'] ?? '';
$maxMb      = (int)($config['max_upload_mb'] ?? 50);
$maxBytes   = $maxMb * 1024 * 1024;
$uploadDir  = dirname(__DIR__) . '/assets/uploads/';
$accent     = htmlspecialchars($config['accent_color'] ?? '#6366f1', ENT_QUOTES, 'UTF-8');
$lectures   = $config['lectures'] ?? [];

$error   = '';
$success = '';

// ── Logout ──────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    unset($_SESSION['upload_auth']);
    header('Location: upload.php');
    exit;
}

// ── Login prüfen ────────────────────────────────────────────
$loggedIn = !empty($_SESSION['upload_auth']);

if (!$loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (hash_equals($password, $_POST['password'])) {
        $_SESSION['upload_auth'] = true;
        $loggedIn = true;
    } else {
        $error = 'Falsches Passwort.';
    }
}

// ── Upload verarbeiten ──────────────────────────────────────
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lecture_index'])) {
    $lectureIndex = (int)$_POST['lecture_index'];

    if ($lectureIndex < 0 || $lectureIndex >= count($lectures)) {
        $error = 'Ungültiges Modul gewählt.';
    } else {
        $allowed = [
            'audio' => ['audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/x-m4a', 'audio/m4a', 'audio/aac'],
            'pdf'   => ['application/pdf'],
        ];

        $lec = $lectures[$lectureIndex];
        $audioPath = $lec['audio_path'] ?? $lec['mp3_path'] ?? '';
        $targets = [
            'audio' => basename($audioPath),
            'pdf'   => basename($lec['pdf_path'] ?? ''),
        ];

        $uploaded = [];

        foreach (['audio', 'pdf'] as $key) {
            $field = 'file_' . $key;
            if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (empty($targets[$key])) {
                continue;
            }

            $file = $_FILES[$field];

            // Upload-Fehler
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error .= strtoupper($key) . ': Upload-Fehler (Code ' . $file['error'] . '). ';
                continue;
            }

            // Dateigrösse
            if ($file['size'] > $maxBytes) {
                $error .= strtoupper($key) . ': Datei zu gross (max ' . $maxMb . ' MB). ';
                continue;
            }

            // MIME prüfen
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!in_array($mime, $allowed[$key], true)) {
                $error .= strtoupper($key) . ': Ungültiger Dateityp (' . htmlspecialchars($mime) . '). ';
                continue;
            }

            // Zielverzeichnis erstellen
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $dest = $uploadDir . $targets[$key];

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $error .= strtoupper($key) . ': Konnte Datei nicht speichern. ';
                continue;
            }

            $uploaded[] = $targets[$key];
        }

        if (!empty($uploaded) && $error === '') {
            $success = 'Hochgeladen für «' . htmlspecialchars($lec['title'] ?? 'Modul', ENT_QUOTES, 'UTF-8') . '»: ' . implode(', ', $uploaded);
        } elseif (!empty($uploaded)) {
            $success = 'Teilweise hochgeladen: ' . implode(', ', $uploaded);
        }
    }
}
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Upload</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>:root{--accent:<?= $accent ?>;--accent-light:<?= $accent ?>22;--accent-glow:<?= $accent ?>44;}</style>
</head>
<body>
    <div class="container" role="main">
        <header class="header">
            <h1 class="header__title">Dateien hochladen</h1>
            <p class="header__subtitle">Audio (MP3/M4A) und PDF für Module aktualisieren</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert--err"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert--ok"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!$loggedIn): ?>
            <!-- Login -->
            <div class="upload-card">
                <h2>Anmelden</h2>
                <form method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="password">Passwort</label>
                        <input type="password" id="password" name="password" required autofocus>
                    </div>
                    <button type="submit" class="btn btn--submit">Anmelden</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Upload Form -->
            <div class="upload-card">
                <h2>Modul wählen & Dateien hochladen</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxBytes ?>">

                    <div class="form-group">
                        <label for="lecture_index">Modul</label>
                        <select id="lecture_index" name="lecture_index" required>
                            <?php foreach ($lectures as $i => $lec): ?>
                                <option value="<?= $i ?>"><?= htmlspecialchars($lec['title'] ?? 'Modul ' . ($i + 1), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file_audio">Audio (MP3 oder M4A, max <?= $maxMb ?> MB)</label>
                        <input type="file" id="file_audio" name="file_audio" accept=".mp3,.m4a,audio/mpeg,audio/mp4">
                    </div>

                    <div class="form-group">
                        <label for="file_pdf">Handout (PDF, max <?= $maxMb ?> MB)</label>
                        <input type="file" id="file_pdf" name="file_pdf" accept=".pdf,application/pdf">
                    </div>

                    <button type="submit" class="btn btn--submit">Hochladen</button>
                </form>
            </div>

            <p class="admin-link">
                <a href="../">Zurück zur Seite</a> · <a href="?logout">Abmelden</a>
            </p>
        <?php endif; ?>

        <footer class="footer"><?= htmlspecialchars($config['footer_text'] ?? '') ?></footer>
    </div>
</body>
</html>
