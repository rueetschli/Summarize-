<?php
/**
 * Konfiguration – hier alle Einstellungen anpassen.
 * Keine Programmierkenntnisse nötig: einfach die Werte zwischen den Anführungszeichen ändern.
 */

return [
    // ─── Seitentitel & Klassenbezeichnung ───
    'title'             => 'Digitales Marketing',
    'class_name'        => 'Marketing- und Verkaufsleiter',
    'subtitle'          => 'Audio-Zusammenfassungen und Handouts zum Download',

    // ─── Module ───
    // Jedes Modul hat: title, audio_path (mp3 oder m4a), pdf_path (optional), date (optional)
    'lectures' => [
        [
            'title'      => 'Digitales Marketing Grundlagen',
            'audio_path' => 'assets/uploads/modul-01.m4a',
            'pdf_path'   => 'assets/uploads/modul-01.pdf',
            'date'       => '2026-01-15',
        ],
        [
            'title'      => 'Social Media Strategien',
            'audio_path' => 'assets/uploads/modul-02.mp3',
            'pdf_path'   => 'assets/uploads/modul-02.pdf',
            'date'       => '2026-01-22',
        ],
        [
            'title'      => 'SEO & Content Marketing',
            'audio_path' => 'assets/uploads/modul-03.m4a',
            'pdf_path'   => 'assets/uploads/modul-03.pdf',
            'date'       => '2026-01-29',
        ],
    ],

    // ─── Player-Optionen ───
    'enable_audio_download' => true,

    // ─── Upload-Formular ───
    'enable_upload'     => false,
    'upload_password'   => 'mein-sicheres-passwort',
    'max_upload_mb'     => 50,

    // ─── Darstellung ───
    'accent_color'      => '#6366f1',   // Modern Indigo
    'footer_text'       => '© ' . date('Y') . ' WSF',
];
