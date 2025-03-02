<?php
/*
Plugin Name: Custom News Ticker
Description: Ein anpassbarer News-Ticker mit Kategorien, Bildern und Live-Updates.
Version: 1.2.7
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
require_once NEWS_TICKER_PATH . 'includes/admin-scripts.php'; // Admin Scripts für Farbpicker

// Assets registrieren mit Cache-Busting und Nonce
function news_ticker_enqueue_assets() {
    $style_path = NEWS_TICKER_PATH . 'assets/style.css';
    $script_path = NEWS_TICKER_PATH . 'assets/script.js';
    $style_version = file_exists($style_path) ? filemtime($style_path) : false;
    $script_version = file_exists($script_path) ? filemtime($script_path) : false;
    
    wp_enqueue_style('news-ticker-style', plugins_url('assets/style.css', __FILE__), array(), $style_version);
    wp_enqueue_script('news-ticker-script', plugins_url('assets/script.js', __FILE__), array('jquery'), $script_version, true);
    wp_localize_script('news-ticker-script', 'newsTickerAjax', [
        'ajax_url'    => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('news_ticker_nonce'),
        'language'    => substr(get_locale(), 0, 2),
        'border_color'=> nt_get_border_color() // Nutzt die Funktion, die ggf. Theme-Farben berücksichtigt
    ]);
}
add_action('wp_enqueue_scripts', 'news_ticker_enqueue_assets');

// Plugin Update Checker laden (GitHub) - optional
require_once NEWS_TICKER_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/matthesv/custom-news-ticker/',
    __FILE__,
    'custom-news-ticker'
);
$myUpdateChecker->setBranch('main');
?>
