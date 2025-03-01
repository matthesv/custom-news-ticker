<?php
if (!defined('ABSPATH')) exit;

function register_news_ticker_post_type() {
    $args = [
        'labels' => [
            'name' => 'News-Ticker',
            'singular_name' => 'Ticker-Meldung',
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-megaphone',
        'taxonomies' => ['ticker_category'], // Hier die Taxonomie hinzufügen
    ];
    register_post_type('news_ticker', $args);
}

function register_news_ticker_taxonomy() {
    $args = [
        'labels' => [
            'name' => 'Ticker-Kategorien',
            'singular_name' => 'Ticker-Kategorie',
        ],
        'public' => true,
        'hierarchical' => true, // Ermöglicht übergeordnete Kategorien
        'show_admin_column' => true,
    ];
    register_taxonomy('ticker_category', 'news_ticker', $args);
}

add_action('init', 'register_news_ticker_post_type');
add_action('init', 'register_news_ticker_taxonomy');
