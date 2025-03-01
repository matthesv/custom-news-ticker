<?php
if (!defined('ABSPATH')) exit;

/**
 * Einstellungen-Seite für den News Ticker
 */
function nt_settings_page() {
    // Einstellungen speichern
    if (isset($_POST['nt_save_settings']) && current_user_can('manage_options')) {
        if (check_admin_referer('nt_settings_nonce')) {
            $language = isset($_POST['nt_language']) ? sanitize_text_field($_POST['nt_language']) : '';
            $refresh_interval = isset($_POST['nt_refresh_interval']) ? intval($_POST['nt_refresh_interval']) : 60;
            $entries_count = isset($_POST['nt_entries_count']) ? intval($_POST['nt_entries_count']) : 5;
            
            update_option('news_ticker_language', $language);
            update_option('news_ticker_refresh_interval', $refresh_interval);
            update_option('news_ticker_entries_count', $entries_count);
            
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Einstellungen gespeichert.', 'news-ticker') . '</p></div>';
        }
    }
    
    // Einstellungen abrufen
    $language = get_option('news_ticker_language', '');
    $refresh_interval = get_option('news_ticker_refresh_interval', 60);
    $entries_count = get_option('news_ticker_entries_count', 5);
    
    // Verfügbare Sprachen
    $languages = [
        '' => __('WordPress-Sprache verwenden', 'news-ticker'),
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