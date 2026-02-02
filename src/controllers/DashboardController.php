<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/DashboardRepository.php';

class DashboardController extends AppController {
    private $dashboardRepository;

    public function __construct() {
        $this->dashboardRepository = new DashboardRepository();
    }

    public function dashboard() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit();
        }

        include 'public/views/dashboard.php';
    }

    public function getChartData() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getMonthlyTrendData($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getCategoryData() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getCategorySpendingData($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getRecentTransactions() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getRecentTransactions($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getGroupMembers() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getGroupMembers($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getBudgetSummary() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getBudgetSummary($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}