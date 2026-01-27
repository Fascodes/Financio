<?php

require_once 'src/utility/DatabaseUtility.php';

class DashboardRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

   
    public function getMonthlyTrendData() {
        $stmt = $this->pdo->prepare(
            "SELECT month, SUM(amount) as total 
             FROM transactions 
             GROUP BY month 
             ORDER BY month"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorySpendingData() {
        $stmt = $this->pdo->prepare(
            "SELECT category, SUM(amount) as total 
             FROM transactions 
             GROUP BY category 
             ORDER BY total DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
