<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fügt eine Meta Box "Verwendete Quellen" hinzu,
 * in der mehrere Links (durch Komma getrennt) eingetragen werden können.
 */
function nt_add_sources_meta_box() {
    add_meta_box(
        'nt_sources_meta',
        __('Verwendete Quellen', 'news-ticker'),
        'nt_render_sources_meta_box',
        'news_ticker',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'nt_add_sources_meta_box');

/**
 * Rendert die Meta Box für "Verwendete Quellen".
 *
 * @param WP_Post $post
 */
function nt_render_sources_meta_box($post) {
    wp_nonce_field('nt_save_sources', 'nt_sources_nonce');
    $sources = get_post_meta($post->ID, 'nt_sources', true);
    ?>
    <p><?php _e('Bitte geben Sie die Quellen-Links ein, getrennt durch Kommas.', 'news-ticker'); ?></p>
    <textarea name="nt_sources" rows="3" style="width:100%;"><?php echo esc_textarea($sources); ?></textarea>
    <?php
}

/**
 * Speichert die Meta-Box-Daten für "Verwendete Quellen".
 *
 * @param int $post_id
 */
function nt_save_sources_meta_box($post_id) {
    // Sicherheitscheck
    if (!isset($_POST['nt_sources_nonce']) || !wp_verify_nonce($_POST['nt_sources_nonce'], 'nt_save_sources')) {
        return;
    }
    // Autosave?
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Berechtigungen prüfen
    if (isset($_POST['post_type']) && $_POST['post_type'] === 'news_ticker') {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Daten speichern oder löschen
    if (isset($_POST['nt_sources'])) {
        $sources = sanitize_textarea_field($_POST['nt_sources']);
        update_post_meta($post_id, 'nt_sources', $sources);
    } else {
        delete_post_meta($post_id, 'nt_sources');
    }
}
add_action('save_post', 'nt_save_sources_meta_box');
