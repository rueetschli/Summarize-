<?php
/**
 * Konfiguration – hier alle Einstellungen anpassen.
 * Keine Programmierkenntnisse nötig: einfach die Werte zwischen den Anführungszeichen ändern.
 */

return [
    // ─── Seitentitel & Beschreibung ───
    'title'             => 'Vorlesungs-Zusammenfassungen',
    'subtitle'          => 'Audio-Zusammenfassungen und Handouts zum Download',

    // ─── Vorlesungen (Lectures) ───
    // Jede Vorlesung hat: title, mp3_path, pdf_path (optional), date (optional)
    'lectures' => [
        [
            'title'    => 'Vorlesung 1 – Einführung',
            'mp3_path' => 'assets/uploads/vorlesung-01.mp3',
            'pdf_path' => 'assets/uploads/vorlesung-01.pdf',
            'date'     => '2026-01-15',
        ],
        [
            'title'    => 'Vorlesung 2 – Grundlagen',
            'mp3_path' => 'assets/uploads/vorlesung-02.mp3',
            'pdf_path' => 'assets/uploads/vorlesung-02.pdf',
            'date'     => '2026-01-22',
        ],
        [
            'title'    => 'Vorlesung 3 – Vertiefung',
            'mp3_path' => 'assets/uploads/vorlesung-03.mp3',
            'pdf_path' => 'assets/uploads/vorlesung-03.pdf',
            'date'     => '2026-01-29',
        ],
    ],

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
