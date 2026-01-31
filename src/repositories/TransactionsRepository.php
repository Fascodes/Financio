<?php

require_once 'src/utility/DatabaseUtility.php';

class TransactionsRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

    /**
     * Pobierz transakcje z filtrami i paginacją
     * @param int $userId - ID zalogowanego użytkownika
     * @param int|null $groupId - ID grupy (opcjonalne)
     * @param array $filters - ['search' => string, 'category_id' => int, 'user_id' => int]
     * @param int $page - numer strony (od 1)
     * @param int $limit - ilość na stronę
     * @return array ['transactions' => [], 'total' => int, 'pages' => int]
     */
    public function getTransactions($userId, $groupId = null, $filters = [], $page = 1, $limit = 10) {
        try {
            // Jeśli groupId nie podany, użyj pierwszej grupy użytkownika
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) {
                    return ['transactions' => [], 'total' => 0, 'pages' => 0];
                }
            }

            // Buduj zapytanie z filtrami
            $whereConditions = ["t.group_id = ?"];
            $params = [$groupId];

            // Filtr wyszukiwania
            if (!empty($filters['search'])) {
                $whereConditions[] = "(t.name ILIKE ? OR c.name ILIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Filtr kategorii
            if (!empty($filters['category_id'])) {
                $whereConditions[] = "t.category_id = ?";
                $params[] = $filters['category_id'];
            }

            // Filtr użytkownika
            if (!empty($filters['user_id'])) {
                $whereConditions[] = "t.user_id = ?";
                $params[] = $filters['user_id'];
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Policz całkowitą liczbę wyników
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) as total 
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE $whereClause"
            );
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Oblicz paginację
            $pages = ceil($total / $limit);
            $offset = ($page - 1) * $limit;

            // Pobierz transakcje
            $stmt = $this->pdo->prepare(
                "SELECT t.id, t.name, t.amount, t.date, 
                        c.name as category, c.id as category_id,
                        u.username as user_name, u.id as user_id
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 LEFT JOIN users u ON t.user_id = u.id
                 WHERE $whereClause
                 ORDER BY t.date DESC, t.id DESC
                 LIMIT ? OFFSET ?"
            );
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Konwertuj amount na float
            foreach ($transactions as &$row) {
                $row['amount'] = (float)$row['amount'];
            }

            return [
                'transactions' => $transactions,
                'total' => $total,
                'pages' => $pages,
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            return ['transactions' => [], 'total' => 0, 'pages' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Pobierz wszystkie kategorie
     */
    public function getCategories() {
        try {
            $stmt = $this->pdo->query("SELECT id, name FROM categories ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz członków grupy (do filtra)
     */
    public function getGroupUsers($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return [];
            }

            $stmt = $this->pdo->prepare(
                "SELECT u.id, u.username as name
                 FROM group_members gm
                 JOIN users u ON gm.user_id = u.id
                 WHERE gm.group_id = ?
                 ORDER BY u.username"
            );
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz pierwszą grupę użytkownika
     */
    private function getUserFirstGroup($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT g.id FROM groups g
             JOIN group_members gm ON g.id = gm.group_id
             WHERE gm.user_id = ?
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }

    /**
     * Dodaj nową transakcję
     * @param int $userId - ID użytkownika dodającego
     * @param array $data - ['name', 'amount', 'category_id', 'date', 'group_id' (opcjonalne)]
     * @return array ['success' => bool, 'id' => int|null, 'error' => string|null]
     */
    public function addTransaction($userId, $data) {
        try {
            // Pobierz groupId
            $groupId = !empty($data['group_id']) ? (int)$data['group_id'] : $this->getUserFirstGroup($userId);
            if (!$groupId) {
                return ['success' => false, 'error' => 'No group found'];
            }

            // Walidacja wymaganych pól
            if (empty($data['name']) || !isset($data['amount']) || empty($data['category_id']) || empty($data['date'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Walidacja amount
            $amount = (float)$data['amount'];
            if ($amount <= 0) {
                return ['success' => false, 'error' => 'Amount must be greater than 0'];
            }

            // Walidacja kategorii
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([(int)$data['category_id']]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'error' => 'Invalid category'];
            }

            // Wstaw transakcję
            $stmt = $this->pdo->prepare(
                "INSERT INTO transactions (group_id, user_id, category_id, name, amount, date)
                 VALUES (?, ?, ?, ?, ?, ?)
                 RETURNING id"
            );
            $stmt->execute([
                $groupId,
                $userId,
                (int)$data['category_id'],
                trim($data['name']),
                $amount,
                $data['date']
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['success' => true, 'id' => (int)$result['id']];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
