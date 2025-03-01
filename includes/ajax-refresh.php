<?php
if (!defined('ABSPATH')) exit;

function fetch_latest_news() {
    $args = [
        'post_type' => 'news_ticker',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    $query = new WP_Query($args);
    $news_items = [];

    while ($query->have_posts()) {
        $query->the_post();
        $news_items[] = [
            'title' => get_the_title(),
            'content' => get_the_content(),
            'time' => human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago',
            'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
        ];
    }
    wp_reset_postdata();

    wp_send_json($news_items);
}
add_action('wp_ajax_fetch_news', 'fetch_latest_news');
add_action('wp_ajax_nopriv_fetch_news', 'fetch_latest_news');
