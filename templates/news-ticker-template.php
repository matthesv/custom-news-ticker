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
<?php if ($query->have_posts()) : ?>
<div class="news-ticker-container" data-category="<?php echo esc_attr($atts['category']); ?>" data-posts-per-page="<?php echo intval($query->query_vars['posts_per_page']); ?>" data-last-timestamp="<?php echo esc_attr($last_timestamp); ?>" style="border-left: 3px solid <?php echo esc_attr($default_color); ?>;">
    <?php while ($query->have_posts()) : $query->the_post(); ?>
        <?php 
        $use_update_date = get_post_meta(get_the_ID(), 'nt_use_updated_date', true) === 'yes';
        $date_timestamp = $use_update_date ? get_the_modified_time('U') : get_the_time('U');
        $time_diff = human_time_diff($date_timestamp, current_time('timestamp'));
        $language = get_option('news_ticker_language', '');
        $translated_time = nt_translate_time($time_diff, $language);
        $full_date = date_i18n('d.m.Y, H:i \U\h\r', $date_timestamp);
        $image = get_the_post_thumbnail(get_the_ID(), 'thumbnail'); 
        
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
        
        $background = get_post_meta(get_the_ID(), 'nt_background_color', true) === 'yes';
        $bg_style = $background ? 'background-color: ' . nt_hex_to_rgba($color, 0.7) . ';' : '';
        ?>
        <div class="news-ticker-entry" data-news-id="<?php the_ID(); ?>" data-timestamp="<?php echo esc_attr($date_timestamp); ?>" style="<?php echo esc_attr($bg_style); ?>">
            <div class="news-ticker-dot" style="--dot-color: <?php echo esc_attr($color); ?>; --dot-color-pulse: <?php echo nt_hex_to_rgba($color, 0.4); ?>; --dot-color-pulse-transparent: <?php echo nt_hex_to_rgba($color, 0); ?>; background-color: <?php echo esc_attr($color); ?>;"></div>
            <div class="news-ticker-content">
                <?php echo $image; ?>
                <h4><?php the_title(); ?></h4>
                <p><?php the_content(); ?></p>
                <span class="news-ticker-time" data-full-date="<?php echo esc_attr($full_date); ?>"><?php echo esc_html($translated_time); ?></span>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php if ($query->max_num_pages > 1) : ?>
    <button id="news-ticker-load-more" class="news-ticker-load-more">
        <span class="dashicons dashicons-arrow-down-alt2"></span> Mehr Laden
    </button>
<?php endif; ?>
<?php else : ?>
<div class="news-ticker-container">
    <p><?php _e('Keine News verfügbar.', 'news-ticker'); ?></p>
</div>
<?php endif; ?>
