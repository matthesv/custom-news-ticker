<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode: [news_ticker_headlines categories="cat1,cat2" interval="5000"]
 * Zeigt alle Headlines der angegebenen Kategorien an.
 * Jede Meldung wird für X Sekunden angezeigt, dann sanft ausgeblendet.
 */
function render_news_ticker_headlines($atts) {
    $atts = shortcode_atts([
        'categories' => '',  // Kommagetrennte Category-Slugs
        'interval'   => 5000 // Zeit (ms) zwischen Meldungen
    ], $atts, 'news_ticker_headlines');

    // Kategorien in Array umwandeln
    $cat_slugs = array_filter(array_map('trim', explode(',', $atts['categories'])));

    // Falls keine Kategorien angegeben, kurze Meldung ausgeben
    if (empty($cat_slugs)) {
        return '<p>' . __('Keine Kategorien angegeben.', 'news-ticker') . '</p>';
    }

    // WP_Query: Alle Beiträge aus diesen Kategorien
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

    ob_start();
    ?>
    <!-- Container: Äußere Box für den Ticker -->
    <div class="news-ticker-headlines-container" 
         data-interval="<?php echo esc_attr($atts['interval']); ?>">

        <!-- Wrapper: Enthält die einzelnen Headlines -->
        <div class="news-ticker-headlines-wrapper">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <div class="news-ticker-headline">
                    <!-- Bei Klick könntest du statt Permalink auch eine Kategorie-URL verwenden -->
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </div>
                <?php
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>

    <!-- Einfaches JS (ohne jQuery) zum zyklischen Einblenden -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.news-ticker-headlines-container');
        if (!container) return;

        const interval = parseInt(container.getAttribute('data-interval'), 10) || 5000;
        const items = container.querySelectorAll('.news-ticker-headline');
        if (!items.length) return;

        let current = 0;
        // Erste Meldung aktiv setzen
        items[current].classList.add('active');

        // Wechsle alle X ms zur nächsten Meldung
        setInterval(() => {
            // Aktuelle Meldung deaktivieren
            items[current].classList.remove('active');
            // Index hochzählen (Modulo Anzahl)
            current = (current + 1) % items.length;
            // Neue Meldung aktivieren
            items[current].classList.add('active');
        }, interval);
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('news_ticker_headlines', 'render_news_ticker_headlines');
