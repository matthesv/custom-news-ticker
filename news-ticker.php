<?php
/*
Plugin Name: Custom News Ticker
Description: Ein anpassbarer News-Ticker mit Kategorien, Bildern und Live-Updates.
Version: 1.2.1
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

// Assets registrieren mit Cache-Busting und Nonce
function news_ticker_enqueue_assets() {
    $style_path = NEWS_TICKER_PATH . 'assets/style.css';
    $script_path = NEWS_TICKER_PATH . 'assets/script.js';
    $style_version = file_exists($style_path) ? filemtime($style_path) : false;
    $script_version = file_exists($script_path) ? filemtime($script_path) : false;
    
    wp_enqueue_style('news-ticker-style', plugins_url('assets/style.css', __FILE__), array(), $style_version);
    wp_enqueue_script('news-ticker-script', plugins_url('assets/script.js', __FILE__), array('jquery'), $script_version, true);
    wp_localize_script('news-ticker-script', 'newsTickerAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('news_ticker_nonce'),
        'language' => substr(get_locale(), 0, 2) // Füge aktuelle Sprache zum JavaScript hinzu
    ]);
}
add_action('wp_enqueue_scripts', 'news_ticker_enqueue_assets');

// Seite rendern
function news_ticker_settings_page() {
    ?>
    <div class="wrap">
        <h1>News Ticker Einstellungen</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('news_ticker_options');
            do_settings_sections('news-ticker-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Einstellungen registrieren
function news_ticker_register_settings() {
    register_setting('news_ticker_options', 'news_ticker_language', ['default' => '']);
    
    add_settings_section(
        'news_ticker_translation_section',
        'Übersetzungseinstellungen',
        'news_ticker_translation_section_callback',
        'news-ticker-settings'
    );
    
    add_settings_field(
        'news_ticker_language',
        'Sprache für Zeitangaben',
        'news_ticker_language_callback',
        'news-ticker-settings',
        'news_ticker_translation_section'
    );
}
add_action('admin_init', 'news_ticker_register_settings');

function news_ticker_translation_section_callback() {
    echo '<p>Hier können Sie die Sprache für die Zeitangaben im News Ticker einstellen.</p>';
}

function news_ticker_language_callback() {
    $languages = [
        '' => 'WordPress-Sprache verwenden',
        'en' => 'Englisch',
        'de' => 'Deutsch',
        'fr' => 'Französisch',
        'es' => 'Spanisch'
    ];
    
    $selected = get_option('news_ticker_language', '');
    
    echo '<select name="news_ticker_language">';
    foreach ($languages as $code => $name) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr($code),
            selected($selected, $code, false),
            esc_html($name)
        );
    }
    echo '</select>';
}

// Plugin Update Checker laden (GitHub) - optional
require_once NEWS_TICKER_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/matthesv/custom-news-ticker/',
    __FILE__,
    'custom-news-ticker'
);
$myUpdateChecker->setBranch('main');
