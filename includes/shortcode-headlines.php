<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode: [news_ticker_headlines categories="cat1,cat2,..."]
 * Zeigt nur die Titel aller News aus den angegebenen Kategorien als Laufband-Ticker.
 * Beim Klick auf eine Headline wird die in der Kategorie hinterlegte URL aufgerufen.
 */
function render_news_ticker_headlines($atts) {
    $atts = shortcode_atts([
        'categories' => '', // Kommagetrennte Liste von Category-Slugs
    ], $atts, 'news_ticker_headlines');

    // Kategorien aufsplitten
    $cat_slugs = array_filter(array_map('trim', explode(',', $atts['categories'])));

    // Falls keine Kategorien angegeben sind, geben wir eine kurze Info aus
    if (empty($cat_slugs)) {
        return '<p>' . __('Keine Kategorien angegeben.', 'news-ticker') . '</p>';
    }

    // WP_Query für alle Beiträge aus diesen Kategorien
    $args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => [
            [
                'taxonomy' => 'ticker_category',
                'field'    => 'slug',
                'terms'    => $cat_slugs,
            ]
        ]
    ];
    $query = new WP_Query($args);

    // Keine Beiträge gefunden?
    if (!$query->have_posts()) {
        return '<p>' . __('Keine News gefunden.', 'news-ticker') . '</p>';
    }

    // HTML-Ausgabe vorbereiten
    ob_start();
    ?>
    <div class="news-ticker-headlines-container">
        <div class="news-ticker-headlines-wrapper">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                
                // Kategorie-URL ermitteln
                $headline_url = '#';
                $post_cats = wp_get_post_terms(get_the_ID(), 'ticker_category');
                
                // Wir nehmen die erste Kategorie, die in $cat_slugs enthalten ist
                foreach ($post_cats as $pc) {
                    if (in_array($pc->slug, $cat_slugs)) {
                        $maybe_url = get_term_meta($pc->term_id, 'nt_category_url', true);
                        if ($maybe_url) {
                            $headline_url = $maybe_url;
                            break;
                        }
                    }
                }
                ?>
                <div class="news-ticker-headline">
                    <a href="<?php echo esc_url($headline_url); ?>" target="_self">
                        <?php the_title(); ?>
                    </a>
                </div>
                <?php
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('news_ticker_headlines', 'render_news_ticker_headlines');
