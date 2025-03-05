<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Neues Feld "Ziel-URL für diese Kategorie" im Formular zum Hinzufügen einer neuen Kategorie.
 */
function nt_ticker_category_add_meta_field() {
    ?>
    <div class="form-field">
        <label for="nt_category_url"><?php _e('Ziel-URL für diese Kategorie', 'news-ticker'); ?></label>
        <input type="text" name="nt_category_url" id="nt_category_url" value="">
        <p class="description">
            <?php _e('Wenn eine News-Meldung dieser Kategorie zugeordnet ist, wird beim Klick auf die Headline diese URL aufgerufen.', 'news-ticker'); ?>
        </p>
    </div>
    <?php
}
add_action('ticker_category_add_form_fields', 'nt_ticker_category_add_meta_field', 10, 2);

/**
 * Neues Feld "Ziel-URL für diese Kategorie" beim Bearbeiten einer bestehenden Kategorie.
 */
function nt_ticker_category_edit_meta_field($term) {
    $category_url = get_term_meta($term->term_id, 'nt_category_url', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="nt_category_url"><?php _e('Ziel-URL für diese Kategorie', 'news-ticker'); ?></label>
        </th>
        <td>
            <input type="text" name="nt_category_url" id="nt_category_url" value="<?php echo esc_attr($category_url); ?>">
            <p class="description">
                <?php _e('Wenn eine News-Meldung dieser Kategorie zugeordnet ist, wird beim Klick auf die Headline diese URL aufgerufen.', 'news-ticker'); ?>
            </p>
        </td>
    </tr>
    <?php
}
add_action('ticker_category_edit_form_fields', 'nt_ticker_category_edit_meta_field', 10, 2);

/**
 * Speichert das benutzerdefinierte URL-Feld beim Erstellen oder Bearbeiten der Kategorie.
 */
function nt_save_ticker_category_custom_meta($term_id) {
    if (isset($_POST['nt_category_url'])) {
        update_term_meta($term_id, 'nt_category_url', sanitize_text_field($_POST['nt_category_url']));
    }
}
add_action('created_ticker_category', 'nt_save_ticker_category_custom_meta', 10, 2);
add_action('edited_ticker_category', 'nt_save_ticker_category_custom_meta', 10, 2);
