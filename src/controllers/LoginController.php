<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/UserRepository.php';

class LoginController extends AppController {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    /**
     * Wyświetl formularz logowania
     */
    public function showLogin() {
        // Jeśli użytkownik już zalogowany, przekieruj na dashboard
        if ($this->isUserLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        include 'public/views/login.html';
    }

    /**
     * Obsługa logowania via POST/JSON
     */
    public function login() {
        // Odbierz JSON
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email i hasło są wymagane'
            ]);
            return;
        }

        $result = $this->userRepository->login($data['email'], $data['password']);

        if ($result['success']) {
            // Utwórz sesję
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['email'] = $result['user']['email'];
            $_SESSION['logged_in'] = true;

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Logowanie powiodło się',
                'user' => $result['user']
            ]);
        } else {
            http_response_code(401);
            echo json_encode($result);
        }
    }

    /**
     * Obsługa rejestracji via POST/JSON
     */
    public function register() {
        // Odbierz JSON
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email, nazwa użytkownika i hasło są wymagane'
            ]);
            return;
        }

        // Walidacja
        if (strlen($data['password']) < 6) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Hasło musi mieć co najmniej 6 znaków'
            ]);
            return;
        }

        $result = $this->userRepository->register($data['email'], $data['username'], $data['password']);

        if ($result['success']) {
            // Automatyczne logowanie po rejestracji
            $loginResult = $this->userRepository->login($data['email'], $data['password']);
            
            if ($loginResult['success']) {
                $_SESSION['user_id'] = $loginResult['user']['id'];
                $_SESSION['username'] = $loginResult['user']['username'];
                $_SESSION['email'] = $loginResult['user']['email'];
                $_SESSION['logged_in'] = true;
            }

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Rejestracja powiodła się',
                'user' => $loginResult['user'] ?? null
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    /**
     * Wylogowanie
     */
    public function logout() {
        session_destroy();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Wylogowano pomyślnie'
        ]);
    }

    /**
     * Pobierz dane aktualnie zalogowanego użytkownika
     */
    public function getCurrentUser() {
        if (!$this->isUserLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Nie zalogowany'
            ]);
            return;
        }

        $user = $this->userRepository->getUserById($_SESSION['user_id']);
        $groups = $this->userRepository->getUserGroups($_SESSION['user_id']);

        echo json_encode([
            'success' => true,
            'user' => $user,
            'groups' => $groups
        ]);
    }

    /**
     * Sprawdzenie czy użytkownik jest zalogowany
     */
    protected function isUserLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}
