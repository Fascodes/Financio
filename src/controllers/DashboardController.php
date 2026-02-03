<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/DashboardRepository.php';

class DashboardController extends AppController {
    private $dashboardRepository;

    public function __construct() {
        $this->dashboardRepository = new DashboardRepository();
    }

    public function dashboard() {
        $this->requireLogin();
        include 'public/views/dashboard.php';
    }

    public function getChartData() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getMonthlyTrendData($userId, $groupId);

        $this->jsonResponse($data);
    }

    public function getCategoryData() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getCategorySpendingData($userId, $groupId);

        $this->jsonResponse($data);
    }

    public function getRecentTransactions() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getRecentTransactions($userId, $groupId);

        $this->jsonResponse($data);
    }

    public function getGroupMembers() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getGroupMembers($userId, $groupId);

        $this->jsonResponse($data);
    }

    public function getBudgetSummary() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->dashboardRepository->getBudgetSummary($userId, $groupId);

        $this->jsonResponse($data);
    }
}