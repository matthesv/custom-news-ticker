<?php
if (!defined('ABSPATH')) exit;

/**
 * Konvertiert eine Hex-Farbe in einen RGBA-String.
 *
 * @param string $hex Die Hex-Farbe (z. B. #FF4500).
 * @param float $alpha Der Alpha-Wert (Standard 1).
 * @return string Der RGBA-Farbstring.
 */
function nt_hex_to_rgba($hex, $alpha = 1) {
    $hex = str_replace("#", "", $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex,0,1),2));
        $g = hexdec(str_repeat(substr($hex,1,1),2));
        $b = hexdec(str_repeat(substr($hex,2,1),2));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return "rgba($r, $g, $b, $alpha)";
}

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
    
    // Sortiere die Posts manuell basierend auf dem effektiven Datum
    if (!empty($query->posts)) {
        usort($query->posts, function($a, $b) {
            $a_use_updated = get_post_meta($a->ID, 'nt_use_updated_date', true) === 'yes';
            $a_date = $a_use_updated ? strtotime($a->post_modified) : strtotime($a->post_date);
            $b_use_updated = get_post_meta($b->ID, 'nt_use_updated_date', true) === 'yes';
            $b_date = $b_use_updated ? strtotime($b->post_modified) : strtotime($b->post_date);
            return $b_date - $a_date;
        });
    }
    
    // Hole die eingestellte Sprache
    $language = get_option('news_ticker_language', '');
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
            
            $use_update_date = get_post_meta(get_the_ID(), 'nt_use_updated_date', true) === 'yes';
            $post_obj = get_post(get_the_ID());
            $date_timestamp = $use_update_date ? strtotime($post_obj->post_modified) : strtotime($post_obj->post_date);
            $time_diff = human_time_diff($date_timestamp, current_time('timestamp'));
            
            $translated_time = nt_translate_time($time_diff, $language);
            $full_date = date_i18n('d.m.Y, H:i \U\h\r', $date_timestamp);
            
            $use_global_color = get_post_meta(get_the_ID(), 'nt_use_global_color', true);
            if ($use_global_color === '') {
                $use_global_color = 'yes';
            }
            
            if ($use_global_color === 'yes') {
                $border_color = nt_get_border_color();
            } else {
                $custom_border_color = get_post_meta(get_the_ID(), 'nt_border_color', true);
                $border_color = $custom_border_color ? $custom_border_color : nt_get_border_color();
            }
            
            $news_items[] = [
                'ID'           => get_the_ID(),
                'title'        => get_the_title(),
                'content'      => apply_filters('the_content', get_the_content()),
                'time'         => $translated_time,
                'full_date'    => $full_date,
                'image'        => $image_url ? $image_url : '',
                'border_color' => $border_color,
                'timestamp'    => $date_timestamp, // Effektiver Zeitstempel
            ];
        }
        wp_reset_postdata();
    }
    return $news_items;
}

/**
 * Formatiert die Zeit für die Anzeige im Ticker mit Übersetzung.
 *
 * @param int $timestamp Unix-Zeitstempel
 * @return string Formatierte und übersetzte Zeit
 */
function nt_format_time($timestamp) {
    $time_diff = human_time_diff($timestamp, current_time('timestamp'));
    $language = get_option('news_ticker_language', '');
    return nt_translate_time($time_diff, $language);
}

/**
 * Bestimmt die Standard-Randfarbe des News Tickers.
 *
 * @return string Hex-Farbcode
 */
function nt_get_border_color() {
    $color_source = get_option('news_ticker_color_source', 'custom');
    if ($color_source === 'primary') {
        return get_theme_mod('primary_color', '#0073aa');
    } elseif ($color_source === 'secondary') {
        return get_theme_mod('secondary_color', '#00a0d2');
    }
    return get_option('news_ticker_border_color', '#FF4500');
}
?>
