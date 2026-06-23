import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler);

window.initRevenueChart = function (labels, data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                y: { display: false, beginAtZero: true },
            },
        },
    });
};
