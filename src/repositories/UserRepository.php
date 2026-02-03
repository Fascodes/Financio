<?php

require_once 'src/repositories/BaseRepository.php';
require_once 'src/utility/SecurityUtility.php';

class UserRepository extends BaseRepository {

    /**
     * Rejestracja nowego użytkownika
     * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
     */
    public function register($email, $username, $password) {
        try {
            // Sprawdzenie czy email/username już istnieje
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email lub nazwa użytkownika już istnieje'
                ];
            }

            // Hash hasła
            $passwordHash = SecurityUtility::hashPassword($password);

            // Wstawienie użytkownika
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (email, username, password_hash, is_active) 
                 VALUES (?, ?, ?, true)"
            );
            $stmt->execute([$email, $username, $passwordHash]);
            $userId = $this->pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Rejestracja powiodła się',
                'user_id' => (int)$userId
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Błąd bazy danych: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Logowanie użytkownika
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login($email, $password) {
        try {
            // Pobranie użytkownika po email
            $stmt = $this->pdo->prepare(
                "SELECT id, username, email, password_hash, is_active 
                 FROM users 
                 WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Nieprawidłowy email lub hasło'
                ];
            }

            // Sprawdzenie czy konto aktywne
            if (!$user['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Konto jest nieaktywne'
                ];
            }

            // Weryfikacja hasła
            if (!SecurityUtility::verifyPassword($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Nieprawidłowy email lub hasło'
                ];
            }

            // Usunięcie hasza z odpowiedzi
            unset($user['password_hash']);

            return [
                'success' => true,
                'message' => 'Logowanie powiodło się',
                'user' => $user
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Błąd bazy danych: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Pobierz użytkownika po ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, username, email, created_at, is_active 
                 FROM users 
                 WHERE id = ?"
            );
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobierz grupy użytkownika
     */
    public function getUserGroups($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT g.id, g.name, g.description, gm.role, g.created_at
                 FROM groups g
                 JOIN group_members gm ON g.id = gm.group_id
                 WHERE gm.user_id = ? AND g.is_active = true
                 ORDER BY g.created_at DESC"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
