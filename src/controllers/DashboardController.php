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

        include 'public/views/dashboard.html';
    }

    public function getChartData() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $data = $this->dashboardRepository->getMonthlyTrendData();

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

        $data = $this->dashboardRepository->getCategorySpendingData();

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

        $data = $this->dashboardRepository->getRecentTransactions();

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

        $data = $this->dashboardRepository->getGroupMembers();

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}