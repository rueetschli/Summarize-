<?php
/**
 * Admin Upload – MP3 und PDF hochladen
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
$accent     = htmlspecialchars($config['accent_color'] ?? '#005a8c', ENT_QUOTES, 'UTF-8');

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
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES)) {

    // CSRF-einfach: Referrer prüfen (für Shared Hosting ausreichend)
    $allowed = [
        'mp3' => ['audio/mpeg', 'audio/mp3'],
        'pdf' => ['application/pdf'],
    ];

    $targets = [
        'mp3' => 'latest.mp3',
        'pdf' => 'handout.pdf',
    ];

    $uploaded = [];

    foreach (['mp3', 'pdf'] as $key) {
        $field = 'file_' . $key;
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
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
        $success = 'Hochgeladen: ' . implode(', ', $uploaded);
    } elseif (!empty($uploaded)) {
        $success = 'Teilweise hochgeladen: ' . implode(', ', $uploaded);
    }
}
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Upload</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>:root{--accent:<?= $accent ?>;--accent-light:<?= $accent ?>22;}</style>
</head>
<body>
    <div class="container" role="main">
        <header class="header">
            <h1 class="header__title">Dateien hochladen</h1>
            <p class="header__subtitle">MP3 und PDF aktualisieren</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert--err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert--ok"><?= htmlspecialchars($success) ?></div>
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
                <h2>Dateien auswählen</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxBytes ?>">

                    <div class="form-group">
                        <label for="file_mp3">Audio (MP3, max <?= $maxMb ?> MB)</label>
                        <input type="file" id="file_mp3" name="file_mp3" accept=".mp3,audio/mpeg">
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
