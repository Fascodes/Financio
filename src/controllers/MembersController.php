<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/MembersRepository.php';


class MembersController extends AppController {

    private $repository;

    public function __construct() {
        $this->repository = new MembersRepository();
    }

    /**
     * Wyświetl widok członków
     */
    public function members() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        include 'public/views/members.php';
    }

    /**
     * API: Pobierz statystyki członków
     */
    public function getMembersStats() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);

        $stats = $this->repository->getMembersStats($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
    }

    /**
     * API: Pobierz listę członków
     */
    public function getMembers() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);

        $data = $this->repository->getMembers($userId, $groupId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /**
     * API: Zmień rolę członka (tylko owner)
     */
    public function updateMemberRole() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['member_id']) || empty($input['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Member ID and role required']);
            return;
        }

        $groupId = $this->getActiveGroupId($input['group_id'] ?? null);

        $result = $this->repository->updateMemberRole(
            $userId, 
            (int)$input['member_id'], 
            $groupId, 
            $input['role']
        );

        header('Content-Type: application/json');

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }

    /**
     * API: Usuń członka z grupy (tylko owner)
     */
    public function removeMember() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['member_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Member ID required']);
            return;
        }

        $groupId = $this->getActiveGroupId($input['group_id'] ?? null);

        $result = $this->repository->removeMember($userId, (int)$input['member_id'], $groupId);

        header('Content-Type: application/json');

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }
}