<?php

require_once 'src/utility/DatabaseUtility.php';

class DashboardRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

    /**
     * Pobierz miesięczne trendy wydatków dla zalogowanego użytkownika
     */
    public function getMonthlyTrendData($userId) {
        try{
            $stmt = $this->pdo->prepare(
                "SELECT TO_CHAR(date, 'YYYY-MM') as month, SUM(amount) as total 
                FROM transactions 
                WHERE user_id = ? 
                GROUP BY TO_CHAR(date, 'YYYY-MM')
                ORDER BY month DESC
                LIMIT 12"
            );
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Konwertuj total na float
            foreach ($results as &$row) {
                $row['total'] = (float)$row['total'];
            }
            
            return $results;
        }
        catch(PDOException $e){
            return [];
        }
    }

    /**
     * Pobierz wydatki po kategoriach dla zalogowanego użytkownika
     */
    public function getCategorySpendingData($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT c.name as category, SUM(t.amount) as total 
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE t.user_id = ?
                 GROUP BY c.name, c.id
                 ORDER BY total DESC"
            );
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Konwertuj total na float
            foreach ($results as &$row) {
                $row['total'] = (float)$row['total'];
            }
            
            return $results;
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz ostatnie transakcje użytkownika
     */
    public function getRecentTransactions($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT t.id, t.name as description, t.amount, t.date, c.name as category
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE t.user_id = ?
                 ORDER BY t.date DESC
                 LIMIT 10"
            );
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Konwertuj amount na float
            foreach ($results as &$row) {
                $row['amount'] = (float)$row['amount'];
            }
            
            return $results;
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz członków grupy użytkownika z saldem
     * Saldo: dodatnie = wpłacił więcej niż mu przypadało, ujemne = nie wpłacił dość
     */
    public function getGroupMembers($userId, $groupId = null) {
        try {
            // Jeśli groupId nie podany, użyj pierwszej grupy użytkownika
            if (!$groupId) {
                $stmt = $this->pdo->prepare(
                    "SELECT g.id FROM groups g
                     JOIN group_members gm ON g.id = gm.group_id
                     WHERE gm.user_id = ?
                     LIMIT 1"
                );
                $stmt->execute([$userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $groupId = $result['id'] ?? 1;
            }

            $stmt = $this->pdo->prepare(
                "SELECT u.id, u.username as name, u.email, 
                        COALESCE(calculate_user_balance_in_group(u.id, ?), 0) as balance
                 FROM group_members gm
                 JOIN users u ON gm.user_id = u.id
                 WHERE gm.group_id = ?
                 ORDER BY u.username"
            );
            $stmt->execute([$groupId, $groupId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Konwertuj balance na float
            foreach ($results as &$row) {
                $row['balance'] = (float)$row['balance'];
            }
            
            return $results;
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz dane podsumowania budżetu grupy (Budżet, Wydatki, Balans)
     */
    public function getBudgetSummary($userId, $groupId = null) {
        try {
            // Jeśli groupId nie podany, użyj pierwszej grupy użytkownika
            if (!$groupId) {
                $stmt = $this->pdo->prepare(
                    "SELECT g.id FROM groups g
                     JOIN group_members gm ON g.id = gm.group_id
                     WHERE gm.user_id = ?
                     LIMIT 1"
                );
                $stmt->execute([$userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $groupId = $result['id'] ?? null;
                
                if (!$groupId) {
                    return [
                        'budget' => 0.0,
                        'spending' => 0.0,
                        'balance' => 0.0,
                        'percentage' => 0.0
                    ];
                }
            }

            // Pobierz budżet grupy
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(budget, 0) as budget FROM groups WHERE id = ?"
            );
            $stmt->execute([$groupId]);
            $budgetResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $budget = (float)($budgetResult['budget'] ?? 0);

            // Pobierz sumę wydatków w grupie (bieżący miesiąc)
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(amount), 0) as spending 
                 FROM transactions 
                 WHERE group_id = ? 
                 AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)
                 AND EXTRACT(YEAR FROM date) = EXTRACT(YEAR FROM CURRENT_DATE)"
            );
            $stmt->execute([$groupId]);
            $spendingResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $spending = (float)($spendingResult['spending'] ?? 0);

            // Oblicz balans i procent
            $balance = $budget - $spending;
            $percentage = $budget > 0 ? round(($spending / $budget) * 100, 1) : 0;

            return [
                'budget' => $budget,
                'spending' => $spending,
                'balance' => $balance,
                'percentage' => $percentage
            ];
        } catch(PDOException $e) {
            return [
                'budget' => 0.0,
                'spending' => 0.0,
                'balance' => 0.0,
                'percentage' => 0.0
            ];
        }
    }
}