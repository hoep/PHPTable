<?php
/**
 * PHPTable - Konfigurierbare HTML-Tabellenklasse
 * Version: 13.4
 */

// Benötigte Dateien einbinden
require_once 'table.class.config.php';
require_once 'table.class.sorter.php';
require_once 'table.class.paginator.php';

class PHPTable {
    private $data = [];
    private $originalData = []; // Speichert die Originaldaten für Neurendering
    private $headers = [];
    public $config = []; // Public für einfachere Fehlersuche
    private $sortColumn = null;
    private $sortDirection = null;
    public $currentPage = 1; // Public für direkten Zugriff
    private $tableVar = null; // ID-Variable für die Tabellenausgabe
    private $columnAlignments = []; // Speichert spaltenspezifische Ausrichtungen
    
    public function __construct($config = []) {
        $this->config = array_merge(TableConfig::$defaults, $config);
        if (isset($config['tableVar'])) {
            $this->setTableVar($config['tableVar']);
        }
    }
    
    // Setter für tableVar
    public function setTableVar($tableVar) {
        // Prüfen, ob es sich um eine 5-stellige Integer handelt
        if (!is_int($tableVar) || $tableVar < 10000 || $tableVar > 99999) {
            throw new InvalidArgumentException('tableVar muss eine 5-stellige Integer sein.');
        }
        $this->tableVar = $tableVar;
        return $this;
    }
    
    // Getter für tableVar
    public function getTableVar() {
        return $this->tableVar;
    }
    
    public function setData($data) {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Daten müssen ein Array sein');
        }
        $this->data = $data;
        $this->originalData = $data; // Originaldaten speichern
        return $this;
    }
    
    public function setHeaders($headers) {
        if (!is_array($headers)) {
            throw new InvalidArgumentException('Header müssen ein Array sein');
        }
        $this->headers = $headers;
        return $this;
    }
    
    public function setConfig($config) {
        if (!is_array($config)) {
            throw new InvalidArgumentException('Konfiguration muss ein Array sein');
        }
        $this->config = array_merge($this->config, $config);
        return $this;
    }
    
    // Verarbeitet GET-Anfragen für Sortierung und Paginierung
    public function processRequest() {
        // Normale Verarbeitung
        if (isset($_GET['sort_column'])) {
            $this->sortColumn = !empty($_GET['sort_column']) ? $_GET['sort_column'] : null;
            $this->sortDirection = !empty($_GET['sort_direction']) ? $_GET['sort_direction'] : null;
        }
        
        if (isset($_GET['page'])) {
            $this->currentPage = max(1, (int)$_GET['page']);
        }
        
        return $this;
    }
    
    // Setzt die anzuzeigende Seite
    public function showPage($pageNumber) {
        $this->currentPage = max(1, (int)$pageNumber);
        return $this;
    }
    
    // Aktiviert HTML-Rendering in Zellen
    public function enableHtmlRendering() {
        $this->config['render_html'] = true;
        return $this;
    }
    
    // Deaktiviert HTML-Rendering in Zellen
    public function disableHtmlRendering() {
        $this->config['render_html'] = false;
        return $this;
    }
    
    // Rendert die Tabelle und gibt sie mit SetValueString zurück
    public function render() {
        // Prüfen, ob tableVar gesetzt ist
        if ($this->tableVar === null) {
            return 'Fehler: tableVar muss gesetzt werden!';
        }
        
        // Daten verarbeiten (sortieren und paginieren)
        $processedData = $this->originalData; // Mit Originaldaten beginnen
        
        // Wenn Standardsortierung gesetzt ist aber noch keine aktive Sortierung, 
        // die Standardsortierung anwenden
        if ($this->sortColumn === null && isset($this->config['default_sort_column'])) {
            $this->sortColumn = $this->config['default_sort_column'];
            $this->sortDirection = $this->config['default_sort_direction'];
        }
        
        // Sortierung anwenden
        if ($this->config['sorting_enabled'] && $this->sortColumn !== null) {
            $sorter = new TableSorter($processedData, $this->sortColumn, $this->sortDirection);
            $processedData = $sorter->sort();
        }
        
        // Gesamtzahl der Daten für Paginierung
        $totalItems = count($processedData);
        $totalPages = $this->config['pagination_enabled'] ? 
                     ceil($totalItems / $this->config['rows_per_page']) : 1;
        
        // Paginierung anwenden
        if ($this->config['pagination_enabled']) {
            $paginator = new TablePaginator(
                $processedData, 
                $this->currentPage, 
                $this->config['rows_per_page']
            );
            $processedData = $paginator->paginate();
        }
        
        // HTML für die Tabelle generieren
        $output = $this->renderTable($processedData, $totalPages);
        
        // In tableVar speichern
        SetValueString($this->tableVar, $output);
        
        return $output;
    }
    
    // Rendert die komplette Tabelle mit Container
    private function renderTable($data, $totalPages) {
        $output = '<div class="php-table-container" style="margin-bottom: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
        
        // Tabelle beginnen
        $output .= '<table id="' . htmlspecialchars($this->config['table_id']) . '" ';
        $output .= 'class="' . htmlspecialchars($this->config['table_class']) . '" ';
        $output .= 'width="' . htmlspecialchars($this->config['table_width']) . '" ';
        $output .= 'border="' . htmlspecialchars($this->config['table_border']) . '" ';
        $output .= 'cellpadding="' . htmlspecialchars($this->config['table_cellpadding']) . '" ';
        $output .= 'cellspacing="' . htmlspecialchars($this->config['table_cellspacing']) . '" ';
        $output .= 'style="border-collapse: collapse; width: 100%; font-size: 14px; border-color: ' . htmlspecialchars($this->config['border_color']) . ';">';
        
        // Tabellenkopf
        $output .= $this->renderTableHeader();
        
        // Tabellenkörper
        $output .= $this->renderTableBody($data);
        
        // Tabelle beenden
        $output .= '</table>';
        
        // Falls Paginierung aktiviert ist, Paginierungsanzeige hinzufügen
        if ($this->config['pagination_enabled'] && $totalPages > 1) {
            $output .= $this->renderPagination($totalPages);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    // Rendert den Tabellenkopf ohne Links
    private function renderTableHeader() {
        $output = '<thead><tr style="';
        $output .= 'background-color: ' . htmlspecialchars($this->config['header_bg_color']) . '; ';
        $output .= 'color: ' . htmlspecialchars($this->config['header_text_color']) . '; ';
        $output .= 'font-weight: ' . htmlspecialchars($this->config['header_font_weight']) . '; ';
        $output .= 'text-align: ' . htmlspecialchars($this->config['header_text_align']) . ';">';
        
        foreach ($this->headers as $key => $header) {
            $output .= '<th style="padding: ' . htmlspecialchars($this->config['cell_padding']) . ';">';
            
            // Nur Text und Icon ohne Link
            $output .= htmlspecialchars($header);
                
            // Sortierindikator anzeigen
            $isCurrentSortColumn = (string)$this->sortColumn === (string)$key;
            if ($isCurrentSortColumn) {
                $iconKey = ($this->sortDirection === 'asc') ? 'sort_asc_icon' : 'sort_desc_icon';
                $icon = isset($this->config[$iconKey]) ? $this->config[$iconKey] : '?';
                $output .= ' <span class="php-table-sort-icon">' . $icon . '</span>';
            } else {
                $icon = isset($this->config['sort_none_icon']) ? $this->config['sort_none_icon'] : '-';
                $output .= ' <span class="php-table-sort-icon">' . $icon . '</span>';
            }
            
            $output .= '</th>';
        }
        
        $output .= '</tr></thead>';
        return $output;
    }
    
    // Rendert den Tabellenkörper
    private function renderTableBody($data) {
        $output = '<tbody>';
        
        if (empty($data)) {
            $colCount = count($this->headers);
            $output .= '<tr><td colspan="' . $colCount . '" style="text-align:center;padding:20px;">Keine Daten vorhanden</td></tr>';
        } else {
            foreach ($data as $rowIndex => $row) {
                $isEven = $rowIndex % 2 === 0;
                $bgColor = $isEven ? 
                          $this->config['row_even_bg_color'] : 
                          $this->config['row_odd_bg_color'];
                $textColor = $isEven ? 
                          $this->config['row_even_text_color'] : 
                          $this->config['row_odd_text_color'];
                
                $output .= '<tr style="';
                $output .= 'background-color: ' . htmlspecialchars($bgColor) . '; ';
                $output .= 'color: ' . htmlspecialchars($textColor) . ';" ';
                $output .= 'onmouseover="this.style.backgroundColor=\'' . htmlspecialchars($this->config['row_hover_color']) . '\'" ';
                $output .= 'onmouseout="this.style.backgroundColor=\'' . htmlspecialchars($bgColor) . '\'">';
                
                foreach (array_keys($this->headers) as $key) {
                    $value = isset($row[$key]) ? $row[$key] : '';
                    
                    $output .= '<td style="';
                    $output .= 'padding: ' . htmlspecialchars($this->config['cell_padding']) . '; ';
                    
                    // Spaltenspezifische Ausrichtung verwenden, falls vorhanden
                    $alignment = isset($this->columnAlignments[$key]) ? 
                                $this->columnAlignments[$key] : 
                                $this->config['cell_text_align'];
                    $output .= 'text-align: ' . htmlspecialchars($alignment) . '; ';
                    
                    $output .= 'font-style: ' . htmlspecialchars($this->config['cell_font_style']) . '; ';
                    $output .= 'font-weight: ' . htmlspecialchars($this->config['cell_font_weight']) . ';">';
                    
                    // Prüfen, ob der Zellinhalt als HTML gerendert werden soll
                    if (isset($this->config['render_html']) && $this->config['render_html'] === true) {
                        $output .= $value; // HTML direkt einfügen ohne escaping
                    } else {
                        $output .= htmlspecialchars($value); // HTML escapen
                    }
                    
                    $output .= '</td>';
                }
                
                $output .= '</tr>';
            }
        }
        
        $output .= '</tbody>';
        return $output;
    }
    
    // Rendert die Paginierungsanzeige ohne Links
    private function renderPagination($totalPages) {
        $output = '<div class="php-table-pagination" style="margin:10px 0;text-align:center;">';
        
        // Nur die aktuelle Seite und Gesamtseitenzahl anzeigen
        $output .= '<span style="margin:0 5px;padding:5px;">Seite ' . $this->currentPage . ' von ' . $totalPages . '</span>';
        
        $output .= '</div>';
        return $output;
    }
    
    // Hilfsmethoden für die Konfiguration
    public function enablePagination($rowsPerPage = 10) {
        $this->config['pagination_enabled'] = true;
        $this->config['rows_per_page'] = $rowsPerPage;
        return $this;
    }
    
    public function disablePagination() {
        $this->config['pagination_enabled'] = false;
        return $this;
    }
    
    public function enableSorting() {
        $this->config['sorting_enabled'] = true;
        return $this;
    }
    
    public function disableSorting() {
        $this->config['sorting_enabled'] = false;
        return $this;
    }
    
    public function setDefaultSort($column, $direction = 'asc') {
        $this->config['default_sort_column'] = $column;
        $this->config['default_sort_direction'] = $direction;
        $this->sortColumn = $column;
        $this->sortDirection = $direction;
        return $this;
    }
    
    public function setAlternateRowColors($evenColor, $oddColor) {
        $this->config['row_alternate_coloring'] = true;
        $this->config['row_even_bg_color'] = $evenColor;
        $this->config['row_odd_bg_color'] = $oddColor;
        return $this;
    }
    
    public function setAlternateTextColors($evenColor, $oddColor) {
        $this->config['row_even_text_color'] = $evenColor;
        $this->config['row_odd_text_color'] = $oddColor;
        return $this;
    }
    
    public function setBorderColor($color) {
        $this->config['border_color'] = $color;
        return $this;
    }
    
    public function setCellAlignment($alignment) {
        if (!in_array($alignment, ['left', 'center', 'right'])) {
            throw new InvalidArgumentException('Ausrichtung muss links, zentriert oder rechts sein');
        }
        $this->config['cell_text_align'] = $alignment;
        return $this;
    }
    
    public function setCellFontStyle($style) {
        if (!in_array($style, ['normal', 'italic', 'oblique'])) {
            throw new InvalidArgumentException('Schriftstil muss normal, kursiv oder schräg sein');
        }
        $this->config['cell_font_style'] = $style;
        return $this;
    }
    
    public function setCellFontWeight($weight) {
        if (!in_array($weight, ['normal', 'bold', 'lighter', 'bolder']) && 
            !in_array((int) $weight, range(100, 900, 100))) {
            throw new InvalidArgumentException('Schriftgewicht muss normal, fett, leichter, fetter oder ein Vielfaches von 100 zwischen 100 und 900 sein');
        }
        $this->config['cell_font_weight'] = $weight;
        return $this;
    }
    
    public function setTextColor($color) {
        $this->config['row_text_color'] = $color;
        $this->config['row_even_text_color'] = $color;
        $this->config['row_odd_text_color'] = $color;
        return $this;
    }
    
    // Setzt die Ausrichtung für eine bestimmte Spalte
    public function setColumnAlignment($column, $alignment) {
        if (!in_array($alignment, ['left', 'center', 'right'])) {
            throw new InvalidArgumentException('Ausrichtung muss links, zentriert oder rechts sein');
        }
        
        if (!isset($this->headers[$column]) && !in_array($column, array_keys($this->headers))) {
            throw new InvalidArgumentException('Spalte "' . $column . '" existiert nicht');
        }
        
        $this->columnAlignments[$column] = $alignment;
        return $this;
    }
}
?>