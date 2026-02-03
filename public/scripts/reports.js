// Reports page JavaScript
// Chart instances
let monthlyComparisonChart = null;
let spendingTrendsChart = null;
let categoryDistributionChart = null;
let memberContributionsChart = null;

/**
 * Initialize reports page
 */
function initializeReports() {
    loadCurrentUser();
    loadUserGroups();
    loadSummaryStats();
    loadMonthlyComparison();
    loadSpendingTrends();
    loadCategoryDistribution();
    loadMemberContributions();
}

/**
 * Load summary statistics
 */
function loadSummaryStats() {
    fetch('/api/reports/summary')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load summary');
            return response.json();
        })
        .then(function(data) {
            renderSummaryStats(data);
        })
        .catch(function(error) {
            console.error('Error loading summary:', error);
        });
}

/**
 * Render summary statistics
 */
function renderSummaryStats(data) {
    document.getElementById('thisMonthValue').textContent = '$' + formatNumber(data.this_month);
    document.getElementById('lastMonthValue').textContent = '$' + formatNumber(data.last_month);
    document.getElementById('avgMonthValue').textContent = '$' + formatNumber(data.avg_month);
    
    // Change label
    const changeLabel = document.getElementById('changeLabel');
    const changePercent = data.change_percent;
    if (changePercent >= 0) {
        changeLabel.textContent = '+' + changePercent + '% increase';
        changeLabel.className = 'summary-label negative';
    } else {
        changeLabel.textContent = changePercent + '% decrease';
        changeLabel.className = 'summary-label positive';
    }
    
    // Top category
    document.getElementById('topCategoryName').textContent = data.top_category.name;
    document.getElementById('topCategoryAmount').textContent = '$' + formatReportNumber(data.top_category.amount) + ' this month';
}

/**
 * Format number for reports (bez miejsc dziesiętnych)
 */
function formatReportNumber(num) {
    return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

/**
 * Load Monthly Comparison by Member chart
 */
function loadMonthlyComparison() {
    fetch('/api/reports/monthly-by-member')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load data');
            return response.json();
        })
        .then(function(data) {
            renderMonthlyComparison(data);
        })
        .catch(function(error) {
            console.error('Error loading monthly comparison:', error);
        });
}

function renderMonthlyComparison(data) {
    const ctx = document.getElementById('monthlyComparisonChart').getContext('2d');
    
    if (monthlyComparisonChart) {
        monthlyComparisonChart.destroy();
    }
    
    monthlyComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: data.datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Load Spending Trends by Category chart
 */
function loadSpendingTrends() {
    fetch('/api/reports/spending-trends')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load data');
            return response.json();
        })
        .then(function(data) {
            renderSpendingTrends(data);
        })
        .catch(function(error) {
            console.error('Error loading spending trends:', error);
        });
}

function renderSpendingTrends(data) {
    const ctx = document.getElementById('spendingTrendsChart').getContext('2d');
    
    if (spendingTrendsChart) {
        spendingTrendsChart.destroy();
    }
    
    spendingTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: data.datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Load Category Distribution chart
 */
function loadCategoryDistribution() {
    fetch('/api/reports/category-distribution')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load data');
            return response.json();
        })
        .then(function(data) {
            renderCategoryDistribution(data);
        })
        .catch(function(error) {
            console.error('Error loading category distribution:', error);
        });
}

function renderCategoryDistribution(data) {
    const ctx = document.getElementById('categoryDistributionChart').getContext('2d');
    
    if (categoryDistributionChart) {
        categoryDistributionChart.destroy();
    }
    
    // Calculate percentages for labels
    const total = data.data.reduce(function(sum, val) { return sum + val; }, 0);
    const labelsWithPercent = data.labels.map(function(label, i) {
        const percent = total > 0 ? Math.round((data.data[i] / total) * 100) : 0;
        return label + ' ' + percent + '%';
    });
    
    categoryDistributionChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labelsWithPercent,
            datasets: [{
                data: data.data,
                backgroundColor: data.colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

/**
 * Load Member Contributions chart
 */
function loadMemberContributions() {
    fetch('/api/reports/member-contributions')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load data');
            return response.json();
        })
        .then(function(data) {
            renderMemberContributions(data);
        })
        .catch(function(error) {
            console.error('Error loading member contributions:', error);
        });
}

function renderMemberContributions(data) {
    const ctx = document.getElementById('memberContributionsChart').getContext('2d');
    
    if (memberContributionsChart) {
        memberContributionsChart.destroy();
    }
    
    memberContributionsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: data.colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}
