<?php
$pageTitle = 'Reports';
$pageStyle = 'reports';
$pageScript = 'reports';
$activePage = 'reports';
$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js'];
include 'public/views/partials/header.php';
?>
<body onload="initializeReports()">
    <div class="main-content">
        <?php include 'public/views/partials/topbar.php'; ?>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <h1>Reports</h1>
                <p class="subtitle">Visual insights and spending analysis</p>
            </div>

            <!-- Summary Widgets -->
            <div class="summary-widgets">
                <div class="summary-card">
                    <h3>This Month</h3>
                    <div class="summary-value" id="thisMonthValue">$0</div>
                    <div class="summary-label">Total spending</div>
                </div>
                <div class="summary-card">
                    <h3>Last Month</h3>
                    <div class="summary-value" id="lastMonthValue">$0</div>
                    <div class="summary-label" id="changeLabel">+0% increase</div>
                </div>
                <div class="summary-card">
                    <h3>Avg/Month</h3>
                    <div class="summary-value" id="avgMonthValue">$0</div>
                    <div class="summary-label">Last 6 months</div>
                </div>
                <div class="summary-card">
                    <h3>Top Category</h3>
                    <div class="summary-value category-value" id="topCategoryName">N/A</div>
                    <div class="summary-label" id="topCategoryAmount">$0 this month</div>
                </div>
            </div>

            <!-- Monthly Comparison Chart -->
            <div class="chart-card full-width">
                <h3>Monthly Comparison by Member</h3>
                <div class="chart-container">
                    <canvas id="monthlyComparisonChart"></canvas>
                </div>
            </div>

            <!-- Spending Trends Chart -->
            <div class="chart-card full-width">
                <h3>Spending Trends by Category</h3>
                <div class="chart-container">
                    <canvas id="spendingTrendsChart"></canvas>
                </div>
            </div>

            <!-- Bottom Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3>Category Distribution</h3>
                    <div class="chart-container pie-container">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Member Contributions</h3>
                    <div class="chart-container">
                        <canvas id="memberContributionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'public/views/partials/navbar.php'; ?>
</body>
</html>
