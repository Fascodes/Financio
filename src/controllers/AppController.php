<?php


class AppController { 


    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Sprawdź czy użytkownik jest zalogowany
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Pobierz ID zalogowanego użytkownika
     * @return int|null
     */
    protected function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Wymagaj zalogowania - przekieruj do logowania jeśli nie zalogowany
     * Używane przy renderowaniu widoków
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Wymagaj zalogowania dla API - zwróć 401 jeśli nie zalogowany
     * @return bool true jeśli zalogowany, false jeśli zwrócono błąd
     */
    protected function requireApiAuth(): bool
    {
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return false;
        }
        return true;
    }

    /**
     * Zwróć odpowiedź JSON
     * @param mixed $data
     * @param int $statusCode
     */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Pobierz dane z JSON body żądania
     * @return array|null
     */
    protected function getJsonInput(): ?array
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * Pobierz aktywną grupę z sesji lub z parametru
     */
    protected function getActiveGroupId($paramGroupId = null) {
        if (!empty($paramGroupId)) {
            return (int)$paramGroupId;
        }
        return isset($_SESSION['active_group_id']) ? (int)$_SESSION['active_group_id'] : null;
    }
    
    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";
                 
        if(file_exists($templatePath)){
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }
    
}