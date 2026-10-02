const dashboardCharts = {};

function mountDashboardChart(id, options) {
    const el = document.querySelector(id);
    if (!el || typeof ApexCharts === 'undefined') return;

    if (dashboardCharts[id]) {
        dashboardCharts[id].destroy();
    }

    dashboardCharts[id] = new ApexCharts(el, options);
    dashboardCharts[id].render();
}

function drawChartTren30(data) {
    mountDashboardChart('#chartTren30', {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [
            { name: 'Hadir', data: data.map(row => Number(row.hadir || 0)) },
            { name: 'Luar', data: data.map(row => Number(row.luar || 0)) },
            { name: 'Tidak Hadir', data: data.map(row => Number(row.tidak || 0)) }
        ],
        xaxis: { categories: data.map(row => row.tanggal) },
        stroke: { curve: 'smooth', width: 3 },
        yaxis: { max: 100, labels: { formatter: value => `${Math.round(value)}%` } }
    });
}

function drawChartDistribusi(data) {
    mountDashboardChart('#chartDistribusi', {
        chart: { type: 'donut', height: 320 },
        series: data.map(row => Number(row.jumlah || 0)),
        labels: data.map(row => row.label || row.status),
        legend: { position: 'bottom' }
    });
}

function drawChartTop10(data) {
    mountDashboardChart('#chartTop10', {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Tidak Hadir', data: data.map(row => Number(row.jumlah || 0)) }],
        xaxis: { categories: data.map(row => row.nama) },
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } }
    });
}

function drawChartAktivitas(data) {
    mountDashboardChart('#chartAktivitas', {
        chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
        series: [
            { name: 'Approved', data: data.map(row => Number(row.approved || 0)) },
            { name: 'Pending', data: data.map(row => Number(row.pending || 0)) },
            { name: 'Rejected', data: data.map(row => Number(row.rejected || 0)) }
        ],
        xaxis: { categories: data.map(row => row.tanggal) },
        plotOptions: { bar: { borderRadius: 4 } }
    });
}

function drawChartPerbandingan(data) {
    mountDashboardChart('#chartPerbandingan', {
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        series: [
            { name: 'Hadir', data: data.map(row => Number(row.hadir || 0)) },
            { name: 'Luar', data: data.map(row => Number(row.luar || 0)) },
            { name: 'Tidak Hadir', data: data.map(row => Number(row.tidak || 0)) }
        ],
        xaxis: { categories: data.map(row => row.nama) },
        yaxis: { max: 100, labels: { formatter: value => `${Math.round(value)}%` } },
        plotOptions: { bar: { borderRadius: 4 } }
    });
}
