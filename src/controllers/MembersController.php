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
        $this->requireLogin();
        include 'public/views/members.php';
    }

    /**
     * API: Pobierz statystyki członków
     */
    public function getMembersStats() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $stats = $this->repository->getMembersStats($userId, $groupId);

        $this->jsonResponse(['success' => true, 'data' => $stats]);
    }

    /**
     * API: Pobierz listę członków
     */
    public function getMembers() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groupId = $this->getActiveGroupId($_GET['group_id'] ?? null);
        $data = $this->repository->getMembers($userId, $groupId);

        $this->jsonResponse(['success' => true, 'data' => $data]);
    }

    /**
     * API: Zmień rolę członka (tylko owner)
     */
    public function updateMemberRole() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['member_id']) || empty($input['role'])) {
            $this->jsonResponse(['error' => 'Member ID and role required'], 400);
            return;
        }

        $groupId = $this->getActiveGroupId($input['group_id'] ?? null);

        $result = $this->repository->updateMemberRole(
            $userId, 
            (int)$input['member_id'], 
            $groupId, 
            $input['role']
        );

        if ($result['success']) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * API: Usuń członka z grupy (tylko owner)
     */
    public function removeMember() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['member_id'])) {
            $this->jsonResponse(['error' => 'Member ID required'], 400);
            return;
        }

        $groupId = $this->getActiveGroupId($input['group_id'] ?? null);
        $result = $this->repository->removeMember($userId, (int)$input['member_id'], $groupId);

        if ($result['success']) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }
}