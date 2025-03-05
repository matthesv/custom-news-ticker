<?php
/*
Plugin Name: Custom News Ticker
Description: Ein anpassbarer News-Ticker mit Kategorien, Bildern und Live-Updates.
Version: 1.6.0
Author: Matthes
*/

if (!defined('ABSPATH')) {
    exit; // Sicherheitsprüfung
}

// Definiere den Plugin-Pfad
define('NEWS_TICKER_PATH', plugin_dir_path(__FILE__));
define('NEWS_TICKER_URL', plugin_dir_url(__FILE__));

// Dateien einbinden
require_once NEWS_TICKER_PATH . 'includes/post-type.php';
require_once NEWS_TICKER_PATH . 'includes/news-functions.php'; // Gemeinsame Funktionen
require_once NEWS_TICKER_PATH . 'includes/shortcode.php';
require_once NEWS_TICKER_PATH . 'includes/ajax-refresh.php';
require_once NEWS_TICKER_PATH . 'includes/time-translations.php'; // Neue Übersetzungsfunktionen
require_once NEWS_TICKER_PATH . 'includes/settings-page.php'; // Einstellungen-Seite
require_once NEWS_TICKER_PATH . 'includes/meta-box.php'; // Meta Box für individuelle Randfarbe
require_once NEWS_TICKER_PATH . 'includes/meta-box-update-date.php'; // Meta Box: Aktualisierungsdatum berücksichtigen
require_once NEWS_TICKER_PATH . 'includes/meta-box-background.php'; // NEU: Meta Box für Hintergrund einfärben
require_once NEWS_TICKER_PATH . 'includes/admin-scripts.php'; // Admin Scripts für Farbpicker
require_once NEWS_TICKER_PATH . 'includes/admin-filters.php';
require_once NEWS_TICKER_PATH . 'includes/meta-box-sources.php';
require_once NEWS_TICKER_PATH . 'includes/breaking-news.php'; // NEU: Breaking News Funktionen

// >>>>> NEUE INCLUDES für Kategorien-URLs und Headline-Shortcode <<<<<
require_once NEWS_TICKER_PATH . 'includes/category-meta.php';       // Neue Datei
require_once NEWS_TICKER_PATH . 'includes/shortcode-headlines.php'; // Neue Datei

// Assets registrieren mit Cache-Busting und Nonce
function news_ticker_enqueue_assets() {
    // Dashicons für Frontend laden (für den "Mehr Laden"-Button)
    wp_enqueue_style('dashicons');

    $style_path = NEWS_TICKER_PATH . 'assets/style.css';
    $script_path = NEWS_TICKER_PATH . 'assets/script.js';
    $style_version = file_exists($style_path) ? filemtime($style_path) : false;
    $script_version = file_exists($script_path) ? filemtime($script_path) : false;
    
    wp_enqueue_style('news-ticker-style', plugins_url('assets/style.css', __FILE__), array(), $style_version);
    wp_enqueue_script('news-ticker-script', plugins_url('assets/script.js', __FILE__), array('jq
