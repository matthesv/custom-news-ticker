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
    ];
    register_post_type('news_ticker', $args);
}
add_action('init', 'register_news_ticker_post_type');
