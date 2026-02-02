// Funkcja do ładowania podsumowania budżetu via FETCH API
async function loadBudgetSummary() {
    try {
        const response = await fetch('/api/budget-summary');
        if (!response.ok) {
            throw new Error('Błąd sieci: ' + response.status);
        }
        const data = await response.json();
        renderBudgetSummary(data);
    } catch (error) {
        console.error('Błąd przy ładowaniu podsumowania budżetu:', error);
    }
}

// Funkcja do renderowania podsumowania budżetu
function renderBudgetSummary(data) {
    // Budżet
    var budgetAmount = document.getElementById('budget-amount');
    if (budgetAmount) {
        budgetAmount.textContent = '$' + formatNumber(data.budget);
    }

    // Wydatki
    var spendingAmount = document.getElementById('spending-amount');
    var spendingFooter = document.getElementById('spending-footer');
    if (spendingAmount) {
        spendingAmount.textContent = '$' + formatNumber(data.spending);
    }
    if (spendingFooter) {
        spendingFooter.textContent = data.percentage + '% of budget';
    }

    // Balans
    var balanceAmount = document.getElementById('balance-amount');
    if (balanceAmount) {
        balanceAmount.textContent = '$' + formatNumber(data.balance);
        // Dodaj klasę dla koloru (zielony jeśli dodatni, czerwony jeśli ujemny)
        if (data.balance >= 0) {
            balanceAmount.classList.add('balance-positive');
            balanceAmount.classList.remove('balance-negative');
        } else {
            balanceAmount.classList.add('balance-negative');
            balanceAmount.classList.remove('balance-positive');
        }
    }
}

// Funkcja do formatowania liczb z przecinkami
function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Funkcja do ładowania ostatnich transakcji via FETCH API
async function loadRecentTransactions() {
    try {
        const response = await fetch('/api/recent-transactions');
        if (!response.ok) {
            throw new Error('Błąd sieci: ' + response.status);
        }
        const transactions = await response.json();

        if (transactions && transactions.length > 0) {
            renderTransactionList(transactions);
        } else {
            document.getElementById('transactionList').innerHTML = '<li class="empty-state"><p>No transactions yet</p></li>';
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        document.getElementById('transactionList').innerHTML = '<li class="empty-state"><p>Error loading data</p></li>';
    }
}

// Funkcja do ładowania członków grupy via FETCH API
async function loadGroupMembers() {
    try {
        const response = await fetch('/api/group-members');
        if (!response.ok) {
            throw new Error('Błąd sieci: ' + response.status);
        }
        const members = await response.json();

        if (members && members.length > 0) {
            renderMembersList(members);
        } else {
            document.getElementById('membersList').innerHTML = '<li class="empty-state"><p>No group members</p></li>';
        }
    } catch (error) {
        console.error('Error loading group members:', error);
        document.getElementById('membersList').innerHTML = '<li class="empty-state"><p>Error loading data</p></li>';
    }
}

// Funkcja do renderowania listy transakcji
function renderTransactionList(transactions) {
    var list = document.getElementById('transactionList');
    list.innerHTML = '';

    transactions.forEach(function(transaction) {
        var li = document.createElement('li');
        li.className = 'transaction-item';
        li.innerHTML = 
            '<div class="transaction-info">' +
                '<span class="transaction-description">' + escapeHtml(transaction.description) + '</span>' +
                '<span class="transaction-meta">' + escapeHtml(transaction.user_name || 'Unknown') + ' - ' + formatDate(transaction.date) + '</span>' +
            '</div>' +
            '<div class="transaction-amount">$' + formatNumber(transaction.amount) + '</div>';
        list.appendChild(li);
    });
}

// Funkcja do formatowania daty
function formatDate(dateStr) {
    var date = new Date(dateStr);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return months[date.getMonth()] + ' ' + date.getDate();
}

// Funkcja do escapowania HTML
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Funkcja do renderowania listy członków grupy
function renderMembersList(members) {
    var list = document.getElementById('membersList');
    list.innerHTML = '';

    // Kolory awatarów
    var avatarColors = [
        'linear-gradient(135deg, #74b9ff, #0984e3)',
        'linear-gradient(135deg, #fd79a8, #e84393)',
        'linear-gradient(135deg, #55efc4, #00b894)',
        'linear-gradient(135deg, #ffeaa7, #fdcb6e)',
        'linear-gradient(135deg, #a29bfe, #6c5ce7)'
    ];

    members.forEach(function(member, index) {
        var li = document.createElement('li');
        li.className = 'member-item';
        var balanceClass = member.balance >= 0 ? 'positive' : 'negative';
        var balancePrefix = member.balance >= 0 ? '+$' : '$';
        var statusText = member.balance >= 0 ? 'To receive' : 'To pay';
        var initials = getInitials(member.name);
        var avatarColor = avatarColors[index % avatarColors.length];
        
        li.innerHTML = 
            '<div class="member-avatar" style="background: ' + avatarColor + '">' + initials + '</div>' +
            '<div class="member-info">' +
                '<span class="member-name">' + escapeHtml(member.name) + '</span>' +
                '<span class="member-status">' + statusText + '</span>' +
            '</div>' +
            '<div class="member-balance ' + balanceClass + '">' +
                balancePrefix + formatNumber(Math.abs(member.balance)) +
            '</div>';
        list.appendChild(li);
    });
}

// Funkcja do pobierania inicjałów
function getInitials(name) {
    if (!name) return '?';
    var parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

async function loadChartData() {
    try {
        const response = await fetch('/api/dashboard-data');
        if (!response.ok) {
            throw new Error('Błąd sieci: ' + response.status);
        }
        const chartData = await response.json();

        if (chartData && chartData.length > 0) {
            renderLineChart(chartData);
        } else {
            document.getElementById('trendsGraph').innerHTML = 'Brak danych do wyświetlenia.';
        }
    } catch (error) {
        console.error('Błąd przy ładowaniu danych wykresu liniowego:', error);
    }
}

async function loadCategoryData() {
    try {
        const response = await fetch('/api/category-data');
        if (!response.ok) {
            throw new Error('Błąd sieci: ' + response.status);
        }
        const categoryData = await response.json();

        if (categoryData && categoryData.length > 0) {
            renderPieChart(categoryData);
        } else {
            document.getElementById('categoryGraph').innerHTML = 'Brak danych do wyświetlenia.';
        }
    } catch (error) {
        console.error('Błąd przy ładowaniu danych wykresu kołowego:', error);
    }
}

function renderLineChart(chartData) {
    var ctx = document.getElementById('trendsGraph').getContext('2d');
    var labels = chartData.map(function(item) { return item.month; });
    var values = chartData.map(function(item) { return parseFloat(item.total); });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Spending',
                data: values,
                borderColor: '#3b5bdb',
                backgroundColor: 'rgba(59, 91, 219, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b5bdb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        color: '#636e72'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#636e72'
                    }
                }
            }
        }
    });
}

function renderPieChart(categoryData) {
    var ctx = document.getElementById('categoryGraph').getContext('2d');
    var labels = categoryData.map(function(item) { return item.category; });
    var values = categoryData.map(function(item) { return parseFloat(item.total); });

    // Kolory zgodne z mockupem
    var colors = [
        '#00b894',  // zielony
        '#0984e3',  // niebieski
        '#6c5ce7',  // fioletowy
        '#fdcb6e',  // żółty/pomarańczowy
        '#636e72',  // szary
        '#e84393',  // różowy
        '#00cec9',  // turkusowy
        '#fab1a0'   // łososiowy
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Spending by Category',
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Funkcja do inicjalizacji wszystkich danych na starcie
function initializeCharts() {
    loadBudgetSummary();
    loadChartData();
    loadCategoryData();
    loadRecentTransactions();
    loadGroupMembers();
}

