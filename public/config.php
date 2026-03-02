<?php
/**
 * Konfiguration – hier alle Einstellungen anpassen.
 * Keine Programmierkenntnisse nötig: einfach die Werte zwischen den Anführungszeichen ändern.
 */

return [
    // ─── Seitentitel & Beschreibung ───
    'title'             => 'Zusammenfassung: Vortrag X',
    'subtitle'          => 'Audio-Zusammenfassung und Handout zum Download',

    // ─── Pfade zu den Dateien (relativ zu diesem Ordner) ───
    'mp3_path'          => 'assets/uploads/latest.mp3',
    'pdf_path'          => 'assets/uploads/handout.pdf',

    // ─── Player-Optionen ───
    'enable_mp3_download' => true,

    // ─── Upload-Formular ───
    'enable_upload'     => false,
    'upload_password'   => 'mein-sicheres-passwort',
    'max_upload_mb'     => 50,

    // ─── Darstellung ───
    'accent_color'      => '#005a8c',   // WSF-Blau
    'footer_text'       => '© ' . date('Y') . ' WSF',
];
