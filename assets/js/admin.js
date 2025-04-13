document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận trước khi thực hiện các hành động quan trọng
    const confirmActions = document.querySelectorAll('[data-confirm]');
    confirmActions.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Xử lý form tìm kiếm trong admin
    const adminSearch = document.querySelector('.admin-search');
    if (adminSearch) {
        adminSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Khởi tạo các biểu đồ thống kê nếu có
    const statsCharts = document.querySelectorAll('.stats-chart');
    if (statsCharts.length && typeof Chart !== 'undefined') {
        statsCharts.forEach(initChart);
    }
});

function initChart(canvas) {
    // Khởi tạo biểu đồ thống kê
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: canvas.dataset.type || 'line',
        data: JSON.parse(canvas.dataset.chartData || '{}'),
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}
