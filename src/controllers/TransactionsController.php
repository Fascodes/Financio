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
        $this->requireLogin();
        include 'public/views/transactions.php';
    }

    /**
     * API: Pobierz listę transakcji z filtrami i paginacją
     */
    public function getTransactions() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();

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

        $this->jsonResponse($result);
    }

    /**
     * API: Pobierz listę kategorii
     */
    public function getCategories() {
        if (!$this->requireApiAuth()) return;

        $categories = $this->repository->getCategories();
        $this->jsonResponse(['categories' => $categories]);
    }

    /**
     * API: Pobierz listę użytkowników w grupie (do filtra)
     */
    public function getGroupUsers() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $users = $this->repository->getGroupUsers($userId, $groupId);

        $this->jsonResponse(['users' => $users]);
    }

    /**
     * API: Dodaj nową transakcję (POST)
     */
    public function addTransaction() {
        if (!$this->requireApiAuth()) return;

        if (!$this->isPost()) {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $userId = $this->getUserId();
        $input = $this->getJsonInput();
        
        if (!$input) {
            $this->jsonResponse(['error' => 'Invalid JSON'], 400);
            return;
        }

        $result = $this->repository->addTransaction($userId, $input);

        if ($result['success']) {
            $this->jsonResponse(['success' => true, 'id' => $result['id']], 201);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * API: Pobierz pojedynczą transakcję
     */
    public function getTransaction() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $transactionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$transactionId) {
            $this->jsonResponse(['error' => 'Transaction ID required'], 400);
            return;
        }

        $transaction = $this->repository->getTransaction($transactionId, $userId);

        if ($transaction) {
            $this->jsonResponse(['success' => true, 'transaction' => $transaction]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Transaction not found'], 404);
        }
    }

    /**
     * API: Aktualizuj transakcję (PUT)
     */
    public function updateTransaction() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->jsonResponse(['error' => 'Invalid JSON or missing ID'], 400);
            return;
        }

        $result = $this->repository->updateTransaction((int)$input['id'], $userId, $input);

        if ($result['success']) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * API: Usuń transakcję (DELETE)
     */
    public function deleteTransaction() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();
        $transactionId = !empty($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        if (!$transactionId) {
            $this->jsonResponse(['error' => 'Transaction ID required'], 400);
            return;
        }

        $result = $this->repository->deleteTransaction($transactionId, $userId);

        if ($result['success']) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }
}