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
            document.getElementById('transactionList').innerHTML = '<li>Brak transakcji.</li>';
        }
    } catch (error) {
        console.error('Błąd przy ładowaniu transakcji:', error);
        document.getElementById('transactionList').innerHTML = '<li>Błąd przy ładowaniu danych.</li>';
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
            document.getElementById('membersList').innerHTML = '<li>Brak członków grupy.</li>';
        }
    } catch (error) {
        console.error('Błąd przy ładowaniu członków grupy:', error);
        document.getElementById('membersList').innerHTML = '<li>Błąd przy ładowaniu danych.</li>';
    }
}

// Funkcja do renderowania listy transakcji
function renderTransactionList(transactions) {
    const list = document.getElementById('transactionList');
    list.innerHTML = '';

    transactions.forEach(transaction => {
        const li = document.createElement('li');
        li.className = 'transaction-item';
        li.innerHTML = `
            <div class="transaction-info">
                <span class="transaction-description">${transaction.description}</span>
                <span class="transaction-category">${transaction.category}</span>
            </div>
            <div class="transaction-amount">${transaction.amount.toFixed(2)} PLN</div>
            <div class="transaction-date">${transaction.date}</div>
        `;
        list.appendChild(li);
    });
}

// Funkcja do renderowania listy członków grupy
function renderMembersList(members) {
    const list = document.getElementById('membersList');
    list.innerHTML = '';

    members.forEach(member => {
        const li = document.createElement('li');
        li.className = `member-item ${member.balance >= 0 ? 'positive' : 'negative'}`;
        const balanceClass = member.balance >= 0 ? 'balance-positive' : 'balance-negative';
        const balanceSymbol = member.balance >= 0 ? '+' : '';
        
        li.innerHTML = `
            <div class="member-info">
                <span class="member-name">${member.name}</span>
                <span class="member-email">${member.email}</span>
            </div>
            <div class="member-balance ${balanceClass}">
                ${balanceSymbol}${member.balance.toFixed(2)} PLN
            </div>
        `;
        list.appendChild(li);
    });
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
    const ctx = document.getElementById('trendsGraph').getContext('2d');
    const labels = chartData.map(item => item.month);
    const values = chartData.map(item => parseFloat(item.total));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Suma transakcji miesięcznie',
                data: values,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function renderPieChart(categoryData) {
    const ctx = document.getElementById('categoryGraph').getContext('2d');
    const labels = categoryData.map(item => item.category);
    const values = categoryData.map(item => parseFloat(item.total));

    const colors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(201, 203, 207, 0.7)',
        'rgba(255, 99, 132, 0.7)',
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Wydatki po kategoriach',
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Funkcja do inicjalizacji wszystkich danych na starcie
function initializeCharts() {
    loadChartData();
    loadCategoryData();
    loadRecentTransactions();
    loadGroupMembers();
}

