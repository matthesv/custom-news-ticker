<?php
/*
Plugin Name: Custom News Ticker
Description: Ein anpassbarer News-Ticker mit Kategorien, Bildern und Live-Updates.
Version: 1.0
Author: Matthes
*/

if (!defined('ABSPATH')) {
    exit; // Sicherheitsprüfung
}

// Definiere den Plugin-Pfad
define('NEWS_TICKER_PATH', plugin_dir_path(__FILE__));

// Dateien einbinden
require_once NEWS_TICKER_PATH . 'includes/post-type.php';
require_once NEWS_TICKER_PATH . 'includes/shortcode.php';
require_once NEWS_TICKER_PATH . 'includes/ajax-refresh.php';

// Assets registrieren
function news_ticker_enqueue_assets() {
    wp_enqueue_style('news-ticker-style', plugins_url('assets/style.css', __FILE__));
    wp_enqueue_script('news-ticker-script', plugins_url('assets/script.js', __FILE__), ['jquery'], null, true);
    wp_localize_script('news-ticker-script', 'newsTickerAjax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'news_ticker_enqueue_assets');

// Plugin Update Checker laden (GitHub) - optional
require_once FEEDBACK_VOTING_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/matthesv/custom-news-ticker/',
    __FILE__,
    'custom-news-ticker'
);
$myUpdateChecker->setBranch('main');
