<?php
if (!defined('ABSPATH')) exit;

require_once NEWS_TICKER_PATH . 'includes/news-functions.php';

/**
 * Ermittelt den effektiven Zeitstempel eines Posts.
 */
function nt_get_effective_timestamp($post) {
    $use_updated = get_post_meta($post->ID, 'nt_use_updated_date', true) === 'yes';
    $timestamp = $use_updated ? strtotime($post->post_modified) : strtotime($post->post_date);
    return $timestamp ? $timestamp : 0;
}

/**
 * Holt die neuesten News-Posts und gibt sie als JSON zurück.
 * Unterstützt zwei Modi:
 * - 'refresh': Lädt die neuesten Beiträge (offset = 0) und fügt neue Beiträge vorne ein.
 * - 'load_more': Lädt ältere Beiträge basierend auf dem letzten geladenen effektiven Zeitstempel.
 */
function fetch_latest_news() {
    // Sicherheitsüberprüfung: Prüfe Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'news_ticker_nonce')) {
        wp_send_json_error('Unauthorized');
    }

    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : intval(get_option('news_ticker_entries_count', 5));
    $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'refresh';

    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => ($mode === 'load_more') ? $posts_per_page * 2 : $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
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

    if ($mode === 'refresh') {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $args['offset'] = $offset;
    }

    $query = nt_get_news_query($args);

    // Für load_more: zuerst nach effektivem Zeitstempel (DESC) sortieren …
    if ($mode === 'load_more' && isset($_POST['last_timestamp'])) {
        $last_timestamp = intval($_POST['last_timestamp']);
        if (!empty($query->posts)) {
            usort($query->posts, function($a, $b) {
                $a_time = nt_get_effective_timestamp($a);
                $b_time = nt_get_effective_timestamp($b);
                return $b_time - $a_time;
            });
        }
        // … und dann nur Beiträge auswählen, die älter sind als der übergebene Zeitstempel
        $filtered_posts = array_filter($query->posts, function($post) use ($last_timestamp) {
            return nt_get_effective_timestamp($post) < $last_timestamp;
        });
        $filtered_posts = array_values($filtered_posts);
        $filtered_posts = array_slice($filtered_posts, 0, $posts_per_page);
        $query->posts = $filtered_posts;
    }

    $news_items = nt_get_news_items($query);

    // Ermitteln des neuen letzten Zeitstempels (Cursor) anhand der sortierten Ergebnisse
    $new_last_timestamp = 0;
    if ($mode === 'load_more' && !empty($news_items)) {
        $last_item = end($news_items);
        if ($last_item && isset($last_item['timestamp'])) {
            $new_last_timestamp = $last_item['timestamp'];
        }
    }

    // Bestimmen, ob noch weitere Beiträge vorhanden sind
    $has_more = false;
    if ($mode === 'load_more') {
        $has_more = (count($query->posts) >= $posts_per_page);
    } else {
        $total_posts = $query->found_posts;
        $current_count = isset($_POST['offset']) ? intval($_POST['offset']) + count($news_items) : count($news_items);
        $has_more = $current_count < $total_posts;
    }

    wp_send_json([
        'news_items' => $news_items,
        'new_last_timestamp' => $new_last_timestamp,
        'has_more'   => $has_more
    ]);
}

add_action('wp_ajax_fetch_news', 'fetch_latest_news');
add_action('wp_ajax_nopriv_fetch_news', 'fetch_latest_news');
?>
