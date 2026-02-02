// Globalna zmienna dla aktywnej grupy
var activeGroupId = null;

function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
    document.getElementById('navOverlay').classList.toggle('open');
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('open');
}

// Załaduj dane zalogowanego użytkownika na starcie
function initializeDashboard() {
    loadCurrentUser();
    loadUserGroups();
}

// Inicjalizacja dla innych stron (transactions, reports, members)
function initializeCommon() {
    loadCurrentUser();
    loadUserGroups();
}

// Załaduj dane aktualnego użytkownika
function loadCurrentUser() {
    fetch('/api/current-user', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success && data.user) {
            var usernameEl = document.querySelector('.username');
            var emailEl = document.querySelector('.email');
            
            if (usernameEl) {
                usernameEl.textContent = data.user.username || 'User';
            }
            if (emailEl) {
                emailEl.textContent = data.user.email || '';
            }
        }
    })
    .catch(function(error) {
        console.error('Błąd ładowania danych użytkownika:', error);
    });
}

// Załaduj grupy użytkownika do selektora
function loadUserGroups() {
    fetch('/api/groups')
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var selector = document.getElementById('groupSelector');
                if (!selector) return;

                var groups = data.data.groups || [];
                activeGroupId = data.data.active_group_id;

                selector.innerHTML = '';

                if (groups.length === 0) {
                    selector.innerHTML = '<option value="">Brak grup</option>';
                    return;
                }

                for (var i = 0; i < groups.length; i++) {
                    var option = document.createElement('option');
                    option.value = groups[i].id;
                    option.textContent = groups[i].name;
                    if (groups[i].id == activeGroupId) {
                        option.selected = true;
                    }
                    selector.appendChild(option);
                }
            }
        })
        .catch(function(error) {
            console.error('Błąd ładowania grup:', error);
        });
}

// Zmień aktywną grupę
function changeGroup(groupId) {
    if (!groupId) return;

    fetch('/api/groups/set-active', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ group_id: parseInt(groupId) })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            activeGroupId = data.active_group_id;
            // Przeładuj stronę aby załadować dane nowej grupy
            window.location.reload();
        } else {
            alert('Błąd zmiany grupy');
        }
    })
    .catch(function(error) {
        console.error('Błąd zmiany grupy:', error);
    });
}

// Obsługa wylogowania
function handleLogout() {
    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            window.location.href = '/login';
        } else {
            alert('Błąd wylogowania');
        }
    })
    .catch(function(error) {
        console.error('Błąd podczas wylogowania:', error);
        alert('Błąd podczas wylogowania');
    });
}