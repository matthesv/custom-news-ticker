<?php
if (!defined('ABSPATH')) exit;

function fetch_latest_news() {
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => 5,
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

    $query = new WP_Query($args);
    $news_items = [];

    while ($query->have_posts()) {
        $query->the_post();
        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');

        $news_items[] = [
            'title'   => get_the_title(),
            'content' => get_the_content(),
            'time'    => human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago',
            'image'   => $image_url ? $image_url : '', // 🔥 Falls kein Bild existiert, leere Zeichenkette statt NULL zurückgeben
        ];
    }
    wp_reset_postdata();

    wp_send_json($news_items);
}

add_action('wp_ajax_fetch_news', 'fetch_latest_news');
add_action('wp_ajax_nopriv_fetch_news', 'fetch_latest_news');
