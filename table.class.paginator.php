<?php
/**
 * Tabellen-Paginierungsklasse
 * Version: 1.0
 */
class TablePaginator {
    private $data;
    private $currentPage;
    private $rowsPerPage;
    private $totalPages;
    
    public function __construct($data, $currentPage = 1, $rowsPerPage = 10) {
        $this->data = $data;
        $this->rowsPerPage = $rowsPerPage;
        $this->currentPage = max(1, min($currentPage, $this->calculateTotalPages()));
    }
    
    public function paginate() {
        if ($this->rowsPerPage <= 0) {
            return $this->data;
        }
        
        $offset = ($this->currentPage - 1) * $this->rowsPerPage;
        return array_slice($this->data, $offset, $this->rowsPerPage);
    }
    
    public function calculateTotalPages() {
        if ($this->rowsPerPage <= 0) {
            return 1;
        }
        
        $this->totalPages = ceil(count($this->data) / $this->rowsPerPage);
        return $this->totalPages;
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }
    
    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }
    
    public function getNextPage() {
        return min($this->currentPage + 1, $this->totalPages);
    }
    
    public function getPreviousPage() {
        return max($this->currentPage - 1, 1);
    }
}
?>