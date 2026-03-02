# Audio-Player & Handout

Responsiver Audio-Player (MP3) mit PDF-Handout-Download fuer Shared Hosting (Apache/PHP 8).

## Schnellstart

1. **Alle Dateien per FTP hochladen** – den gesamten Inhalt dieses Ordners in ein Verzeichnis auf dem Webserver kopieren (z.B. `/vortrag/`).
2. **MP3 und PDF bereitstellen** – die Dateien in `assets/uploads/` ablegen:
   - `assets/uploads/latest.mp3` (Audio-Zusammenfassung)
   - `assets/uploads/handout.pdf` (PDF-Handout)
3. **`config.php` anpassen** – Titel, Untertitel und Pfade aendern.
4. **Seite im Browser oeffnen** – z.B. `https://meine-domain.ch/vortrag/`.

## Projektstruktur

```
public/
  index.php              Hauptseite mit Audio-Player
  config.php             Alle Einstellungen
  .htaccess              Sicherheit & MIME-Types
  css/styles.css         Stylesheet
  js/player.js           Player-Logik
  assets/uploads/        MP3 & PDF hier ablegen
    .htaccess            Verhindert PHP-Ausfuehrung
  admin/upload.php       Optionales Upload-Formular
```

## Konfiguration (config.php)

| Einstellung          | Beschreibung                              | Standard                     |
|----------------------|-------------------------------------------|------------------------------|
| `title`              | Seitentitel                               | `Zusammenfassung: Vortrag X` |
| `subtitle`           | Untertitel / Beschreibung                 | (optional)                   |
| `mp3_path`           | Pfad zur MP3 (relativ)                    | `assets/uploads/latest.mp3`  |
| `pdf_path`           | Pfad zum PDF (relativ)                    | `assets/uploads/handout.pdf` |
| `enable_mp3_download`| MP3-Download-Button anzeigen              | `true`                       |
| `enable_upload`      | Upload-Formular aktivieren                | `false`                      |
| `upload_password`    | Passwort fuer Upload-Seite                | (muss gesetzt werden)        |
| `max_upload_mb`      | Maximale Upload-Groesse in MB             | `50`                         |
| `accent_color`       | Akzentfarbe (CSS)                         | `#005a8c`                    |
| `footer_text`        | Text in der Fusszeile                     | (c) [Jahr] WSF               |

## Upload-Modus aktivieren

1. In `config.php` setzen: `'enable_upload' => true`
2. Ein sicheres Passwort setzen: `'upload_password' => 'mein-geheimes-passwort'`
3. Upload-Seite oeffnen: `https://meine-domain.ch/vortrag/admin/upload.php`
4. Nach dem Upload werden Dateien automatisch als `latest.mp3` und `handout.pdf` gespeichert.

**Wichtig:** Das Passwort steht im Klartext in `config.php`. Die Datei ist per `.htaccess` vor direktem Zugriff geschuetzt, aber auf dem Server lesbar. Fuer hoehere Sicherheit kann das Passwort als Hash gespeichert werden.

## Haeufige Fehler

| Problem                         | Loesung                                                          |
|----------------------------------|------------------------------------------------------------------|
| 404 bei MP3/PDF                  | Pfad in `config.php` pruefen, Datei in `assets/uploads/` vorhanden? |
| Audio spielt nicht               | MIME-Type pruefen: `.htaccess` muss `AddType audio/mpeg .mp3` enthalten |
| Upload schlaegt fehl             | `php.ini`: `upload_max_filesize` und `post_max_size` erhoehen    |
| Rechte-Fehler beim Upload        | `assets/uploads/` braucht Schreibrechte (chmod 755 oder 775)     |
| Seite zeigt PHP-Code             | PHP muss auf dem Server aktiviert sein (mind. PHP 8.0)           |
| Directory Listing sichtbar       | `.htaccess` mit `Options -Indexes` muss vorhanden sein           |

## MIME-Types & Browser-Playback

Moderne Browser spielen MP3 nativ ab. Falls nicht:
- Stelle sicher, dass der Server `audio/mpeg` als MIME-Type fuer `.mp3` liefert.
- Die `.htaccess` im Hauptverzeichnis setzt dies bereits.
- Einige Shared-Hosting-Anbieter ueberschreiben `.htaccess` – in dem Fall beim Hoster nachfragen.

## Sicherheit

- `config.php` ist per `.htaccess` vor Browser-Zugriff geschuetzt.
- In `assets/uploads/` wird PHP-Ausfuehrung blockiert.
- Upload validiert MIME-Type serverseitig (nicht nur Extension).
- Dateinamen werden serverseitig auf feste Werte gesetzt (kein User-Input).
- Directory Listing ist deaktiviert.
