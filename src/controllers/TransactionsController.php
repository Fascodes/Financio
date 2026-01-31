<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/TransactionsRepository.php';


class TransactionsController extends AppController {

    private $repository;

    public function __construct() {
        $this->repository = new TransactionsRepository();
    }

    /**
     * Wyświetl widok transakcji
     */
    public function transactions() {
        // Sprawdź czy użytkownik jest zalogowany
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        include 'public/views/transactions.html';
    }

    /**
     * API: Pobierz listę transakcji z filtrami i paginacją
     */
    public function getTransactions() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Pobierz parametry z query string
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;

        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = (int)$_GET['category_id'];
        }
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int)$_GET['user_id'];
        }

        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $result = $this->repository->getTransactions($userId, $groupId, $filters, $page, $limit);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * API: Pobierz listę kategorii
     */
    public function getCategories() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $categories = $this->repository->getCategories();

        header('Content-Type: application/json');
        echo json_encode(['categories' => $categories]);
    }

    /**
     * API: Pobierz listę użytkowników w grupie (do filtra)
     */
    public function getGroupUsers() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $users = $this->repository->getGroupUsers($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode(['users' => $users]);
    }
}