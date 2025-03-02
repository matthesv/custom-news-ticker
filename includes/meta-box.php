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
    
    // Vorhandene Werte abrufen oder Standardwerte verwenden
    $border_color = get_post_meta($post->ID, 'nt_border_color', true);
    $use_global_color = get_post_meta($post->ID, 'nt_use_global_color', true);
    
    // Wenn noch kein Wert gesetzt ist, als Standard die globale Farbe verwenden
    if ($use_global_color === '') {
        $use_global_color = 'yes';
    }
    
    if (!$border_color) {
        $border_color = get_option('news_ticker_border_color', '#FF4500');
    }
    
    // Optionen anzeigen
    echo '<div>';
    echo '<p>';
    echo '<label>';
    echo '<input type="radio" name="nt_use_global_color" value="yes" ' . checked($use_global_color, 'yes', false) . '> ';
    echo __('Globale Farbeinstellung verwenden', 'news-ticker');
    echo '</label><br>';
    echo '<label>';
    echo '<input type="radio" name="nt_use_global_color" value="no" ' . checked($use_global_color, 'no', false) . '> ';
    echo __('Individuelle Farbe für diesen Eintrag verwenden', 'news-ticker');
    echo '</label>';
    echo '</p>';
    echo '<div id="custom_color_field" style="' . ($use_global_color === 'yes' ? 'display:none;' : '') . '">';
    echo '<label for="nt_border_color">' . __('Individuelle Randfarbe:', 'news-ticker') . '</label>';
    echo '<input type="text" id="nt_border_color" name="nt_border_color" value="' . esc_attr($border_color) . '" class="my-color-field" data-default-color="#FF4500" />';
    echo '</div>';
    echo '</div>';
    
    // JavaScript zum Ein-/Ausblenden des Farbfeldes
    echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $("input[name=\'nt_use_global_color\']").change(function() {
                if ($(this).val() === "yes") {
                    $("#custom_color_field").hide();
                } else {
                    $("#custom_color_field").show();
                }
            });
        });
    </script>';
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
    
    // Speichern der Einstellung, ob globale Farbe verwendet werden soll
    if (isset($_POST['nt_use_global_color'])) {
        $use_global_color = sanitize_text_field($_POST['nt_use_global_color']);
        update_post_meta($post_id, 'nt_use_global_color', $use_global_color);
    }
    
    // Speichern oder Löschen der individuellen Farbe
    if (isset($_POST['nt_border_color'])) {
        $new_color = sanitize_text_field($_POST['nt_border_color']);
        update_post_meta($post_id, 'nt_border_color', $new_color);
    } else {
        delete_post_meta($post_id, 'nt_border_color');
    }
}
add_action('save_post', 'nt_save_border_color_meta_box');