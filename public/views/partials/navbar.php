<nav class="nav-menu" id="navMenu">
    <ul>
        <li><a href="/dashboard" <?= ($activePage ?? '') === 'dashboard' ? 'class="active"' : '' ?>>Dashboard</a></li>
        <li><a href="/transactions" <?= ($activePage ?? '') === 'transactions' ? 'class="active"' : '' ?>>Transactions</a></li>
        <li><a href="/reports" <?= ($activePage ?? '') === 'reports' ? 'class="active"' : '' ?>>Reports</a></li>
        <li><a href="/members" <?= ($activePage ?? '') === 'members' ? 'class="active"' : '' ?>>Members</a></li>
    </ul>
</nav>
