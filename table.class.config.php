<?php
/**
 * Tabellen-Konfigurationsklasse
 * Version: 2.1
 */
class TableConfig {
    // Standardwerte für die Tabellenkonfiguration
    public static $defaults = [
        // Tabellen-Styling
        'table_class' => 'php-table',
        'table_id' => '',
        'table_width' => '100%',
        'table_border' => '1',
        'table_cellpadding' => '5',
        'table_cellspacing' => '0',
        
        // Header-Styling
        'header_bg_color' => '#f8f9fa',
        'header_text_color' => '#212529',
        'header_font_weight' => 'bold',
        'header_text_align' => 'center',
        
        // Zeilen-Styling
        'row_alternate_coloring' => true,
        'row_even_bg_color' => '#ffffff',
        'row_odd_bg_color' => '#f2f2f2',
        'row_hover_color' => '#e8e8e8',
        'row_text_color' => '#212529',
        'row_even_text_color' => '#212529', // Neue Option
        'row_odd_text_color' => '#212529',  // Neue Option
        
        // Zellen-Styling
        'cell_padding' => '8px',
        'cell_text_align' => 'left',
        'cell_font_style' => 'normal',
        'cell_font_weight' => 'normal',
        
        // Rahmen-Styling
        'border_color' => '#000000', // Neue Option
        
        // Seitennummerierung
        'pagination_enabled' => false,
        'rows_per_page' => 10,
        'pagination_position' => 'bottom', // 'top', 'bottom', 'both'
        
        // Sortierung
        'sorting_enabled' => true,
        'default_sort_column' => null,
        'default_sort_direction' => 'asc',
        'sort_asc_icon' => '▲',
        'sort_desc_icon' => '▼',
        'sort_none_icon' => '⇅',
        
        // HTML-Rendering
        'render_html' => false, // Neue Option
    ];
}
?>