document.addEventListener('DOMContentLoaded', function() {
    // chartData jest przekazane przez PHP jako globalna zmienna JS
    if (typeof chartData !== 'undefined') {
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
                    backgroundColor: 'rgb(255, 255, 255)',
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
    } else {
        document.getElementById('trendsGraph').innerHTML = 'Brak danych do wyświetlenia.';
    }
});