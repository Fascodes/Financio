<div class="top-bar">
    <div class="left-item">
        <button class="burger-btn" onclick="toggleMenu()">☰</button>
        <select id="groupSelector" onchange="changeGroup(this.value)">
            <option value="">Wybierz grupę...</option>
        </select>
    </div>
    <div class="right-item">
        <button class="user-btn" onclick="toggleUserMenu()">U</button>
        <div class="user-menu" id="userMenu">
            <p class="username">Username</p>
            <p class="email">email@example.com</p>
            <button class="settings">Settings</button>
            <button class="logout" onclick="handleLogout()">Logout</button>
        </div>
    </div>
</div>
