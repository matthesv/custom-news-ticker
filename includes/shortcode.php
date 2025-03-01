<?php
if (!defined('ABSPATH')) exit;

function render_news_ticker($atts) {
    $atts = shortcode_atts(['category' => ''], $atts);

    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'ticker_category',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ],
        ];
    }

    $query = new WP_Query($args);

    ob_start();
    echo '<div class="news-ticker-container" data-category="' . esc_attr($atts['category']) . '">';
    while ($query->have_posts()) {
        $query->the_post();
        $time_diff = human_time_diff(get_the_time('U'), current_time('timestamp'));
        $image = get_the_post_thumbnail(get_the_ID(), 'thumbnail') ?: '';

        echo '<div class="news-ticker-entry">';
        echo '<div class="news-ticker-dot"></div>';
        echo '<div class="news-ticker-content">';
        echo $image;
        echo '<h4>' . get_the_title() . '</h4>';
        echo '<p>' . get_the_content() . '</p>';
        echo '<span class="news-ticker-time">' . $time_diff . ' ago</span>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('news_ticker', 'render_news_ticker');
