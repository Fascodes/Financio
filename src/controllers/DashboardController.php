<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/DashboardRepository.php';

class DashboardController extends AppController {
    private $dashboardRepository;

    public function __construct() {
        $this->dashboardRepository = new DashboardRepository();
    }

    public function dashboard() {
        include 'public/views/dashboard.html';
    }

    public function getChartData() {
        $data = $this->dashboardRepository->getMonthlyTrendData();

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getCategoryData() {
        $data = $this->dashboardRepository->getCategorySpendingData();

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getRecentTransactions() {
        $data = $this->dashboardRepository->getRecentTransactions();

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getGroupMembers() {
        $data = $this->dashboardRepository->getGroupMembers();

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}