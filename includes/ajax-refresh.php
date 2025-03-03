<?php
if (!defined('ABSPATH')) exit;

require_once NEWS_TICKER_PATH . 'includes/news-functions.php';

/**
 * Holt die neuesten News-Posts und gibt sie als JSON zurück.
 * Unterstützt zwei Modi:
 * - 'refresh': Lädt die neuesten Beiträge (offset = 0) und fügt neue Beiträge hinzu.
 * - 'load_more': Lädt ältere Beiträge, wobei bereits geladene Beiträge ausgeschlossen werden.
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

    if ($mode === 'load_more') {
        // Im "load_more"-Modus werden bereits geladene Beiträge anhand ihrer IDs ausgeschlossen.
        // Daher entfernen wir den Offset, um zu verhindern, dass Beiträge übersprungen werden.
        if (isset($_POST['exclude_ids']) && is_array($_POST['exclude_ids'])) {
            $exclude_ids = array_map('intval', $_POST['exclude_ids']);
            $args['post__not_in'] = $exclude_ids;
        } else {
            $exclude_ids = [];
        }
        $previous_count = count($exclude_ids);
    } else {
        $args['offset'] = $offset;
        $previous_count = $offset;
    }

    $query = nt_get_news_query($args);
    $news_items = nt_get_news_items($query);

    if ($mode === 'load_more') {
        $new_offset = $previous_count + count($news_items);
        $total_posts = $query->found_posts; // Anzahl der Beiträge, die der Query (ohne die ausgeschlossenen) entsprechen
    } else {
        $new_offset = $offset + count($news_items);
        $total_posts = $query->found_posts;
    }
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
