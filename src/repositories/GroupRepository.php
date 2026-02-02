<?php

require_once 'src/utility/DatabaseUtility.php';

class GroupRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

    /**
     * Pobierz wszystkie grupy użytkownika z dodatkowymi informacjami
     */
    public function getUserGroups($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT g.id, g.name, g.description, g.budget, gm.role,
                        (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count
                 FROM groups g
                 JOIN group_members gm ON g.id = gm.group_id
                 WHERE gm.user_id = ? AND g.is_active = TRUE
                 ORDER BY g.name"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobierz aktywną grupę użytkownika (z sesji lub pierwszą dostępną)
     */
    public function getActiveGroup($userId, $sessionGroupId = null) {
        $groups = $this->getUserGroups($userId);
        
        if (empty($groups)) {
            return null;
        }

        // Jeśli podano group_id w sesji, sprawdź czy użytkownik ma do niej dostęp
        if ($sessionGroupId) {
            foreach ($groups as $group) {
                if ($group['id'] == $sessionGroupId) {
                    return $group;
                }
            }
        }

        // Zwróć pierwszą grupę
        return $groups[0];
    }

    /**
     * Sprawdź czy użytkownik należy do grupy
     */
    public function userBelongsToGroup($userId, $groupId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?"
        );
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Utwórz nową grupę dla użytkownika
     */
    public function createGroup($userId, $name, $description = '', $budget = 0) {
        try {
            $this->pdo->beginTransaction();

            // Utwórz grupę
            $stmt = $this->pdo->prepare(
                "INSERT INTO groups (name, owner_id, description, budget) 
                 VALUES (?, ?, ?, ?) RETURNING id"
            );
            $stmt->execute([$name, $userId, $description, $budget]);
            $groupId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            // Dodaj użytkownika jako właściciela
            $stmt = $this->pdo->prepare(
                "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'owner')"
            );
            $stmt->execute([$groupId, $userId]);

            $this->pdo->commit();

            return ['success' => true, 'group_id' => $groupId];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Opuść grupę (usuń członkostwo, zachowaj transakcje)
     */
    public function leaveGroup($userId, $groupId) {
        try {
            // Sprawdź czy użytkownik jest właścicielem
            $stmt = $this->pdo->prepare(
                "SELECT role FROM group_members WHERE user_id = ? AND group_id = ?"
            );
            $stmt->execute([$userId, $groupId]);
            $membership = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$membership) {
                return ['success' => false, 'error' => 'You are not a member of this group'];
            }

            if ($membership['role'] === 'owner') {
                return ['success' => false, 'error' => 'Owners cannot leave their group. Transfer ownership first or delete the group.'];
            }

            // Usuń członkostwo (transakcje pozostają)
            $stmt = $this->pdo->prepare(
                "DELETE FROM group_members WHERE user_id = ? AND group_id = ?"
            );
            $stmt->execute([$userId, $groupId]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
