<div class="top-bar">
    <div class="left-item">
        <button class="burger-btn" onclick="toggleMenu()">☰</button>
        <select id="groupSelector" onchange="changeGroup(this.value)">
            <option value="">Select group...</option>
        </select>
    </div>
    <div class="right-item">
        <button class="user-btn" onclick="toggleUserMenu()">U</button>
        <div class="user-menu" id="userMenu">
            <p class="username">Username</p>
            <p class="email">email@example.com</p>
            <a href="/groups" class="menu-link" onclick="closeUserMenu()">
                <span class="menu-icon">📁</span> My Groups
            </a>
            <button class="logout" onclick="handleLogout()">Logout</button>
        </div>
    </div>
</div>
<div class="nav-overlay" id="navOverlay" onclick="toggleMenu()"></div>
