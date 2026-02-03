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
        $this->requireLogin();
        include 'public/views/reports.php';
    }

    /**
     * API: Pobierz statystyki podsumowujące
     */
    public function getSummaryStats() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $stats = $this->repository->getSummaryStats($userId, $groupId);

        $this->jsonResponse($stats);
    }

    /**
     * API: Dane do wykresu Monthly Comparison by Member
     */
    public function getMonthlyByMember() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->repository->getMonthlyByMember($userId, $groupId);

        $this->jsonResponse($data);
    }

    /**
     * API: Dane do wykresu Spending Trends by Category
     */
    public function getSpendingTrends() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->repository->getSpendingTrendsByCategory($userId, $groupId);

        $this->jsonResponse($data);
    }

    /**
     * API: Dane do wykresu Category Distribution
     */
    public function getCategoryDistribution() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->repository->getCategoryDistribution($userId, $groupId);

        $this->jsonResponse($data);
    }

    /**
     * API: Dane do wykresu Member Contributions
     */
    public function getMemberContributions() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->repository->getMemberContributions($userId, $groupId);

        $this->jsonResponse($data);
    }
}