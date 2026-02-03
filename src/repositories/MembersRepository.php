<?php

require_once 'src/repositories/BaseRepository.php';

class MembersRepository extends BaseRepository {

    /**
     * Pobierz statystyki członków grupy
     */
    public function getMembersStats($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) {
                    return $this->getEmptyStats();
                }
            }

            // Total members
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as total FROM group_members WHERE group_id = ?"
            );
            $stmt->execute([$groupId]);
            $totalMembers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total spent
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE group_id = ?"
            );
            $stmt->execute([$groupId]);
            $totalSpent = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Top contributor
            $stmt = $this->pdo->prepare(
                "SELECT u.username, SUM(t.amount) as total
                 FROM transactions t
                 JOIN users u ON t.user_id = u.id
                 WHERE t.group_id = ?
                 GROUP BY u.username
                 ORDER BY total DESC
                 LIMIT 1"
            );
            $stmt->execute([$groupId]);
            $topContributor = $stmt->fetch(PDO::FETCH_ASSOC);

            // Avg spending per member
            $avgSpending = $totalMembers > 0 ? $totalSpent / $totalMembers : 0;

            return [
                'total_members' => $totalMembers,
                'total_spent' => $totalSpent,
                'top_contributor' => $topContributor ? [
                    'name' => $topContributor['username'],
                    'amount' => (float)$topContributor['total']
                ] : ['name' => 'N/A', 'amount' => 0],
                'avg_spending' => round($avgSpending, 2)
            ];
        } catch (PDOException $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats() {
        return [
            'total_members' => 0,
            'total_spent' => 0,
            'top_contributor' => ['name' => 'N/A', 'amount' => 0],
            'avg_spending' => 0
        ];
    }

    /**
     * Pobierz listę członków grupy z ich statystykami
     */
    public function getMembers($userId, $groupId = null) {
        try {
            if (!$groupId) {
                $groupId = $this->getUserFirstGroup($userId);
                if (!$groupId) return ['members' => [], 'is_owner' => false];
            }

            // Sprawdź czy current user jest ownerem
            $isOwner = $this->isGroupOwner($userId, $groupId);

            // Pobierz członków z ich rolami
            $stmt = $this->pdo->prepare(
                "SELECT u.id as user_id, u.username as name, u.email, gm.role, gm.joined_at
                 FROM group_members gm
                 JOIN users u ON gm.user_id = u.id
                 WHERE gm.group_id = ?
                 ORDER BY gm.role DESC, u.username"
            );
            $stmt->execute([$groupId]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pobierz statystyki dla każdego członka
            foreach ($members as &$member) {
                $stats = $this->getMemberStats($member['user_id'], $groupId);
                $member['total_spent'] = $stats['total_spent'];
                $member['balance'] = $stats['balance'];
            }

            return [
                'members' => $members,
                'is_owner' => $isOwner
            ];
        } catch (PDOException $e) {
            return ['members' => [], 'is_owner' => false];
        }
    }

    /**
     * Pobierz statystyki pojedynczego członka
     */
    private function getMemberStats($memberId, $groupId) {
        // Total spent by member
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(amount), 0) as total
             FROM transactions
             WHERE user_id = ? AND group_id = ?"
        );
        $stmt->execute([$memberId, $groupId]);
        $totalSpent = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Calculate balance using the database function if exists, otherwise calculate
        $balance = $this->calculateBalance($memberId, $groupId);

        return [
            'total_spent' => $totalSpent,
            'balance' => $balance
        ];
    }

    /**
     * Oblicz balans członka (ile nadpłacił/ile jest winien)
     */
    private function calculateBalance($memberId, $groupId) {
        try {
            // Try to use database function first
            $stmt = $this->pdo->prepare("SELECT calculate_user_balance_in_group(?, ?) as balance");
            $stmt->execute([$memberId, $groupId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['balance'] ?? 0);
        } catch (PDOException $e) {
            // Fallback: calculate manually
            // Balance = what user paid - (total group spending / number of members)
            $stmt = $this->pdo->prepare(
                "SELECT 
                    COALESCE((SELECT SUM(amount) FROM transactions WHERE user_id = ? AND group_id = ?), 0) as user_spent,
                    COALESCE((SELECT SUM(amount) FROM transactions WHERE group_id = ?), 0) as group_total,
                    (SELECT COUNT(*) FROM group_members WHERE group_id = ?) as member_count"
            );
            $stmt->execute([$memberId, $groupId, $groupId, $groupId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userSpent = (float)$row['user_spent'];
            $groupTotal = (float)$row['group_total'];
            $memberCount = (int)$row['member_count'];
            
            $fairShare = $memberCount > 0 ? $groupTotal / $memberCount : 0;
            return round($userSpent - $fairShare, 2);
        }
    }

    /**
     * Zmień rolę członka (tylko owner może)
     */
    public function updateMemberRole($ownerId, $memberId, $groupId, $newRole) {
        try {
            // Sprawdź czy wykonujący jest ownerem
            if (!$this->isGroupOwner($ownerId, $groupId)) {
                return ['success' => false, 'error' => 'Only owner can change roles'];
            }

            // Nie można zmienić roli sobie
            if ($ownerId == $memberId) {
                return ['success' => false, 'error' => 'Cannot change own role'];
            }

            // Walidacja roli
            if (!in_array($newRole, ['owner', 'editor'])) {
                return ['success' => false, 'error' => 'Invalid role'];
            }

            $stmt = $this->pdo->prepare(
                "UPDATE group_members SET role = ? WHERE user_id = ? AND group_id = ?"
            );
            $stmt->execute([$newRole, $memberId, $groupId]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Usuń członka z grupy (tylko owner może)
     */
    public function removeMember($ownerId, $memberId, $groupId) {
        try {
            // Sprawdź czy wykonujący jest ownerem
            if (!$this->isGroupOwner($ownerId, $groupId)) {
                return ['success' => false, 'error' => 'Only owner can remove members'];
            }

            // Nie można usunąć siebie
            if ($ownerId == $memberId) {
                return ['success' => false, 'error' => 'Cannot remove yourself'];
            }

            $stmt = $this->pdo->prepare(
                "DELETE FROM group_members WHERE user_id = ? AND group_id = ?"
            );
            $stmt->execute([$memberId, $groupId]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
