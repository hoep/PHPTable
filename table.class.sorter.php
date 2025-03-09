<?php
/**
 * Tabellen-Sortierklasse
 * Version: 1.3
 */
class TableSorter {
    private $data;
    private $sortColumn;
    private $sortDirection;
    
    public function __construct($data, $sortColumn = null, $sortDirection = null) {
        $this->data = $data;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
    }
    
    public function sort() {
        if ($this->sortColumn === null) {
            return $this->data;
        }
        
        $column = $this->sortColumn;
        $direction = $this->sortDirection;
        
        usort($this->data, function($a, $b) use ($column, $direction) {
            if (!isset($a[$column]) || !isset($b[$column])) {
                return 0;
            }
            
            $valueA = $a[$column];
            $valueB = $b[$column];
            
            // HTML-Tags entfernen und Strings bereinigen
            $plainA = $this->cleanStringForSorting($valueA);
            $plainB = $this->cleanStringForSorting($valueB);
            
            // Debug-Ausgabe
            // error_log("Sortiere: '$plainA' vs '$plainB'");
            
            // Prüfen, ob es sich um Episodentitel handelt (Format: Name - SXXEXX - Titel)
            if (preg_match('/^(.+?)\s*-\s*S(\d+)E(\d+)\s*-\s*(.+)$/', $plainA, $matchesA) &&
                preg_match('/^(.+?)\s*-\s*S(\d+)E(\d+)\s*-\s*(.+)$/', $plainB, $matchesB)) {
                
                // Vergleiche zuerst nach Serienname
                $showNameA = trim($matchesA[1]);
                $showNameB = trim($matchesB[1]);
                $showCompare = strcasecmp($showNameA, $showNameB);
                
                if ($showCompare !== 0) {
                    return $direction == 'desc' ? -$showCompare : $showCompare;
                }
                
                // Gleiche Serie, vergleiche nach Staffel
                $seasonA = (int)$matchesA[2];
                $seasonB = (int)$matchesB[2];
                
                if ($seasonA !== $seasonB) {
                    $result = $seasonA <=> $seasonB;
                    return $direction == 'desc' ? -$result : $result;
                }
                
                // Gleiche Staffel, vergleiche nach Episode
                $episodeA = (int)$matchesA[3];
                $episodeB = (int)$matchesB[3];
                
                $result = $episodeA <=> $episodeB;
                return $direction == 'desc' ? -$result : $result;
            }
            
            // Wenn beide Werte Zahlen enthalten, versuche numerische Sortierung
            if ($this->containsNumbers($plainA) && $this->containsNumbers($plainB)) {
                // Extrahiere die erste Zahl aus dem String
                $numA = $this->extractNumber($plainA);
                $numB = $this->extractNumber($plainB);
                
                if ($numA !== false && $numB !== false) {
                    $comparison = $numA <=> $numB;
                    return $direction == 'desc' ? -$comparison : $comparison;
                }
            }
            
            // Standard-Stringvergleich (case-insensitive)
            $comparison = strcasecmp($plainA, $plainB);
            return $direction == 'desc' ? -$comparison : $comparison;
        });
        
        return $this->data;
    }
    
    // Bereinigt einen String für die Sortierung
    private function cleanStringForSorting($str) {
        // HTML-Tags entfernen
        $plainText = strip_tags($str);
        
        // Führende und nachfolgende Leerzeichen entfernen
        $plainText = trim($plainText);
        
        // HTML-Entities dekodieren (z.B. &amp; wird zu &)
        $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Mehrfache Leerzeichen durch ein einzelnes ersetzen
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        
        return $plainText;
    }
    
    // Prüft, ob ein String Zahlen enthält
    private function containsNumbers($str) {
        return preg_match('/\d/', $str) === 1;
    }
    
    // Extrahiert die erste Zahl aus einem String
    private function extractNumber($str) {
        if (preg_match('/(\d+(\.\d+)?)/', $str, $matches)) {
            return floatval($matches[1]);
        }
        return false;
    }
    
    public function toggleSortDirection($column) {
        if ($this->sortColumn === $column) {
            if ($this->sortDirection === 'asc') {
                $this->sortDirection = 'desc';
            } elseif ($this->sortDirection === 'desc') {
                $this->sortColumn = null;
                $this->sortDirection = null;
            } else {
                $this->sortDirection = 'asc';
            }
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        
        return ['column' => $this->sortColumn, 'direction' => $this->sortDirection];
    }
    
    public function getSortColumn() {
        return $this->sortColumn;
    }
    
    public function getSortDirection() {
        return $this->sortDirection;
    }
}
?>