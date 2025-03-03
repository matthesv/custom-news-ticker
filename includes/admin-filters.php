<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fügt in der Admin-Übersicht des Post-Types "news_ticker" ein Dropdown zur Filterung nach der Taxonomie "ticker_category" hinzu.
 */
function nt_add_taxonomy_filters() {
    global $typenow;
    if ($typenow == 'news_ticker') {
        $taxonomy = 'ticker_category';
        $current_value = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        ?>
        <select name="<?php echo esc_attr($taxonomy); ?>">
            <option value=""><?php _e('Alle Kategorien', 'news-ticker'); ?></option>
            <option value="none" <?php selected($current_value, 'none'); ?>><?php _e('Ohne Kategorie', 'news-ticker'); ?></option>
            <?php
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
            ]);
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    ?>
                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($current_value, $term->term_id); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'nt_add_taxonomy_filters');

/**
 * Passt die WP_Query in der Admin-Übersicht an, um anhand des ausgewählten Filters zu sortieren.
 *
 * @param WP_Query $query Die aktuelle Query.
 */
function nt_filter_posts_by_taxonomy($query) {
    global $pagenow;
    $post_type = 'news_ticker';
    $taxonomy  = 'ticker_category';

    if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == $post_type && isset($_GET[$taxonomy]) && $_GET[$taxonomy] !== '') {
        if ($_GET[$taxonomy] === 'none') {
            $query->set('tax_query', [
                [
                    'taxonomy' => $taxonomy,
                    'operator' => 'NOT EXISTS'
                ]
            ]);
        } else {
            $query->set('tax_query', [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => intval($_GET[$taxonomy]),
                ]
            ]);
        }
    }
}
add_filter('pre_get_posts', 'nt_filter_posts_by_taxonomy');
?>
