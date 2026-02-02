<?php
$pageTitle = 'Members';
$pageStyle = 'members';
$pageScript = 'members';
$activePage = 'members';
include 'public/views/partials/header.php';
?>
<body onload="initializeMembers()">
    <div class="main-content">
        <?php include 'public/views/partials/topbar.php'; ?>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <h1>Members</h1>
                    <p class="subtitle">Group members and spending overview</p>
                </div>
                <div class="header-right" id="addMemberBtn" style="display: none;">
                    <button class="btn btn-primary" onclick="openAddMemberModal()">
                        + Add Member
                    </button>
                </div>
            </div>

            <!-- Summary Widgets -->
            <div class="summary-widgets">
                <div class="summary-card">
                    <h3>Total Members</h3>
                    <div class="summary-value" id="totalMembersValue">0</div>
                    <div class="summary-label">Active members</div>
                </div>
                <div class="summary-card">
                    <h3>Total Spent</h3>
                    <div class="summary-value" id="totalSpentValue">$0</div>
                    <div class="summary-label">Group total</div>
                </div>
                <div class="summary-card">
                    <h3>Top Contributor</h3>
                    <div class="summary-value contributor-value" id="topContributorName">N/A</div>
                    <div class="summary-label" id="topContributorAmount">$0 spent</div>
                </div>
                <div class="summary-card">
                    <h3>Avg Spending</h3>
                    <div class="summary-value" id="avgSpendingValue">$0</div>
                    <div class="summary-label">Per member</div>
                </div>
            </div>

            <!-- Members Grid -->
            <div class="members-container">
                <div class="members-header">
                    <span id="memberCount">0 Members</span>
                </div>
                <div class="members-grid" id="membersGrid">
                    <div class="loading">Loading members...</div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'public/views/partials/navbar.php'; ?>

    <!-- Edit Member Modal -->
    <div class="modal-overlay" id="editMemberModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit Member</h2>
                <button class="close-btn" onclick="closeEditMemberModal()">×</button>
            </div>
            <form id="editMemberForm" onsubmit="handleEditMember(event)">
                <input type="hidden" id="editMemberId">
                <div class="form-group">
                    <label>Member Name</label>
                    <input type="text" id="editMemberName" disabled>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editMemberEmail" disabled>
                </div>
                <div class="form-group">
                    <label for="editMemberRole">Role</label>
                    <select id="editMemberRole" required>
                        <option value="editor">Editor</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditMemberModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Remove Member Confirmation Modal -->
    <div class="modal-overlay" id="removeMemberModal">
        <div class="modal modal-small">
            <div class="modal-header">
                <h2>Remove Member</h2>
                <button class="close-btn" onclick="closeRemoveMemberModal()">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeMemberName"></strong> from this group?</p>
                <p class="warning-text">This action cannot be undone.</p>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeRemoveMemberModal()">Cancel</button>
                <button type="button" class="btn-danger" onclick="confirmRemoveMember()">Remove</button>
            </div>
            <input type="hidden" id="removeMemberId">
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal-overlay" id="addMemberModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add Member</h2>
                <button class="close-btn" onclick="closeAddMemberModal()">×</button>
            </div>
            <form id="addMemberForm" onsubmit="handleAddMember(event)">
                <div class="form-group">
                    <label for="addMemberEmail">Email Address</label>
                    <input type="email" id="addMemberEmail" placeholder="Enter member's email" required>
                </div>
                <div class="form-group">
                    <label for="addMemberRole">Role</label>
                    <select id="addMemberRole" required>
                        <option value="editor">Editor</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeAddMemberModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
