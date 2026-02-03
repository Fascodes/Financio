<?php

require_once 'src/utility/DatabaseUtility.php';

/**
 * Klasa bazowa dla wszystkich repozytoriów
 * Zawiera wspólną logikę dostępu do bazy danych
 */
abstract class BaseRepository {
    protected $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

    /**
     * Pobierz pierwszą grupę użytkownika (fallback gdy nie podano group_id)
     * @param int $userId
     * @return int|null
     */
    protected function getUserFirstGroup($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT g.id FROM groups g
             JOIN group_members gm ON g.id = gm.group_id
             WHERE gm.user_id = ? AND g.is_active = TRUE
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }

    /**
     * Sprawdź czy użytkownik należy do grupy
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    public function userBelongsToGroup($userId, $groupId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?"
        );
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Sprawdź czy użytkownik jest właścicielem grupy
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    public function isGroupOwner($userId, $groupId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM groups WHERE id = ? AND owner_id = ?"
        );
        $stmt->execute([$groupId, $userId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Pobierz ID grupy - z parametru lub pierwszą grupę użytkownika
     * @param int $userId
     * @param int|null $groupId
     * @return int|null
     */
    protected function resolveGroupId($userId, $groupId = null) {
        if (!empty($groupId)) {
            return (int)$groupId;
        }
        return $this->getUserFirstGroup($userId);
    }
}
