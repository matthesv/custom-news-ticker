<?php
if (!defined('ABSPATH')) exit;

require_once NEWS_TICKER_PATH . 'includes/news-functions.php';

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

    $query = nt_get_news_query($args);

    // Sortiere die Posts manuell basierend auf dem gewählten Datum (Aktualisierungsdatum falls aktiviert, sonst Veröffentlichungsdatum)
    if (!empty($query->posts)) {
        usort($query->posts, function($a, $b) {
            $a_use_updated = get_post_meta($a->ID, 'nt_use_updated_date', true) === 'yes';
            $a_date = $a_use_updated ? strtotime($a->post_modified) : strtotime($a->post_date);
            $b_use_updated = get_post_meta($b->ID, 'nt_use_updated_date', true) === 'yes';
            $b_date = $b_use_updated ? strtotime($b->post_modified) : strtotime($b->post_date);
            return $b_date - $a_date;
        });
    }

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
?>
