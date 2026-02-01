<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/ReportsRepository.php';


class ReportsController extends AppController {

    private $repository;

    public function __construct() {
        $this->repository = new ReportsRepository();
    }

    /**
     * Wyświetl widok raportów
     */
    public function reports() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        include 'public/views/reports.html';
    }

    /**
     * API: Pobierz statystyki podsumowujące
     */
    public function getSummaryStats() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $stats = $this->repository->getSummaryStats($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    /**
     * API: Dane do wykresu Monthly Comparison by Member
     */
    public function getMonthlyByMember() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $data = $this->repository->getMonthlyByMember($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * API: Dane do wykresu Spending Trends by Category
     */
    public function getSpendingTrends() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $data = $this->repository->getSpendingTrendsByCategory($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * API: Dane do wykresu Category Distribution
     */
    public function getCategoryDistribution() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $data = $this->repository->getCategoryDistribution($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * API: Dane do wykresu Member Contributions
     */
    public function getMemberContributions() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $data = $this->repository->getMemberContributions($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}