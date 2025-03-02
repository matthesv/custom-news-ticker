<?php
if (!defined('ABSPATH')) exit;

require_once NEWS_TICKER_PATH . 'includes/news-functions.php';

/**
 * Holt die neuesten News-Posts und gibt sie als JSON zurück.
 * Unterstützt zwei Modi:
 * - 'refresh': Lädt die neuesten Beiträge (offset = 0) und ersetzt den Inhalt.
 * - 'load_more': Lädt ältere Beiträge ab dem aktuellen Offset und hängt sie an.
 */
function fetch_latest_news() {
    // Sicherheitsüberprüfung: Prüfe Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'news_ticker_nonce')) {
        wp_send_json_error('Unauthorized');
    }

    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : intval(get_option('news_ticker_entries_count', 5));
    $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'refresh'; // 'refresh' oder 'load_more'

    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'offset'         => $offset,
    ];

    if (!empty($category)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'ticker_category',
                'field'    => 'slug',
                'terms'    => $category,
            ],
        ];
    }

    $query = nt_get_news_query($args);
    $news_items = nt_get_news_items($query);

    // Neues Offset berechnen
    $new_offset = $offset + count($news_items);
    $total_posts = $query->found_posts;
    $has_more = $new_offset < $total_posts;

    wp_send_json([
        'news_items' => $news_items,
        'new_offset' => $new_offset,
        'has_more'   => $has_more
    ]);
}

add_action('wp_ajax_fetch_news', 'fetch_latest_news');
add_action('wp_ajax_nopriv_fetch_news', 'fetch_latest_news');
?>
