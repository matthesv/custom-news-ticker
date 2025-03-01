<?php
if (!defined('ABSPATH')) exit;

/**
 * Fügt eine Meta Box hinzu, um die individuelle Randfarbe für einen News-Eintrag festzulegen.
 */
function nt_add_border_color_meta_box() {
    add_meta_box(
        'nt_border_color_meta',
        __('Randfarbe für diesen News-Eintrag', 'news-ticker'),
        'nt_render_border_color_meta_box',
        'news_ticker',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nt_add_border_color_meta_box');

/**
 * Rendert die Meta Box für die Randfarbe.
 *
 * @param WP_Post $post Der aktuelle Post.
 */
function nt_render_border_color_meta_box($post) {
    // Sicherheits-Nounce hinzufügen
    wp_nonce_field('nt_save_border_color', 'nt_border_color_nonce');
    // Vorhandenen Wert abrufen oder Standardwert verwenden
    $border_color = get_post_meta($post->ID, 'nt_border_color', true);
    if (!$border_color) {
        $border_color = get_option('news_ticker_border_color', '#FF4500');
    }
    echo '<label for="nt_border_color">' . __('Randfarbe:', 'news-ticker') . '</label>';
    echo '<input type="text" id="nt_border_color" name="nt_border_color" value="' . esc_attr($border_color) . '" class="my-color-field" data-default-color="#FF4500" />';
}

/**
 * Speichert die Meta Box Daten beim Speichern des Beitrags.
 *
 * @param int $post_id Die ID des Posts.
 */
function nt_save_border_color_meta_box($post_id) {
    // Sicherheitsüberprüfung
    if (!isset($_POST['nt_border_color_nonce']) || !wp_verify_nonce($_POST['nt_border_color_nonce'], 'nt_save_border_color')) {
        return;
    }
    // Autosave überprüfen
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Berechtigungen überprüfen
    if (isset($_POST['post_type']) && 'news_ticker' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    // Speichern oder Löschen des Wertes
    if (isset($_POST['nt_border_color'])) {
        $new_color = sanitize_text_field($_POST['nt_border_color']);
        update_post_meta($post_id, 'nt_border_color', $new_color);
    } else {
        delete_post_meta($post_id, 'nt_border_color');
    }
}
add_action('save_post', 'nt_save_border_color_meta_box');
?>
