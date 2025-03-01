<?php
/**
 * Template für den News Ticker.
 *
 * Dieses Template kann durch Kopieren in dein Theme und Anpassung überschrieben werden.
 */

if (!defined('ABSPATH')) exit;
?>
<?php if ($query->have_posts()) : ?>
<div class="news-ticker-container" data-category="<?php echo esc_attr($atts['category']); ?>">
    <?php while ($query->have_posts()) : $query->the_post(); ?>
        <?php 
        $time_diff = human_time_diff(get_the_time('U'), current_time('timestamp')); 
        $language = get_option('news_ticker_language', '');
        $translated_time = nt_translate_time($time_diff, $language);
        $image = get_the_post_thumbnail(get_the_ID(), 'thumbnail'); 
        ?>
        <div class="news-ticker-entry">
            <div class="news-ticker-dot"></div>
            <div class="news-ticker-content">
                <?php echo $image; ?>
                <h4><?php the_title(); ?></h4>
                <p><?php the_content(); ?></p>
                <span class="news-ticker-time"><?php echo esc_html($translated_time); ?></span>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php else : ?>
<div class="news-ticker-container">
    <p><?php _e('Keine News verfügbar.', 'news-ticker'); ?></p>
</div>
<?php endif; ?>