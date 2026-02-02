<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/GroupRepository.php';

class GroupController extends AppController {

    private $repository;

    public function __construct() {
        $this->repository = new GroupRepository();
    }

    /**
     * API: Pobierz grupy użytkownika
     */
    public function getUserGroups() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $groups = $this->repository->getUserGroups($userId);
        $activeGroupId = $_SESSION['active_group_id'] ?? null;

        // Jeśli brak aktywnej grupy w sesji, ustaw pierwszą
        if (!$activeGroupId && !empty($groups)) {
            $activeGroupId = $groups[0]['id'];
            $_SESSION['active_group_id'] = $activeGroupId;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                'groups' => $groups,
                'active_group_id' => $activeGroupId
            ]
        ]);
    }

    /**
     * API: Zmień aktywną grupę
     */
    public function setActiveGroup() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['group_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Group ID required']);
            return;
        }

        $groupId = (int)$input['group_id'];

        // Sprawdź czy użytkownik należy do grupy
        if (!$this->repository->userBelongsToGroup($userId, $groupId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $_SESSION['active_group_id'] = $groupId;

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'active_group_id' => $groupId]);
    }

    /**
     * API: Utwórz nową grupę
     */
    public function createGroup() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Group name required']);
            return;
        }

        $result = $this->repository->createGroup(
            $userId,
            trim($input['name']),
            $input['description'] ?? '',
            $input['budget'] ?? 0
        );

        header('Content-Type: application/json');

        if ($result['success']) {
            // Ustaw nową grupę jako aktywną
            $_SESSION['active_group_id'] = $result['group_id'];
            echo json_encode(['success' => true, 'group_id' => $result['group_id']]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }
}
