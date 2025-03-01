<?php
if (!defined('ABSPATH')) exit;

/**
 * Rendert den News Ticker Shortcode.
 *
 * @param array $atts Shortcode Attribute.
 * @return string HTML Ausgabe.
 */
function render_news_ticker($atts) {
    $atts = shortcode_atts(['category' => '', 'posts_per_page' => 10], $atts, 'news_ticker');

    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => intval($atts['posts_per_page']),
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

    // Ermögliche Template-Override: Suche nach einem Template in deinem Theme
    $template_path = locate_template('news-ticker-template.php');
    if (!$template_path) {
        $template_path = NEWS_TICKER_PATH . 'templates/news-ticker-template.php';
    }

    ob_start();
    include $template_path;
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('news_ticker', 'render_news_ticker');
