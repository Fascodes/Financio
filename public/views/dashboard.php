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
        
        <div class="dashboard-text">
            <h1>DASHBOARD</h1>
        </div>
        <div class="top-widgets">
            
            <div class="widget" data-id="1">
                <div class="widget-header">
                    <span class="widget-title">Budżet</span>
                </div>
                <div class="widget-amount" id="budget-amount">
                    0.00 PLN
                </div>
                <div class="widget-footer" id="budget-footer">
                    Miesięczna alokacja
                </div>
            </div>

            <div class="widget" data-id="2">
                <div class="widget-header">
                    <span class="widget-title">Wydatki</span>
                </div>
                <div class="widget-amount spending" id="spending-amount">
                    0.00 PLN
                </div>
                <div class="widget-footer" id="spending-footer">
                    0% budżetu
                </div>
            </div>

            <div class="widget" data-id="3">
                <div class="widget-header">
                    <span class="widget-title">Balans</span>
                </div>
                <div class="widget-amount balance" id="balance-amount">
                    0.00 PLN
                </div>
                <div class="widget-footer" id="balance-footer">
                    Pozostały budżet
                </div>
            </div>

        </div>
        <div class="graph-widgets">
            <canvas class="trends-graph" id="trendsGraph" width="400" height="200"></canvas>
            <canvas class="category-graph" id="categoryGraph" width="400" height="200"></canvas>
        </div>
        <div class="bottom-widgets">
            <div class="recent-transactions">
                <div class="widget-list-header">
                    <h3>Ostatnie transakcje</h3>
                </div>
                <ul class="transaction-list" id="transactionList">
                    <li class="loading">Ładowanie danych...</li>
                </ul>
            </div>
            <div class="group-members">
                <div class="widget-list-header">
                    <h3>Członkowie grupy</h3>
                </div>
                <ul class="members-list" id="membersList">
                    <li class="loading">Ładowanie danych...</li>
                </ul>
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
