// Globalna zmienna dla aktywnej grupy
var activeGroupId = null;

// =====================
// FUNKCJE POMOCNICZE (UTILS)
// =====================

/**
 * Formatuj liczbę z 2 miejscami dziesiętnymi i separatorami tysięcy
 * @param {number} num
 * @returns {string}
 */
function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Formatuj datę do czytelnego formatu (Jan 15, 2025)
 * @param {string} dateStr
 * @returns {string}
 */
function formatDate(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Formatuj datę do krótkiego formatu (Jan 15)
 * @param {string} dateStr
 * @returns {string}
 */
function formatDateShort(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return months[date.getMonth()] + ' ' + date.getDate();
}

/**
 * Escape HTML aby zapobiec XSS
 * @param {string} text
 * @returns {string}
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Pobierz inicjały z imienia i nazwiska
 * @param {string} name
 * @returns {string}
 */
function getInitials(name) {
    if (!name) return '?';
    var parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

/**
 * Formatuj kwotę (alias dla formatNumber)
 * @param {number} amount
 * @returns {string}
 */
function formatAmount(amount) {
    return parseFloat(amount).toFixed(2);
}

/**
 * Formatuj walutę
 * @param {number} amount
 * @returns {string}
 */
function formatCurrency(amount) {
    return '$' + formatNumber(amount);
}

// =====================
// NAWIGACJA I MENU
// =====================

function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
    document.getElementById('navOverlay').classList.toggle('open');
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('open');
}

function closeUserMenu() {
    document.getElementById('userMenu').classList.remove('open');
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