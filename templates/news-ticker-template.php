<?php
/**
 * Template für den News Ticker.
 *
 * Dieses Template kann durch Kopieren in dein Theme und Anpassung überschrieben werden.
 */

if (!defined('ABSPATH')) exit;

$default_color = nt_get_border_color();

// Sortiere die Posts im Query manuell nach effektivem Zeitstempel (DESC)
if (!empty($query->posts)) {
    usort($query->posts, function($a, $b) {
        $a_use_updated = get_post_meta($a->ID, 'nt_use_updated_date', true) === 'yes';
        $a_date = $a_use_updated ? strtotime($a->post_modified) : strtotime($a->post_date);
        $b_use_updated = get_post_meta($b->ID, 'nt_use_updated_date', true) === 'yes';
        $b_date = $b_use_updated ? strtotime($b->post_modified) : strtotime($b->post_date);
        return $b_date - $a_date;
    });
}

// Berechne den effektiven Zeitstempel des letzten (ältesten) Beitrags
$last_timestamp = 0;
if (!empty($query->posts)) {
    $last_post = end($query->posts);
    $use_update_date = get_post_meta($last_post->ID, 'nt_use_updated_date', true) === 'yes';
    $last_timestamp = $use_update_date ? strtotime($last_post->post_modified) : strtotime($last_post->post_date);
}
?>
<div class="news-ticker-container"
     role="list"
     aria-label="News Ticker"
     data-category="<?php echo esc_attr($atts['category']); ?>"
     data-posts-per-page="<?php echo intval($query->query_vars['posts_per_page']); ?>"
     data-last-timestamp="<?php echo esc_attr($last_timestamp); ?>"
     style="border-left: 3px solid <?php echo esc_attr($default_color); ?>;">

    <?php while ($query->have_posts()) : $query->the_post(); ?>
        <?php 
        $use_update_date = get_post_meta(get_the_ID(), 'nt_use_updated_date', true) === 'yes';
        $date_timestamp  = $use_update_date ? get_the_modified_time('U') : get_the_time('U');
        $time_diff       = human_time_diff($date_timestamp, current_time('timestamp'));
        $language        = get_option('news_ticker_language', '');
        $translated_time = nt_translate_time($time_diff, $language);

        // ISO-Format für datetime-Attribut
        $iso_date  = get_the_date('c');
        $full_date = date_i18n('d.m.Y, H:i \U\h\r', $date_timestamp);

        // Beitragsbild (Thumbnail) mit verbessertem Alt-Attribut
        $image = get_the_post_thumbnail(get_the_ID(), 'thumbnail', array('alt' => get_the_title())); 
        
        // Rand-/Dot-Farbe ermitteln
        $use_global_color = get_post_meta(get_the_ID(), 'nt_use_global_color', true);
        if ($use_global_color === '') {
            $use_global_color = 'yes';
        }

        if ($use_global_color === 'yes') {
            $color = $default_color;
        } else {
            $custom_color = get_post_meta(get_the_ID(), 'nt_border_color', true);
            $color = $custom_color ? $custom_color : $default_color;
        }
        
        // Hintergrund einfärben?
        $background = get_post_meta(get_the_ID(), 'nt_background_color', true) === 'yes';

        // Neuer, dezenterer Hintergrundstil
        $bg_style = $background
            ? 'background-color: ' . nt_hex_to_rgba($color, 0.08) . '; 
               border: 1px solid '   . nt_hex_to_rgba($color, 0.2) . '; 
               border-radius: 4px; 
               padding: 15px;'
            : '';
        ?>
        
        <article class="news-ticker-entry"
                 role="listitem"
                 tabindex="0"
                 data-news-id="<?php the_ID(); ?>"
                 data-timestamp="<?php echo esc_attr($date_timestamp); ?>"
                 style="<?php echo esc_attr($bg_style); ?>"
                 itemscope itemtype="https://schema.org/NewsArticle">

            <div class="news-ticker-dot"
                 style="
                     --dot-color: <?php echo esc_attr($color); ?>;
                     --dot-color-pulse: <?php echo nt_hex_to_rgba($color, 0.4); ?>;
                     --dot-color-pulse-transparent: <?php echo nt_hex_to_rgba($color, 0); ?>;
                     background-color: <?php echo esc_attr($color); ?>;
                 ">
            </div>

            <div class="news-ticker-content">
                <?php echo $image; ?>

                <header>
                    <h2 itemprop="headline"><?php the_title(); ?></h2>
                </header>

                <div itemprop="articleBody">
                    <?php the_content(); ?>
                </div>

                <time class="news-ticker-time"
                      datetime="<?php echo esc_attr($iso_date); ?>"
                      itemprop="datePublished"
                      data-full-date="<?php echo esc_attr($full_date); ?>">
                    <?php echo esc_html($translated_time); ?>
                </time>

                <!-- Permalink für Suchmaschinen und Nutzer ohne JavaScript -->
                <a class="news-ticker-permalink" href="<?php the_permalink(); ?>" aria-label="Mehr lesen zu <?php the_title_attribute(); ?>">Mehr lesen</a>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php if ($query->max_num_pages > 1) : ?>
    <button id="news-ticker-load-more" type="button" class="news-ticker-load-more" aria-label="Mehr Nachrichten laden">
        <span class="dashicons dashicons-arrow-down-alt2"></span> Mehr Laden
    </button>
<?php endif; ?>

<!-- Fallback für Nutzer ohne JavaScript: Link zur vollständigen, paginierten Übersicht -->
<noscript>
    <div class="news-ticker-noscript">
        <p>JavaScript ist deaktiviert. Bitte besuchen Sie die <a href="<?php echo esc_url(get_post_type_archive_link('news_ticker')); ?>">vollständige Übersicht</a> der News.</p>
    </div>
</noscript>

<?php wp_reset_postdata(); ?>
