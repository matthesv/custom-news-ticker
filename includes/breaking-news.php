<?php
if (!defined('ABSPATH')) exit;

/**
 * Prüft, ob ein News-Beitrag als "Breaking News" gilt.
 *
 * @param int $timestamp Unix-Zeitstempel des Beitrags.
 * @param int $threshold Schwellenwert in Sekunden (Standard 600 = 10 Minuten).
 * @return bool
 */
function nt_is_breaking_news($timestamp, $threshold = 600) {
    return (current_time('timestamp') - $timestamp) <= $threshold;
}

/**
 * Gibt den HTML-Code für das Breaking-News-Badge zurück, falls der Beitrag als breaking gilt.
 *
 * @param int $timestamp Unix-Zeitstempel des Beitrags.
 * @param int $threshold Schwellenwert in Sekunden.
 * @return string HTML des Badges oder leer.
 */
function nt_get_breaking_badge($timestamp, $threshold = 600) {
    if (nt_is_breaking_news($timestamp, $threshold)) {
        return '<span class="nt-breaking-news">' . __('Breaking News', 'news-ticker') . '</span>';
    }
    return '';
}
?>
