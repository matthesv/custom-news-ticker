<?php
if (!defined('ABSPATH')) exit;

function nt_enqueue_admin_scripts($hook) {
    // Enqueue nur auf der Plugin-Einstellungsseite oder beim Bearbeiten von news_ticker-Posts
    if ( $hook !== 'settings_page_news-ticker-settings' && get_post_type() !== 'news_ticker' ) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('nt-admin-script', NEWS_TICKER_URL . 'assets/admin.js', array('wp-color-picker', 'jquery'), false, true);
}
add_action('admin_enqueue_scripts', 'nt_enqueue_admin_scripts');
?>
