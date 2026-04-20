(function () {
    var payload = window.TECHNICIAN_DASHBOARD_DATA || {};

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

    function drawWorkflowChart() {
        var canvas = document.getElementById('tdWorkflowChart');
        if (!canvas || !window.Chart) {
            return;
        }

        var series = normalizeSeries(payload.workflow);
        var hasValue = series.values.some(function (value) {
            return value > 0;
        });

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: hasValue ? series.labels : ['No active reports'],
                datasets: [
                    {
                        data: hasValue ? series.values : [1],
                        backgroundColor: hasValue ? series.colors : ['#e3eaf2'],
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
                    duration: 580
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: hasValue
                    }
                }
            }
        });
    }

    drawWorkflowChart();
})();
