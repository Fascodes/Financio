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
        include 'public/views/transactions.php';
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

        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);

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
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);

        $users = $this->repository->getGroupUsers($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode(['users' => $users]);
    }

    /**
     * API: Dodaj nową transakcję (POST)
     */
    public function addTransaction() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Sprawdź metodę HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Pobierz dane z body
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $result = $this->repository->addTransaction($userId, $input);

        header('Content-Type: application/json');

        if ($result['success']) {
            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $result['id']]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }

    /**
     * API: Pobierz pojedynczą transakcję
     */
    public function getTransaction() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $transactionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$transactionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Transaction ID required']);
            return;
        }

        $transaction = $this->repository->getTransaction($transactionId, $userId);

        header('Content-Type: application/json');

        if ($transaction) {
            echo json_encode(['success' => true, 'transaction' => $transaction]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Transaction not found']);
        }
    }

    /**
     * API: Aktualizuj transakcję (PUT)
     */
    public function updateTransaction() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Pobierz dane z body
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON or missing ID']);
            return;
        }

        $result = $this->repository->updateTransaction((int)$input['id'], $userId, $input);

        header('Content-Type: application/json');

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }

    /**
     * API: Usuń transakcję (DELETE)
     */
    public function deleteTransaction() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Pobierz dane z body lub query string
        $input = json_decode(file_get_contents('php://input'), true);
        $transactionId = !empty($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        if (!$transactionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Transaction ID required']);
            return;
        }

        $result = $this->repository->deleteTransaction($transactionId, $userId);

        header('Content-Type: application/json');

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }
}