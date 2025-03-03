<?php
if (!defined('ABSPATH')) exit;

/**
 * Fügt eine Meta Box hinzu, um zu entscheiden, ob der Hintergrund des News-Beitrags eingefärbt werden soll.
 */
function nt_add_background_color_meta_box() {
    add_meta_box(
        'nt_background_color_meta',
        __('Hintergrund einfärben', 'news-ticker'),
        'nt_render_background_color_meta_box',
        'news_ticker',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nt_add_background_color_meta_box');

/**
 * Rendert die Meta Box für die Hintergrundfarbe.
 *
 * @param WP_Post $post Der aktuelle Post.
 */
function nt_render_background_color_meta_box($post) {
    wp_nonce_field('nt_save_background_color', 'nt_background_color_nonce');
    $background_color = get_post_meta($post->ID, 'nt_background_color', true);
    ?>
    <p>
        <label>
            <input type="checkbox" name="nt_background_color" value="yes" <?php checked($background_color, 'yes'); ?> />
            <?php _e('Hintergrund einfärben', 'news-ticker'); ?>
        </label>
    </p>
    <?php
}

/**
 * Speichert die Meta Box Daten für die Hintergrundfarbe.
 *
 * @param int $post_id Die ID des Posts.
 */
function nt_save_background_color_meta_box($post_id) {
    if (!isset($_POST['nt_background_color_nonce']) || !wp_verify_nonce($_POST['nt_background_color_nonce'], 'nt_save_background_color')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['post_type']) && 'news_ticker' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    if (isset($_POST['nt_background_color']) && $_POST['nt_background_color'] === 'yes') {
        update_post_meta($post_id, 'nt_background_color', 'yes');
    } else {
        delete_post_meta($post_id, 'nt_background_color');
    }
}
add_action('save_post', 'nt_save_background_color_meta_box');
?>
