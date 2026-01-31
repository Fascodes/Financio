<?php

class SecurityUtility {
    /**
     * Hash hasła za pomocą bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Weryfikuj hasło
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Sprawdzenie czy użytkownik ma dostęp do grupy
     */
    public static function canAccessGroup($userId, $groupId, $pdo) {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM group_members 
             WHERE user_id = ? AND group_id = ?"
        );
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Sprawdzenie czy użytkownik może edytować grupę (owner lub editor)
     */
    public static function canEditGroup($userId, $groupId, $pdo) {
        $stmt = $pdo->prepare(
            "SELECT role FROM group_members 
             WHERE user_id = ? AND group_id = ? AND role IN ('owner', 'editor')"
        );
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Sprawdzenie czy użytkownik jest właścicielem grupy
     */
    public static function isGroupOwner($userId, $groupId, $pdo) {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM groups WHERE id = ? AND owner_id = ?"
        );
        $stmt->execute([$groupId, $userId]);
        return $stmt->fetch() !== false;
    }
}
