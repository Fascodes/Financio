<?php
$pageTitle = 'Dashboard';
$pageStyle = 'dashboard';
$pageScript = 'dashboard';
$activePage = 'dashboard';
$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js'];
include 'public/views/partials/header.php';
?>
<body>
    <div class="main-content">
        <?php include 'public/views/partials/topbar.php'; ?>
        
        <div class="page-container">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p class="page-subtitle">Overview of your group budget</p>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Budget</span>
                    </div>
                    <div class="stat-value" id="budget-amount">$0.00</div>
                    <div class="stat-label" id="budget-footer">Monthly allocation</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Spending</span>
                    </div>
                    <div class="stat-value" id="spending-amount">$0.00</div>
                    <div class="stat-label" id="spending-footer">0% of budget</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Balance</span>
                    </div>
                    <div class="stat-value balance-positive" id="balance-amount">$0.00</div>
                    <div class="stat-label" id="balance-footer">Remaining budget</div>
                </div>
            </div>

            <div class="charts-section">
                <div class="chart-card">
                    <h3 class="chart-title">Spending Trends</h3>
                    <div class="chart-container">
                        <canvas id="trendsGraph"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Spending by Category</h3>
                    <div class="chart-container">
                        <canvas id="categoryGraph"></canvas>
                    </div>
                </div>
            </div>

            <div class="lists-section">
                <div class="list-card">
                    <h3 class="list-title">Recent Transactions</h3>
                    <ul class="transaction-list" id="transactionList">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="list-card">
                    <h3 class="list-title">Group Members</h3>
                    <ul class="members-list" id="membersList">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'public/views/partials/navbar.php'; ?>

    <script>
        window.onload = function() {
            initializeDashboard();
            initializeCharts();
        };
    </script>
</body>
</html>
