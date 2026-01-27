function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('open');
}

// Załaduj dane zalogowanego użytkownika na starcie
document.addEventListener('DOMContentLoaded', async function() {
    await loadCurrentUser();
    setupLogoutButton();
});

// Załaduj dane aktualnego użytkownika
async function loadCurrentUser() {
    try {
        const response = await fetch('/api/current-user', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.user) {
                // Wyświetl dane użytkownika w menu
                const usernameEl = document.querySelector('.username');
                const emailEl = document.querySelector('.email');
                
                if (usernameEl) {
                    usernameEl.textContent = data.user.username || 'User';
                }
                if (emailEl) {
                    emailEl.textContent = data.user.email || '';
                }
            }
        }
    } catch (error) {
        console.error('Błąd ładowania danych użytkownika:', error);
    }
}

// Obsługa logout buttona
function setupLogoutButton() {
    const logoutBtn = document.querySelector('.logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Redirect do strony logowania
                    window.location.href = '/login';
                } else {
                    alert('Błąd wylogowania: ' + (data.message || 'Nieznany błąd'));
                }
            } catch (error) {
                console.error('Błąd podczas wylogowania:', error);
                alert('Błąd podczas wylogowania');
            }
        });
    }
}