<?php
if (!defined('ABSPATH')) exit;

/**
 * Übersetzt Zeit-Angaben in verschiedene Sprachen
 * 
 * @param string $time_diff Die Zeit von WordPress human_time_diff
 * @param string $language Der Sprachcode (de, en, fr, etc.)
 * @return string Übersetzte Zeitangabe mit korrekter Grammatik
 */
function nt_translate_time($time_diff, $language = '') {
    // Wenn keine Sprache angegeben, nutze die WordPress-Sprache
    if (empty($language)) {
        $language = substr(get_locale(), 0, 2);
    }
    
    // Extrahiere numerischen Wert und Zeiteinheit
    preg_match('/(\d+)\s+(\w+)/', $time_diff, $matches);
    
    if (empty($matches)) {
        return $time_diff;
    }
    
    $number = $matches[1];
    $unit = $matches[2];
    
    // Übersetzungen nach Sprache
    switch ($language) {
        case 'de':
            return nt_translate_time_de($number, $unit);
        case 'fr':
            return nt_translate_time_fr($number, $unit);
        case 'es':
            return nt_translate_time_es($number, $unit);
        // Weitere Sprachen können hier hinzugefügt werden
        default:
            return $time_diff . ' ago';
    }
}

/**
 * Deutsche Übersetzung
 */
function nt_translate_time_de($number, $unit) {
    $unit_translations = [
        'second' => 'Sekunde',
        'seconds' => 'Sekunden',
        'minute' => 'Minute',
        'minutes' => 'Minuten',
        'hour' => 'Stunde',
        'hours' => 'Stunden',
        'day' => 'Tag',
        'days' => 'Tagen',
        'week' => 'Woche',
        'weeks' => 'Wochen',
        'month' => 'Monat',
        'months' => 'Monaten',
        'year' => 'Jahr',
        'years' => 'Jahren'
    ];
    
    if (isset($unit_translations[$unit])) {
        return "vor {$number} {$unit_translations[$unit]}";
    }
    
    return "vor {$number} {$unit}";
}

/**
 * Französische Übersetzung
 */
function nt_translate_time_fr($number, $unit) {
    $unit_translations = [
        'second' => 'seconde',
        'seconds' => 'secondes',
        'minute' => 'minute',
        'minutes' => 'minutes',
        'hour' => 'heure',
        'hours' => 'heures',
        'day' => 'jour',
        'days' => 'jours',
        'week' => 'semaine',
        'weeks' => 'semaines',
        'month' => 'mois',
        'months' => 'mois',
        'year' => 'an',
        'years' => 'ans'
    ];
    
    if (isset($unit_translations[$unit])) {
        return "il y a {$number} {$unit_translations[$unit]}";
    }
    
    return "il y a {$number} {$unit}";
}

/**
 * Spanische Übersetzung
 */
function nt_translate_time_es($number, $unit) {
    $unit_translations = [
        'second' => 'segundo',
        'seconds' => 'segundos',
        'minute' => 'minuto',
        'minutes' => 'minutos',
        'hour' => 'hora',
        'hours' => 'horas',
        'day' => 'día',
        'days' => 'días',
        'week' => 'semana',
        'weeks' => 'semanas',
        'month' => 'mes',
        'months' => 'meses',
        'year' => 'año',
        'years' => 'años'
    ];
    
    if (isset($unit_translations[$unit])) {
        return "hace {$number} {$unit_translations[$unit]}";
    }
    
    return "hace {$number} {$unit}";
}