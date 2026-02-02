/**
 * Members Page JavaScript
 * Handles member listing, role management, and member removal
 */

// Global state
let membersData = [];
let isOwner = false;

/**
 * Initialize the members page
 */
function initializeMembers() {
    loadCurrentUser();
    loadUserGroups();
    loadMembersStats();
    loadMembers();
}

/**
 * Load members statistics for summary widgets
 */
function loadMembersStats() {
    fetch('/api/members/stats')
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                renderMembersStats(data.data);
            }
        })
        .catch(function(error) {
            console.error('Error loading members stats:', error);
        });
}

/**
 * Render members statistics in summary widgets
 */
function renderMembersStats(stats) {
    document.getElementById('totalMembersValue').textContent = stats.total_members || 0;
    document.getElementById('totalSpentValue').textContent = '$' + formatNumber(stats.total_spent || 0);
    
    if (stats.top_contributor) {
        document.getElementById('topContributorName').textContent = stats.top_contributor.name || 'N/A';
        document.getElementById('topContributorAmount').textContent = '$' + formatNumber(stats.top_contributor.amount || 0) + ' spent';
    } else {
        document.getElementById('topContributorName').textContent = 'N/A';
        document.getElementById('topContributorAmount').textContent = '$0 spent';
    }
    
    document.getElementById('avgSpendingValue').textContent = '$' + formatNumber(stats.avg_spending || 0);
}

/**
 * Load members list
 */
function loadMembers() {
    var container = document.getElementById('membersGrid');
    container.innerHTML = '<div class="loading">Loading members...</div>';
    
    fetch('/api/members/list')
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                membersData = data.data.members || [];
                isOwner = data.data.is_owner || false;
                
                // Show/hide Add Member button based on ownership
                if (isOwner) {
                    document.getElementById('addMemberBtn').style.display = 'block';
                }
                
                renderMembers(membersData);
            } else {
                container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">👥</div><p>Failed to load members</p></div>';
            }
        })
        .catch(function(error) {
            console.error('Error loading members:', error);
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Error loading members</p></div>';
        });
}

/**
 * Render members grid
 */
function renderMembers(members) {
    var container = document.getElementById('membersGrid');
    var countElement = document.getElementById('memberCount');
    
    countElement.textContent = members.length + ' Member' + (members.length !== 1 ? 's' : '');
    
    if (members.length === 0) {
        container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">👥</div><p>No members found</p></div>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < members.length; i++) {
        html += renderMemberCard(members[i]);
    }
    
    container.innerHTML = html;
}

/**
 * Render individual member card
 */
function renderMemberCard(member) {
    var initials = getInitials(member.name);
    var roleBadgeClass = member.role === 'owner' ? 'owner' : 'editor';
    var roleLabel = member.role === 'owner' ? 'Owner' : 'Editor';
    
    var balanceClass = '';
    var balanceValue = parseFloat(member.balance) || 0;
    if (balanceValue > 0) {
        balanceClass = 'positive';
    } else if (balanceValue < 0) {
        balanceClass = 'negative';
    }
    
    var formattedBalance = (balanceValue >= 0 ? '+$' : '-$') + formatNumber(Math.abs(balanceValue));
    var formattedTotalSpent = '$' + formatNumber(member.total_spent || 0);
    var joinedDate = formatDate(member.joined_at);
    
    var actionsHtml = '';
    // Show edit/remove buttons only if current user is owner AND this member is not the owner
    if (isOwner && member.role !== 'owner') {
        actionsHtml = '<div class="member-actions">' +
            '<button class="btn-edit" onclick="openEditMemberModal(' + member.user_id + ')">Edit</button>' +
            '<button class="btn-remove" onclick="openRemoveMemberModal(' + member.user_id + ', \'' + escapeHtml(member.name) + '\')">Remove</button>' +
            '</div>';
    }
    
    return '<div class="member-card" data-member-id="' + member.user_id + '">' +
        '<div class="member-avatar">' + initials + '</div>' +
        '<div class="member-info">' +
            '<div class="member-header">' +
                '<h3 class="member-name">' + escapeHtml(member.name) + '</h3>' +
                '<span class="role-badge ' + roleBadgeClass + '">' + roleLabel + '</span>' +
            '</div>' +
            '<div class="member-joined">Joined ' + joinedDate + '</div>' +
            '<div class="member-email">' + escapeHtml(member.email) + '</div>' +
            '<div class="member-stats">' +
                '<div class="stat-item">' +
                    '<span class="stat-label">Balance</span>' +
                    '<span class="stat-value ' + balanceClass + '">' + formattedBalance + '</span>' +
                '</div>' +
                '<div class="stat-item">' +
                    '<span class="stat-label">Total Spent</span>' +
                    '<span class="stat-value">' + formattedTotalSpent + '</span>' +
                '</div>' +
            '</div>' +
            actionsHtml +
        '</div>' +
    '</div>';
}

/**
 * Get initials from name
 */
function getInitials(name) {
    if (!name) return '?';
    var parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }
    return name.charAt(0).toUpperCase();
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format date string
 */
function formatDate(dateString) {
    if (!dateString) return 'Unknown';
    var date = new Date(dateString);
    var options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Open edit member modal
 */
function openEditMemberModal(memberId) {
    var member = findMemberById(memberId);
    if (!member) return;
    
    document.getElementById('editMemberId').value = memberId;
    document.getElementById('editMemberName').value = member.name;
    document.getElementById('editMemberEmail').value = member.email;
    document.getElementById('editMemberRole').value = member.role;
    
    document.getElementById('editMemberModal').classList.add('open');
}

/**
 * Close edit member modal
 */
function closeEditMemberModal() {
    document.getElementById('editMemberModal').classList.remove('open');
    document.getElementById('editMemberForm').reset();
}

/**
 * Handle edit member form submission
 */
function handleEditMember(event) {
    event.preventDefault();
    
    var memberId = document.getElementById('editMemberId').value;
    var newRole = document.getElementById('editMemberRole').value;
    
    fetch('/api/members/update-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            member_id: parseInt(memberId),
            role: newRole
        })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            closeEditMemberModal();
            loadMembers();
            loadMembersStats();
        } else {
            alert(data.message || 'Failed to update member role');
        }
    })
    .catch(function(error) {
        console.error('Error updating member:', error);
        alert('Error updating member');
    });
}

/**
 * Open remove member modal
 */
function openRemoveMemberModal(memberId, memberName) {
    document.getElementById('removeMemberId').value = memberId;
    document.getElementById('removeMemberName').textContent = memberName;
    document.getElementById('removeMemberModal').classList.add('open');
}

/**
 * Close remove member modal
 */
function closeRemoveMemberModal() {
    document.getElementById('removeMemberModal').classList.remove('open');
}

/**
 * Confirm and remove member
 */
function confirmRemoveMember() {
    var memberId = document.getElementById('removeMemberId').value;
    
    fetch('/api/members/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            member_id: parseInt(memberId)
        })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            closeRemoveMemberModal();
            loadMembers();
            loadMembersStats();
        } else {
            alert(data.message || 'Failed to remove member');
        }
    })
    .catch(function(error) {
        console.error('Error removing member:', error);
        alert('Error removing member');
    });
}

/**
 * Find member by ID
 */
function findMemberById(memberId) {
    for (var i = 0; i < membersData.length; i++) {
        if (membersData[i].user_id == memberId) {
            return membersData[i];
        }
    }
    return null;
}

/**
 * Open add member modal
 */
function openAddMemberModal() {
    document.getElementById('addMemberModal').classList.add('open');
}

/**
 * Close add member modal
 */
function closeAddMemberModal() {
    document.getElementById('addMemberModal').classList.remove('open');
    document.getElementById('addMemberForm').reset();
}

/**
 * Handle add member form submission
 * Note: This is a placeholder - backend endpoint for adding members needs to be implemented
 */
function handleAddMember(event) {
    event.preventDefault();
    
    var email = document.getElementById('addMemberEmail').value;
    var role = document.getElementById('addMemberRole').value;
    
    // Placeholder - show message that feature is not yet implemented
    alert('Add member feature coming soon! Email: ' + email + ', Role: ' + role);
    closeAddMemberModal();
}
