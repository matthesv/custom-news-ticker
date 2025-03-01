<?php
if (!defined('ABSPATH')) exit;

/**
 * Holt die WP_Query für News-Ticker Beiträge basierend auf übergebenen Argumenten.
 *
 * @param array $args Argumente für die Query.
 * @return WP_Query
 */
function nt_get_news_query($args = array()) {
    $default_args = [
        'post_type'      => 'news_ticker',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    $args = wp_parse_args($args, $default_args);
    return new WP_Query($args);
}

/**
 * Wandelt die WP_Query Ergebnisse in ein Array um, das per AJAX ausgegeben wird.
 *
 * @param WP_Query $query
 * @return array
 */
function nt_get_news_items($query) {
    $news_items = [];
    
    // Hole die gewählte Sprache aus den Einstellungen
    $language = get_option('news_ticker_language', '');
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
            $time_diff = human_time_diff(get_the_time('U'), current_time('timestamp'));
            
            // Übersetze die Zeitangabe mit unserer neuen Funktion
            $translated_time = nt_translate_time($time_diff, $language);
            
            $news_items[] = [
                'title'   => get_the_title(),
                'content' => apply_filters('the_content', get_the_content()),
                'time'    => $translated_time,
                'image'   => $image_url ? $image_url : '',
            ];
        }
        wp_reset_postdata();
    }
    return $news_items;
}

/**
 * Formatiert die Zeit für die Anzeige im Ticker mit Übersetzung
 *
 * @param int $timestamp Unix-Zeitstempel
 * @return string Formatierte und übersetzte Zeit
 */
function nt_format_time($timestamp) {
    $time_diff = human_time_diff($timestamp, current_time('timestamp'));
    $language = get_option('news_ticker_language', '');
    return nt_translate_time($time_diff, $language);
}