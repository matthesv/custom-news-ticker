<?php
if (!defined('ABSPATH')) exit;

/**
 * Fügt eine Meta Box hinzu, um zu entscheiden, ob das Aktualisierungsdatum anstelle des Veröffentlichungsdatums verwendet wird.
 */
function nt_add_update_date_meta_box() {
    add_meta_box(
        'nt_update_date_meta',
        __('Aktualisierungsdatum berücksichtigen', 'news-ticker'),
        'nt_render_update_date_meta_box',
        'news_ticker',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nt_add_update_date_meta_box');

/**
 * Rendert die Meta Box für das Aktualisierungsdatum.
 *
 * @param WP_Post $post Der aktuelle Post.
 */
function nt_render_update_date_meta_box($post) {
    wp_nonce_field('nt_save_update_date', 'nt_update_date_nonce');
    $use_update_date = get_post_meta($post->ID, 'nt_use_updated_date', true);
    ?>
    <p>
        <label>
            <input type="checkbox" name="nt_use_updated_date" value="yes" <?php checked($use_update_date, 'yes'); ?> />
            <?php _e('Aktualisierungsdatum anstelle des Veröffentlichungsdatums verwenden', 'news-ticker'); ?>
        </label>
    </p>
    <?php
}

/**
 * Speichert die Meta Box Daten für das Aktualisierungsdatum.
 *
 * @param int $post_id Die ID des Posts.
 */
function nt_save_update_date_meta_box($post_id) {
    if (!isset($_POST['nt_update_date_nonce']) || !wp_verify_nonce($_POST['nt_update_date_nonce'], 'nt_save_update_date')) {
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
    
    if (isset($_POST['nt_use_updated_date']) && $_POST['nt_use_updated_date'] === 'yes') {
        update_post_meta($post_id, 'nt_use_updated_date', 'yes');
    } else {
        delete_post_meta($post_id, 'nt_use_updated_date');
    }
}
add_action('save_post', 'nt_save_update_date_meta_box');
?>
