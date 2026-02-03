<?php

require_once 'src/controllers/AppController.php';
require_once 'src/repositories/GroupRepository.php';
require_once 'src/repositories/UserPreferencesRepository.php';

class GroupController extends AppController {

    private $repository;
    private $preferencesRepository;

    public function __construct() {
        $this->repository = new GroupRepository();
        $this->preferencesRepository = new UserPreferencesRepository();
    }

    /**
     * API: Pobierz grupy użytkownika
     */
    public function getUserGroups() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $groups = $this->repository->getUserGroups($userId);
        
        // Priorytet: sesja > user_preferences > pierwsza grupa
        $activeGroupId = $_SESSION['active_group_id'] ?? null;
        
        if (!$activeGroupId) {
            // Pobierz domyślną grupę z user_preferences (relacja 1:1)
            $activeGroupId = $this->preferencesRepository->getDefaultGroup($userId);
        }

        // Sprawdź czy grupa nadal istnieje w liście grup użytkownika
        $groupExists = false;
        foreach ($groups as $group) {
            if ($group['id'] == $activeGroupId) {
                $groupExists = true;
                break;
            }
        }

        // Jeśli grupa nie istnieje lub nie ustawiono, użyj pierwszej
        if (!$groupExists && !empty($groups)) {
            $activeGroupId = $groups[0]['id'];
        }

        $_SESSION['active_group_id'] = $activeGroupId;

        $this->jsonResponse([
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
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['group_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Group ID required'], 400);
            return;
        }

        $groupId = (int)$input['group_id'];

        // Sprawdź czy użytkownik należy do grupy
        if (!$this->repository->userBelongsToGroup($userId, $groupId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            return;
        }

        // Zapisz w sesji
        $_SESSION['active_group_id'] = $groupId;
        
        // Zapisz jako domyślną grupę w user_preferences (relacja 1:1)
        $this->preferencesRepository->setDefaultGroup($userId, $groupId);
        
        $this->jsonResponse(['success' => true, 'active_group_id' => $groupId]);
    }

    /**
     * API: Utwórz nową grupę
     */
    public function createGroup() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['name'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Group name required'], 400);
            return;
        }

        $result = $this->repository->createGroup(
            $userId,
            trim($input['name']),
            $input['description'] ?? '',
            $input['budget'] ?? 0
        );

        if ($result['success']) {
            // Ustaw nową grupę jako aktywną
            $_SESSION['active_group_id'] = $result['group_id'];
            // Zapisz jako domyślną grupę (relacja 1:1)
            $this->preferencesRepository->setDefaultGroup($userId, $result['group_id']);
            $this->jsonResponse(['success' => true, 'group_id' => $result['group_id']]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * API: Opuść grupę
     */
    public function leaveGroup() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['group_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Group ID required'], 400);
            return;
        }

        $groupId = (int)$input['group_id'];
        $result = $this->repository->leaveGroup($userId, $groupId);

        if ($result['success']) {
            // Jeśli opuścił aktywną grupę, ustaw inną
            if (isset($_SESSION['active_group_id']) && $_SESSION['active_group_id'] == $groupId) {
                $groups = $this->repository->getUserGroups($userId);
                if (!empty($groups)) {
                    $_SESSION['active_group_id'] = $groups[0]['id'];
                    // Zaktualizuj domyślną grupę (relacja 1:1)
                    $this->preferencesRepository->setDefaultGroup($userId, $groups[0]['id']);
                } else {
                    unset($_SESSION['active_group_id']);
                }
            }
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * API: Dodaj członka do grupy
     */
    public function addMember() {
        if (!$this->requireApiAuth()) return;

        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        if (empty($input['group_id']) || empty($input['email'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Group ID and email required'], 400);
            return;
        }

        $groupId = (int)$input['group_id'];
        $email = trim($input['email']);
        $role = isset($input['role']) && $input['role'] === 'owner' ? 'owner' : 'editor';

        // Sprawdź czy użytkownik jest właścicielem grupy
        if (!$this->repository->isGroupOwner($userId, $groupId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Only group owners can add members'], 403);
            return;
        }

        $result = $this->repository->addMemberByEmail($groupId, $email, $role);

        if ($result['success']) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * Widok zarządzania grupami
     */
    public function groups() {
        $this->requireLogin();
        include 'public/views/groups.php';
    }
}
