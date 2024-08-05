document.addEventListener("DOMContentLoaded", function() {
    var reportesData = document.getElementById('reportesChart').getAttribute('data-value');
    var vehiculosData = document.getElementById('vehiculosChart').getAttribute('data-value');
    var personalData = document.getElementById('personalChart').getAttribute('data-value');
    var solicitudesData = document.getElementById('solicitudesChart').getAttribute('data-value');
    var generalStats = JSON.parse(document.getElementById('generalStatsChart').getAttribute('data-values'));

    const createChart = (ctx, label, data, bgColor, borderColor) => {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [label],
                datasets: [{
                    label: `Cantidad de ${label}`,
                    data: [data],
                    backgroundColor: bgColor,
                    borderColor: borderColor,
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    };

    createChart(document.getElementById('reportesChart').getContext('2d'), 'Reportes', reportesData, 'rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)');
    createChart(document.getElementById('vehiculosChart').getContext('2d'), 'Vehículos', vehiculosData, 'rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)');
    createChart(document.getElementById('personalChart').getContext('2d'), 'Personal', personalData, 'rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 1)');
    createChart(document.getElementById('solicitudesChart').getContext('2d'), 'Solicitudes', solicitudesData, 'rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)');

    var ctxGeneral = document.getElementById('generalStatsChart').getContext('2d');
    var generalStatsChart = new Chart(ctxGeneral, {
        type: 'line',
        data: {
            labels: generalStats.materiales.map(item => item.nombre_pieza).concat(generalStats.servicios.map(item => item.tipo_servicio)),
            datasets: [
                {
                    label: 'Costos de Refacciones',
                    data: [generalStats.costos],
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Piezas en Mayor Cantidad',
                    data: generalStats.materiales.map(item => item.total),
                    backgroundColor: 'rgba(255, 206, 86, 0.8)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Servicios de Mantenimiento',
                    data: generalStats.servicios.map(item => item.total),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
