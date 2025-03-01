<?php
if (!defined('ABSPATH')) exit;

function register_news_ticker_post_type() {
    $labels = array(
        'name'               => 'News-Ticker',
        'singular_name'      => 'Ticker-Meldung',
        'add_new'            => 'Neue Meldung hinzufügen',
        'add_new_item'       => 'Neue Ticker-Meldung hinzufügen',
        'edit_item'          => 'Ticker-Meldung bearbeiten',
        'new_item'           => 'Neue Ticker-Meldung',
        'all_items'          => 'Alle Ticker-Meldungen',
        'view_item'          => 'Ticker-Meldung ansehen',
        'search_items'       => 'Ticker-Meldungen durchsuchen',
        'not_found'          => 'Keine Ticker-Meldungen gefunden',
        'not_found_in_trash' => 'Keine Ticker-Meldungen im Papierkorb gefunden',
        'menu_name'          => 'News-Ticker'
    );
    $args = [
        'labels' => $labels,
        'public' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-megaphone',
        'has_archive' => true,
        'rewrite' => ['slug' => 'news-ticker'],
        'taxonomies' => ['ticker_category'],
    ];
    register_post_type('news_ticker', $args);
}

function register_news_ticker_taxonomy() {
    $labels = array(
        'name'              => 'Ticker-Kategorien',
        'singular_name'     => 'Ticker-Kategorie',
        'search_items'      => 'Ticker-Kategorien durchsuchen',
        'all_items'         => 'Alle Ticker-Kategorien',
        'parent_item'       => 'Übergeordnete Kategorie',
        'parent_item_colon' => 'Übergeordnete Kategorie:',
        'edit_item'         => 'Ticker-Kategorie bearbeiten',
        'update_item'       => 'Ticker-Kategorie aktualisieren',
        'add_new_item'      => 'Neue Ticker-Kategorie hinzufügen',
        'new_item_name'     => 'Name der neuen Ticker-Kategorie',
        'menu_name'         => 'Ticker-Kategorien',
    );
    $args = [
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'ticker-category'),
    ];
    register_taxonomy('ticker_category', 'news_ticker', $args);
}

add_action('init', 'register_news_ticker_post_type');
add_action('init', 'register_news_ticker_taxonomy');
