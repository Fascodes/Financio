<?php

require_once 'src/repositories/BaseRepository.php';

class ReportsRepository extends BaseRepository {

    /**
     * Pobierz statystyki podsumowujące
     * @param int $userId
     * @param int|null $groupId
     * @return array [this_month, last_month, avg_month, top_category]
     */
    public function getSummaryStats($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) {
                    return $this->getEmptyStats();
                }
            }

            // This month spending
            $thisMonth = $this->getMonthSpending($groupId, 0);
            
            // Last month spending
            $lastMonth = $this->getMonthSpending($groupId, 1);
            
            // Calculate change percentage
            $change = 0;
            if ($lastMonth > 0) {
                $change = (($thisMonth - $lastMonth) / $lastMonth) * 100;
            }

            // Average monthly (last 6 months)
            $avgMonth = $this->getAverageMonthlySpending($groupId, 6);

            // Top category this month
            $topCategory = $this->getTopCategory($groupId);

            return [
                'this_month' => (float)$thisMonth,
                'last_month' => (float)$lastMonth,
                'change_percent' => round($change, 1),
                'avg_month' => (float)$avgMonth,
                'top_category' => $topCategory
            ];
        } catch (PDOException $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats() {
        return [
            'this_month' => 0,
            'last_month' => 0,
            'change_percent' => 0,
            'avg_month' => 0,
            'top_category' => ['name' => 'N/A', 'amount' => 0]
        ];
    }

    private function getMonthSpending($groupId, $monthsAgo = 0) {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(amount), 0) as total
             FROM transactions
             WHERE group_id = ?
             AND date >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '{$monthsAgo} months')
             AND date < DATE_TRUNC('month', CURRENT_DATE - INTERVAL '{$monthsAgo} months') + INTERVAL '1 month'"
        );
        $stmt->execute([$groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    private function getAverageMonthlySpending($groupId, $months) {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(AVG(monthly_total), 0) as avg_total
             FROM (
                 SELECT DATE_TRUNC('month', date) as month, SUM(amount) as monthly_total
                 FROM transactions
                 WHERE group_id = ?
                 AND date >= CURRENT_DATE - INTERVAL '{$months} months'
                 GROUP BY DATE_TRUNC('month', date)
             ) as monthly"
        );
        $stmt->execute([$groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['avg_total'];
    }

    private function getTopCategory($groupId) {
        $stmt = $this->pdo->prepare(
            "SELECT c.name, SUM(t.amount) as total
             FROM transactions t
             JOIN categories c ON t.category_id = c.id
             WHERE t.group_id = ?
             AND t.date >= DATE_TRUNC('month', CURRENT_DATE)
             GROUP BY c.name
             ORDER BY total DESC
             LIMIT 1"
        );
        $stmt->execute([$groupId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? ['name' => $result['name'], 'amount' => (float)$result['total']] : ['name' => 'N/A', 'amount' => 0];
    }

    /**
     * Dane do wykresu: Monthly Comparison by Member (grouped bar)
     * @return array ['labels' => ['Oct', 'Nov', ...], 'datasets' => [['label' => 'Emma', 'data' => [...]], ...]]
     */
    public function getMonthlyByMember($userId, $groupId = null, $months = 6) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return ['labels' => [], 'datasets' => []];
            }

            $stmt = $this->pdo->prepare(
                "SELECT 
                    TO_CHAR(t.date, 'Mon') as month_label,
                    DATE_TRUNC('month', t.date) as month_date,
                    u.username,
                    SUM(t.amount) as total
                 FROM transactions t
                 JOIN users u ON t.user_id = u.id
                 WHERE t.group_id = ?
                 AND t.date >= CURRENT_DATE - INTERVAL '{$months} months'
                 GROUP BY DATE_TRUNC('month', t.date), TO_CHAR(t.date, 'Mon'), u.username
                 ORDER BY month_date"
            );
            $stmt->execute([$groupId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->formatGroupedBarData($rows);
        } catch (PDOException $e) {
            return ['labels' => [], 'datasets' => []];
        }
    }

    private function formatGroupedBarData($rows) {
        $months = [];
        $users = [];
        $data = [];

        foreach ($rows as $row) {
            $month = $row['month_label'];
            $user = $row['username'];
            
            if (!in_array($month, $months)) {
                $months[] = $month;
            }
            if (!in_array($user, $users)) {
                $users[] = $user;
            }
            
            $data[$user][$month] = (float)$row['total'];
        }

        $colors = ['#f5a623', '#4a90d9', '#9b59b6', '#2ecc71', '#e74c3c', '#3498db'];
        $datasets = [];
        $i = 0;
        
        foreach ($users as $user) {
            $userData = [];
            foreach ($months as $month) {
                $userData[] = $data[$user][$month] ?? 0;
            }
            $datasets[] = [
                'label' => $user,
                'data' => $userData,
                'backgroundColor' => $colors[$i % count($colors)]
            ];
            $i++;
        }

        return ['labels' => $months, 'datasets' => $datasets];
    }

    /**
     * Dane do wykresu: Spending Trends by Category (line chart)
     */
    public function getSpendingTrendsByCategory($userId, $groupId = null, $months = 6) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return ['labels' => [], 'datasets' => []];
            }

            $stmt = $this->pdo->prepare(
                "SELECT 
                    TO_CHAR(t.date, 'Mon') as month_label,
                    DATE_TRUNC('month', t.date) as month_date,
                    c.name as category,
                    SUM(t.amount) as total
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE t.group_id = ?
                 AND t.date >= CURRENT_DATE - INTERVAL '{$months} months'
                 GROUP BY DATE_TRUNC('month', t.date), TO_CHAR(t.date, 'Mon'), c.name
                 ORDER BY month_date"
            );
            $stmt->execute([$groupId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->formatLineChartData($rows);
        } catch (PDOException $e) {
            return ['labels' => [], 'datasets' => []];
        }
    }

    private function formatLineChartData($rows) {
        $months = [];
        $categories = [];
        $data = [];

        foreach ($rows as $row) {
            $month = $row['month_label'];
            $category = $row['category'];
            
            if (!in_array($month, $months)) {
                $months[] = $month;
            }
            if (!in_array($category, $categories)) {
                $categories[] = $category;
            }
            
            $data[$category][$month] = (float)$row['total'];
        }

        $colors = [
            'Food' => '#2ecc71',
            'Shopping' => '#f5a623',
            'Transport' => '#4a90d9',
            'Entertainment' => '#9b59b6',
            'Others' => '#95a5a6'
        ];
        
        $datasets = [];
        foreach ($categories as $category) {
            $catData = [];
            foreach ($months as $month) {
                $catData[] = $data[$category][$month] ?? 0;
            }
            $color = $colors[$category] ?? '#' . substr(md5($category), 0, 6);
            $datasets[] = [
                'label' => $category,
                'data' => $catData,
                'borderColor' => $color,
                'backgroundColor' => $color,
                'fill' => false,
                'tension' => 0.4
            ];
        }

        return ['labels' => $months, 'datasets' => $datasets];
    }

    /**
     * Dane do wykresu: Category Distribution (pie chart)
     */
    public function getCategoryDistribution($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return ['labels' => [], 'data' => [], 'colors' => []];
            }

            $stmt = $this->pdo->prepare(
                "SELECT c.name, SUM(t.amount) as total
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE t.group_id = ?
                 GROUP BY c.name
                 ORDER BY total DESC"
            );
            $stmt->execute([$groupId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $colors = ['#2ecc71', '#f5a623', '#4a90d9', '#9b59b6', '#95a5a6', '#e74c3c'];
            
            foreach ($rows as $i => $row) {
                $labels[] = $row['name'];
                $data[] = (float)$row['total'];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'colors' => array_slice($colors, 0, count($labels))
            ];
        } catch (PDOException $e) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
    }

    /**
     * Dane do wykresu: Member Contributions (horizontal bar)
     */
    public function getMemberContributions($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return ['labels' => [], 'data' => [], 'colors' => []];
            }

            $stmt = $this->pdo->prepare(
                "SELECT u.username, SUM(t.amount) as total
                 FROM transactions t
                 JOIN users u ON t.user_id = u.id
                 WHERE t.group_id = ?
                 GROUP BY u.username
                 ORDER BY total DESC"
            );
            $stmt->execute([$groupId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $colors = ['#2ecc71', '#4a90d9', '#9b59b6', '#f5a623', '#e74c3c', '#3498db'];
            
            foreach ($rows as $row) {
                $labels[] = $row['username'];
                $data[] = (float)$row['total'];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'colors' => array_slice($colors, 0, count($labels))
            ];
        } catch (PDOException $e) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
    }
}
