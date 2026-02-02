<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    $pageTitle = "My Groups - BudgetApp";
    $pageStyle = "groups";
    include 'public/views/partials/header.php'; 
    ?>
</head>
<body>
    <div class="main-content">
        <?php include 'public/views/partials/topbar.php'; ?>
        
        <div class="page-container">
            <div class="page-header">
                <div class="header-left">
                    <h1>My Groups</h1>
                    <p class="subtitle">Manage your budget groups</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <span>+</span> Create Group
                    </button>
                </div>
            </div>

            <div class="groups-container">
                <div class="groups-grid" id="groupsGrid">
                    <div class="loading">Loading groups...</div>
                </div>
            </div>
        </div>

        <?php include 'public/views/partials/navbar.php'; ?>
    </div>

    <!-- Create Group Modal -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Create New Group</h2>
                <button class="close-btn" onclick="closeCreateModal()">&times;</button>
            </div>
            <form id="createGroupForm" onsubmit="handleCreateGroup(event)">
                <div class="form-group">
                    <label for="groupName">Group Name *</label>
                    <input type="text" id="groupName" name="name" required placeholder="e.g., Family Budget">
                </div>
                <div class="form-group">
                    <label for="groupDescription">Description</label>
                    <input type="text" id="groupDescription" name="description" placeholder="Optional description">
                </div>
                <div class="form-group">
                    <label for="groupBudget">Monthly Budget</label>
                    <input type="number" id="groupBudget" name="budget" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Group</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leave Group Modal -->
    <div class="modal-overlay" id="leaveModal">
        <div class="modal modal-small">
            <div class="modal-header">
                <h2>Leave Group</h2>
                <button class="close-btn" onclick="closeLeaveModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to leave <strong id="leaveGroupName"></strong>?</p>
                <p class="warning-text">Your transactions will remain in the group.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeLeaveModal()">Cancel</button>
                <button class="btn btn-danger" onclick="confirmLeaveGroup()">Leave Group</button>
            </div>
        </div>
    </div>

    <script src="/public/scripts/main.js"></script>
    <script src="/public/scripts/groups.js"></script>
</body>
</html>
