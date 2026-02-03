<?php

require_once 'src/repositories/BaseRepository.php';

/**
 * Repozytorium dla preferencji użytkownika (relacja 1:1 z users)
 */
class UserPreferencesRepository extends BaseRepository {

    /**
     * Pobierz domyślną grupę użytkownika
     * @param int $userId
     * @return int|null ID grupy lub null jeśli nie ustawiono
     */
    public function getDefaultGroup($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT default_group_id FROM user_preferences WHERE user_id = ?"
            );
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['default_group_id'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Ustaw domyślną grupę użytkownika (INSERT lub UPDATE - UPSERT)
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    public function setDefaultGroup($userId, $groupId) {
        try {
            // PostgreSQL UPSERT - INSERT ... ON CONFLICT UPDATE
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_preferences (user_id, default_group_id, updated_at)
                 VALUES (?, ?, CURRENT_TIMESTAMP)
                 ON CONFLICT (user_id) 
                 DO UPDATE SET default_group_id = EXCLUDED.default_group_id, 
                               updated_at = CURRENT_TIMESTAMP"
            );
            $stmt->execute([$userId, $groupId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Pobierz wszystkie preferencje użytkownika
     * @param int $userId
     * @return array|null
     */
    public function getPreferences($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM user_preferences WHERE user_id = ?"
            );
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
