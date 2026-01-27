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

document.addEventListener('DOMContentLoaded', function() {
    loadChartData();
    loadCategoryData();
});