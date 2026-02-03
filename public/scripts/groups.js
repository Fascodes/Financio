// Groups Management JavaScript
// NO addEventListener - using inline onclick handlers

var groupsData = [];
var activeGroupId = null;
var groupToLeave = null;

// Initialize on page load
window.onload = function() {
    loadUserData();
    loadGroups();
    loadGroupsForSelector();
};

// Load user data for topbar
function loadUserData() {
    fetch('/api/current-user')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.user) {
                var usernameEl = document.querySelector('.username');
                var emailEl = document.querySelector('.email');
                
                if (usernameEl) usernameEl.textContent = data.user.username || 'User';
                if (emailEl) emailEl.textContent = data.user.email || '';
            } else {
                console.error('User data error:', data);
            }
        })
        .catch(function(error) {
            console.error('Error loading user data:', error);
        });
}

// Load groups for topbar selector
function loadGroupsForSelector() {
    fetch('/api/groups')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var selector = document.getElementById('groupSelector');
                if (!selector) return;

                var groups = data.data.groups || [];
                var activeId = data.data.active_group_id;

                selector.innerHTML = '';

                if (groups.length === 0) {
                    selector.innerHTML = '<option value="">No groups</option>';
                    return;
                }

                for (var i = 0; i < groups.length; i++) {
                    var option = document.createElement('option');
                    option.value = groups[i].id;
                    option.textContent = groups[i].name;
                    if (groups[i].id == activeId) {
                        option.selected = true;
                    }
                    selector.appendChild(option);
                }
            }
        })
        .catch(function(error) {
            console.error('Error loading groups for selector:', error);
        });
}

// Load groups for main grid
function loadGroups() {
    fetch('/api/groups')
        .then(function(response) { 
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json(); 
        })
        .then(function(result) {
            console.log('Groups API response:', result);
            if (result.success) {
                groupsData = result.data.groups || [];
                activeGroupId = result.data.active_group_id;
                renderGroups();
            } else {
                console.error('API error:', result);
                document.getElementById('groupsGrid').innerHTML = 
                    '<div class="empty-state"><p>Error: ' + (result.error || 'Unknown error') + '</p></div>';
            }
        })
        .catch(function(error) {
            console.error('Error loading groups:', error);
            document.getElementById('groupsGrid').innerHTML = 
                '<div class="empty-state"><p>Error loading groups: ' + error.message + '</p></div>';
        });
}

// Render groups grid
function renderGroups() {
    var container = document.getElementById('groupsGrid');
    
    if (groupsData.length === 0) {
        container.innerHTML = 
            '<div class="empty-state">' +
                '<div class="empty-state-icon">📁</div>' +
                '<p>You don\'t have any groups yet</p>' +
                '<button class="btn btn-primary" onclick="openCreateModal()">Create Your First Group</button>' +
            '</div>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < groupsData.length; i++) {
        var group = groupsData[i];
        var isActive = group.id == activeGroupId;
        var isOwner = group.role === 'owner';
        
        html += '<div class="group-card' + (isActive ? ' active' : '') + '">';
        html += '<div class="group-header">';
        html += '<div class="group-icon">📊</div>';
        html += '<span class="role-badge ' + group.role + '">' + group.role + '</span>';
        html += '</div>';
        
        html += '<div class="group-info">';
        html += '<h3 class="group-name">' + escapeHtml(group.name);
        if (isActive) {
            html += '<span class="active-badge">Active</span>';
        }
        html += '</h3>';
        html += '<p class="group-description">' + (group.description ? escapeHtml(group.description) : 'No description') + '</p>';
        html += '</div>';
        
        html += '<div class="group-stats">';
        html += '<div class="stat-item">';
        html += '<span class="stat-label">Members</span>';
        html += '<span class="stat-value">' + (group.member_count || 1) + '</span>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<span class="stat-label">Budget</span>';
        html += '<span class="stat-value">' + formatCurrency(group.budget || 0) + '</span>';
        html += '</div>';
        html += '</div>';
        
        html += '<div class="group-actions">';
        if (isActive) {
            html += '<button class="btn-select" disabled>Currently Active</button>';
        } else {
            html += '<button class="btn-select" onclick="selectGroup(' + group.id + ')">Select Group</button>';
        }
        if (!isOwner) {
            html += '<button class="btn-leave" onclick="openLeaveModal(' + group.id + ', \'' + escapeHtml(group.name) + '\')">Leave</button>';
        }
        html += '</div>';
        
        html += '</div>';
    }
    
    container.innerHTML = html;
}

// Select a group as active
function selectGroup(groupId) {
    fetch('/api/groups/set-active', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ group_id: groupId })
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success) {
            activeGroupId = groupId;
            renderGroups();
            loadUserData(); // Refresh topbar selector
        }
    })
    .catch(function(error) {
        console.error('Error selecting group:', error);
    });
}

// Create group modal
function openCreateModal() {
    document.getElementById('createModal').classList.add('open');
    document.getElementById('groupName').value = '';
    document.getElementById('groupDescription').value = '';
    document.getElementById('groupBudget').value = '';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('open');
}

// Handle create group form
function handleCreateGroup(e) {
    e.preventDefault();
    
    var name = document.getElementById('groupName').value.trim();
    var description = document.getElementById('groupDescription').value.trim();
    var budget = parseFloat(document.getElementById('groupBudget').value) || 0;
    
    if (!name) {
        alert('Group name is required');
        return;
    }
    
    fetch('/api/groups/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: name,
            description: description,
            budget: budget
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success) {
            closeCreateModal();
            loadGroups();
            loadUserData(); // Refresh topbar selector
        } else {
            alert('Error creating group: ' + (result.error || 'Unknown error'));
        }
    })
    .catch(function(error) {
        console.error('Error creating group:', error);
        alert('Error creating group');
    });
}

// Leave group modal
function openLeaveModal(groupId, groupName) {
    groupToLeave = groupId;
    document.getElementById('leaveGroupName').textContent = groupName;
    document.getElementById('leaveModal').classList.add('open');
}

function closeLeaveModal() {
    document.getElementById('leaveModal').classList.remove('open');
    groupToLeave = null;
}

function confirmLeaveGroup() {
    if (!groupToLeave) return;
    
    fetch('/api/groups/leave', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ group_id: groupToLeave })
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success) {
            closeLeaveModal();
            loadGroups();
            loadGroupsForSelector();
        } else {
            alert('Error leaving group: ' + (result.error || 'Unknown error'));
        }
    })
    .catch(function(error) {
        console.error('Error leaving group:', error);
        alert('Error leaving group');
    });
}

// Helper functions
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2) + ' zł';
}
