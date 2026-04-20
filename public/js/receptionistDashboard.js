(function () {
    var data = window.RECEPTIONIST_DASHBOARD_DATA || {};

    function normalizeSeries(series) {
        if (!series || !Array.isArray(series.values) || !Array.isArray(series.labels)) {
            return {
                labels: [],
                values: [],
                colors: []
            };
        }

        return {
            labels: series.labels,
            values: series.values.map(function (value) {
                return Number(value || 0);
            }),
            colors: Array.isArray(series.colors) ? series.colors : []
        };
    }

    function drawDonutChart(canvasId, series) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) {
            return;
        }

        var normalized = normalizeSeries(series);
        var hasAnyValue = normalized.values.some(function (value) {
            return value > 0;
        });

        var datasetValues = hasAnyValue
            ? normalized.values
            : [1];
        var datasetColors = hasAnyValue
            ? normalized.colors
            : ['#e8eef5'];
        var datasetLabels = hasAnyValue
            ? normalized.labels
            : ['No data'];

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: datasetLabels,
                datasets: [
                    {
                        data: datasetValues,
                        backgroundColor: datasetColors,
                        borderWidth: 0,
                        hoverOffset: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                animation: {
                    duration: 600
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: hasAnyValue
                    }
                }
            }
        });
    }

    drawDonutChart('rtStatusChart', data.status);
    drawDonutChart('rtTypeChart', data.types);
})();
