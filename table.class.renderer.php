<?php
/**
 * Tabellen-Renderer-Klasse
 * Version: 2.0
 */
class TableRenderer {
    private $config;
    private $tableId;
    private $sortColumn;
    private $sortDirection;
    private $currentPage;
    
    public function __construct($config = [], $sortColumn = null, $sortDirection = null, $currentPage = 1) {
        $this->config = array_merge(TableConfig::$defaults, $config);
        $this->tableId = !empty($this->config['table_id']) ? 
                        $this->config['table_id'] : 
                        'php-table-' . uniqid();
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        $this->currentPage = $currentPage;
    }
    
    public function renderTable($headers, $data, $sortColumn = null, $sortDirection = null, $currentPage = 1, $allData = null) {
        $output = '<div class="php-table-container">';
        
        // Für Paginierung nutzen wir allData, wenn verfügbar
        $totalItems = $allData ? count($allData) : count($data);
        $totalPages = $this->config['pagination_enabled'] ? 
                     ceil($totalItems / $this->config['rows_per_page']) : 1;
        
        // Paginierung rendern wenn aktiviert
        if ($this->config['pagination_enabled'] && 
            ($this->config['pagination_position'] == 'top' || 
             $this->config['pagination_position'] == 'both')) {
            $output .= $this->renderPagination($currentPage, $totalPages);
        }
        
        // Tabelle beginnen
        $output .= '<table id="' . htmlspecialchars($this->tableId) . '" ';
        $output .= 'class="' . htmlspecialchars($this->config['table_class']) . '" ';
        $output .= 'width="' . htmlspecialchars($this->config['table_width']) . '" ';
        $output .= 'border="' . htmlspecialchars($this->config['table_border']) . '" ';
        $output .= 'cellpadding="' . htmlspecialchars($this->config['table_cellpadding']) . '" ';
        $output .= 'cellspacing="' . htmlspecialchars($this->config['table_cellspacing']) . '">';
        
        // Tabellenkopf rendern
        $output .= $this->renderHeader($headers, $sortColumn, $sortDirection);
        
        // Tabellenkörper rendern
        $output .= $this->renderBody($headers, $data);
        
        // Tabelle beenden
        $output .= '</table>';
        
        // Paginierung rendern wenn aktiviert
        if ($this->config['pagination_enabled'] && 
            ($this->config['pagination_position'] == 'bottom' || 
             $this->config['pagination_position'] == 'both')) {
            $output .= $this->renderPagination($currentPage, $totalPages);
        }
        
        // Verstecktes Formular mit aktuellen Daten
        $output .= $this->renderDataForm($allData);
        
        $output .= '</div>';
        
        return $output;
    }
    
    private function renderHeader($headers, $sortColumn, $sortDirection) {
        $output = '<thead><tr style="';
        $output .= 'background-color: ' . htmlspecialchars($this->config['header_bg_color']) . '; ';
        $output .= 'color: ' . htmlspecialchars($this->config['header_text_color']) . '; ';
        $output .= 'font-weight: ' . htmlspecialchars($this->config['header_font_weight']) . '; ';
        $output .= 'text-align: ' . htmlspecialchars($this->config['header_text_align']) . ';">';
        
        foreach ($headers as $key => $header) {
            $output .= '<th style="padding: ' . htmlspecialchars($this->config['cell_padding']) . ';" ';
            $output .= 'class="' . ($this->config['sorting_enabled'] ? 'php-table-sortable' : '') . '">';
            
            if ($this->config['sorting_enabled']) {
                // Bestimme die nächste Sortierrichtung
                $nextDirection = 'asc';
                if ($sortColumn === $key) {
                    $nextDirection = ($sortDirection === 'asc') ? 'desc' : '';
                }
                
                // Erstelle Link für die Sortierung mit POST-Formular
                $output .= '<form method="post" style="margin:0;padding:0;">';
                $output .= '<input type="hidden" name="sort_column" value="' . ($nextDirection ? $key : '') . '">';
                $output .= '<input type="hidden" name="sort_direction" value="' . $nextDirection . '">';
                if ($this->currentPage) {
                    $output .= '<input type="hidden" name="page" value="' . $this->currentPage . '">';
                }
                $output .= '<button type="submit" style="background:none;border:none;padding:0;margin:0;font:inherit;color:inherit;text-align:inherit;width:100%;cursor:pointer;text-decoration:none;">';
            }
            
            $output .= htmlspecialchars($header);
            
            // Sortierindikator hinzufügen wenn diese Spalte sortiert ist
            if ($this->config['sorting_enabled'] && $sortColumn === $key) {
                $icon = ($sortDirection === 'asc') ? 
                         $this->config['sort_asc_icon'] : 
                         $this->config['sort_desc_icon'];
                $output .= ' <span class="php-table-sort-icon">' . $icon . '</span>';
            } elseif ($this->config['sorting_enabled']) {
                $output .= ' <span class="php-table-sort-icon">' . $this->config['sort_none_icon'] . '</span>';
            }
            
            if ($this->config['sorting_enabled']) {
                $output .= '</button></form>';
            }
            
            $output .= '</th>';
        }
        
        $output .= '</tr></thead>';
        return $output;
    }
    
    private function renderBody($headers, $data) {
        $output = '<tbody>';
        
        if (empty($data)) {
            $colCount = count($headers);
            $output .= '<tr><td colspan="' . $colCount . '" style="text-align:center;padding:20px;">Keine Daten vorhanden</td></tr>';
        } else {
            foreach ($data as $rowIndex => $row) {
                $isEven = $rowIndex % 2 === 0;
                $bgColor = $isEven ? 
                          $this->config['row_even_bg_color'] : 
                          $this->config['row_odd_bg_color'];
                
                $output .= '<tr style="';
                $output .= 'background-color: ' . htmlspecialchars($bgColor) . '; ';
                $output .= 'color: ' . htmlspecialchars($this->config['row_text_color']) . ';" ';
                $output .= 'onmouseover="this.style.backgroundColor=\'' . htmlspecialchars($this->config['row_hover_color']) . '\'" ';
                $output .= 'onmouseout="this.style.backgroundColor=\'' . htmlspecialchars($bgColor) . '\'">';
                
                foreach (array_keys($headers) as $key) {
                    $value = isset($row[$key]) ? $row[$key] : '';
                    
                    $output .= '<td style="';
                    $output .= 'padding: ' . htmlspecialchars($this->config['cell_padding']) . '; ';
                    $output .= 'text-align: ' . htmlspecialchars($this->config['cell_text_align']) . '; ';
                    $output .= 'font-style: ' . htmlspecialchars($this->config['cell_font_style']) . '; ';
                    $output .= 'font-weight: ' . htmlspecialchars($this->config['cell_font_weight']) . ';">';
                    $output .= htmlspecialchars($value);
                    $output .= '</td>';
                }
                
                $output .= '</tr>';
            }
        }
        
        $output .= '</tbody>';
        return $output;
    }
    
    private function renderPagination($currentPage, $totalPages) {
        if ($totalPages <= 1) {
            return '';
        }
        
        $output = '<div class="php-table-pagination">';
        
        // Vorherige-Seite Button
        if ($currentPage > 1) {
            $output .= '<form method="post" style="display:inline;">';
            $output .= '<input type="hidden" name="page" value="' . ($currentPage - 1) . '">';
            if ($this->sortColumn) {
                $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
            }
            $output .= '<button type="submit" class="php-table-page-btn">&laquo; Zurück</button>';
            $output .= '</form>';
        } else {
            $output .= '<span class="php-table-page-btn disabled">&laquo; Zurück</span>';
        }
        
        // Seitenzahlen
        $output .= '<span class="php-table-page-info">Seite ' . $currentPage . ' von ' . $totalPages . '</span>';
        
        // Numerische Links für Seiten
        if ($totalPages <= 10) {
            // Alle Seiten anzeigen, wenn es weniger als 10 sind
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    $output .= '<span class="php-table-page-number active">' . $i . '</span>';
                } else {
                    $output .= '<form method="post" style="display:inline;">';
                    $output .= '<input type="hidden" name="page" value="' . $i . '">';
                    if ($this->sortColumn) {
                        $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                        $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
                    }
                    $output .= '<button type="submit" class="php-table-page-number">' . $i . '</button>';
                    $output .= '</form>';
                }
            }
        } else {
            // Zeige nur einen Teil der Seiten an
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $startPage + 4);
            
            if ($startPage > 1) {
                $output .= '<form method="post" style="display:inline;">';
                $output .= '<input type="hidden" name="page" value="1">';
                if ($this->sortColumn) {
                    $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                    $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
                }
                $output .= '<button type="submit" class="php-table-page-number">1</button>';
                $output .= '</form>';
                
                if ($startPage > 2) {
                    $output .= '<span class="php-table-page-ellipsis">...</span>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    $output .= '<span class="php-table-page-number active">' . $i . '</span>';
                } else {
                    $output .= '<form method="post" style="display:inline;">';
                    $output .= '<input type="hidden" name="page" value="' . $i . '">';
                    if ($this->sortColumn) {
                        $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                        $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
                    }
                    $output .= '<button type="submit" class="php-table-page-number">' . $i . '</button>';
                    $output .= '</form>';
                }
            }
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    $output .= '<span class="php-table-page-ellipsis">...</span>';
                }
                
                $output .= '<form method="post" style="display:inline;">';
                $output .= '<input type="hidden" name="page" value="' . $totalPages . '">';
                if ($this->sortColumn) {
                    $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                    $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
                }
                $output .= '<button type="submit" class="php-table-page-number">' . $totalPages . '</button>';
                $output .= '</form>';
            }
        }
        
        // Nächste-Seite Button
        if ($currentPage < $totalPages) {
            $output .= '<form method="post" style="display:inline;">';
            $output .= '<input type="hidden" name="page" value="' . ($currentPage + 1) . '">';
            if ($this->sortColumn) {
                $output .= '<input type="hidden" name="sort_column" value="' . $this->sortColumn . '">';
                $output .= '<input type="hidden" name="sort_direction" value="' . $this->sortDirection . '">';
            }
            $output .= '<button type="submit" class="php-table-page-btn">Weiter &raquo;</button>';
            $output .= '</form>';
        } else {
            $output .= '<span class="php-table-page-btn disabled">Weiter &raquo;</span>';
        }
        
        $output .= '</div>';
        return $output;
    }
    
    // Dieses Formular ist nicht mehr notwendig, da wir jetzt reguläre Formulare verwenden
    private function renderDataForm($data) {
        return '';
    }
}
?>