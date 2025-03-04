<?php
if (!defined('ABSPATH')) exit;

/**
 * Fügt die Einstellungen-Seite dem WordPress-Admin-Menü hinzu.
 */
function nt_add_settings_menu() {
    add_options_page(
        __('News Ticker Einstellungen', 'news-ticker'),
        __('News Ticker', 'news-ticker'),
        'manage_options',
        'news-ticker-settings',
        'nt_settings_page'
    );
}
add_action('admin_menu', 'nt_add_settings_menu');

/**
 * Rendert die Einstellungen-Seite für den News Ticker.
 */
function nt_settings_page() {
    // Einstellungen speichern
    if (isset($_POST['nt_save_settings']) && current_user_can('manage_options')) {
        if (check_admin_referer('nt_settings_nonce')) {
            $language         = isset($_POST['nt_language']) ? sanitize_text_field($_POST['nt_language']) : '';
            $refresh_interval = isset($_POST['nt_refresh_interval']) ? intval($_POST['nt_refresh_interval']) : 60;
            $entries_count    = isset($_POST['nt_entries_count']) ? intval($_POST['nt_entries_count']) : 5;
            $border_color     = isset($_POST['nt_border_color']) ? sanitize_text_field($_POST['nt_border_color']) : '#FF4500';
            $color_source     = isset($_POST['nt_color_source']) ? sanitize_text_field($_POST['nt_color_source']) : 'custom';
            $static_threshold = isset($_POST['nt_static_threshold']) ? intval($_POST['nt_static_threshold']) : 24;
            
            update_option('news_ticker_language', $language);
            update_option('news_ticker_refresh_interval', $refresh_interval);
            update_option('news_ticker_entries_count', $entries_count);
            update_option('news_ticker_border_color', $border_color);
            update_option('news_ticker_color_source', $color_source);
            update_option('news_ticker_static_threshold', $static_threshold);
            
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Einstellungen gespeichert.', 'news-ticker') . '</p></div>';
        }
    }
    
    // Einstellungen abrufen
    $language         = get_option('news_ticker_language', '');
    $refresh_interval = get_option('news_ticker_refresh_interval', 60);
    $entries_count    = get_option('news_ticker_entries_count', 5);
    $border_color     = get_option('news_ticker_border_color', '#FF4500');
    $color_source     = get_option('news_ticker_color_source', 'custom');
    $static_threshold = get_option('news_ticker_static_threshold', 24);
    
    // Theme-Farben abrufen
    $primary_color   = get_theme_mod('primary_color', '#0073aa');
    $secondary_color = get_theme_mod('secondary_color', '#00a0d2');
    
    // Verfügbare Sprachen
    $languages = [
        ''   => __('WordPress-Sprache verwenden', 'news-ticker'),
        'en' => __('Englisch', 'news-ticker'),
        'de' => __('Deutsch', 'news-ticker'),
        'fr' => __('Französisch', 'news-ticker'),
        'es' => __('Spanisch', 'news-ticker')
    ];
    ?>
    <div class="wrap">
        <h1><?php _e('News Ticker Einstellungen', 'news-ticker'); ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('nt_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Sprache für Zeitangaben', 'news-ticker'); ?></th>
                    <td>
                        <select name="nt_language">
                            <?php foreach ($languages as $code => $name) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($language, $code); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Wähle die Sprache für die Anzeige von Zeitangaben wie "vor 3 Stunden".', 'news-ticker'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Aktualisierungsintervall', 'news-ticker'); ?></th>
                    <td>
                        <input type="number" name="nt_refresh_interval" value="<?php echo esc_attr($refresh_interval); ?>" min="10" step="1" class="small-text" />
                        <p class="description"><?php _e('Intervall in Sekunden, in dem der Ticker neue Nachrichten lädt.', 'news-ticker'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Anzahl der Einträge', 'news-ticker'); ?></th>
                    <td>
                        <input type="number" name="nt_entries_count" value="<?php echo esc_attr($entries_count); ?>" min="1" max="20" step="1" class="small-text" />
                        <p class="description"><?php _e('Standardanzahl der Einträge, die im Ticker angezeigt werden.', 'news-ticker'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Farbquelle für den Rand', 'news-ticker'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="nt_color_source" value="custom" <?php checked($color_source, 'custom'); ?> />
                                <?php _e('Benutzerdefinierte Farbe', 'news-ticker'); ?>
                            </label><br>
                            
                            <label style="display: flex; align-items: center; margin-top: 8px;">
                                <input type="radio" name="nt_color_source" value="primary" <?php checked($color_source, 'primary'); ?> />
                                <span style="margin: 0 8px;"><?php _e('Theme Primärfarbe', 'news-ticker'); ?></span>
                                <span class="color-preview" style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($primary_color); ?>; border: 1px solid #ddd; border-radius: 50%;"></span>
                                <code style="margin-left: 8px;"><?php echo esc_html($primary_color); ?></code>
                            </label><br>
                            
                            <label style="display: flex; align-items: center; margin-top: 8px;">
                                <input type="radio" name="nt_color_source" value="secondary" <?php checked($color_source, 'secondary'); ?> />
                                <span style="margin: 0 8px;"><?php _e('Theme Sekundärfarbe', 'news-ticker'); ?></span>
                                <span class="color-preview" style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($secondary_color); ?>; border: 1px solid #ddd; border-radius: 50%;"></span>
                                <code style="margin-left: 8px;"><?php echo esc_html($secondary_color); ?></code>
                            </label>
                        </fieldset>
                        <p class="description"><?php _e('Wählen Sie, ob die Standard-Randfarbe aus einer benutzerdefinierten Farbe oder aus den Theme-Farben entnommen werden soll.', 'news-ticker'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Benutzerdefinierte Randfarbe', 'news-ticker'); ?></th>
                    <td>
                        <input type="text" name="nt_border_color" value="<?php echo esc_attr($border_color); ?>" class="my-color-field" data-default-color="#FF4500" />
                        <p class="description"><?php _e('Wählen Sie die Farbe, die verwendet wird, wenn "Benutzerdefinierte Farbe" ausgewählt ist.', 'news-ticker'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Statischer Modus ab (Stunden)', 'news-ticker'); ?></th>
                    <td>
                        <input type="number" name="nt_static_threshold" value="<?php echo esc_attr($static_threshold); ?>" min="0" max="999" step="1" class="small-text" />
                        <p class="description"><?php _e('Meldungen, die älter als die angegebene Stundenanzahl sind, werden ohne Pulsieren dargestellt.', 'news-ticker'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="nt_save_settings" class="button button-primary" value="<?php _e('Einstellungen speichern', 'news-ticker'); ?>" />
            </p>
        </form>
        
        <hr>
        
        <h2><?php _e('Shortcode-Verwendung', 'news-ticker'); ?></h2>
        <p><?php _e('Füge den News Ticker auf jeder Seite oder in jedem Beitrag mit dem folgenden Shortcode ein:', 'news-ticker'); ?></p>
        <code>[news_ticker]</code>
        
        <p><?php _e('Mit Optionen:', 'news-ticker'); ?></p>
        <code>[news_ticker category="deine-kategorie" posts_per_page="5"]</code>
        
        <h3><?php _e('Verfügbare Parameter:', 'news-ticker'); ?></h3>
        <ul>
            <li><code>category</code> - <?php _e('Slug der Ticker-Kategorie, die angezeigt werden soll.', 'news-ticker'); ?></li>
            <li><code>posts_per_page</code> - <?php _e('Anzahl der anzuzeigenden Einträge.', 'news-ticker'); ?></li>
        </ul>
    </div>
    <?php
}
?> 
