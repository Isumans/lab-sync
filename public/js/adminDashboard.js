(function () {
    var payload = window.ADMIN_DASHBOARD_DATA || {};

    function numberSeries(values) {
        if (!Array.isArray(values)) {
            return [];
        }

        return values.map(function (value) {
            return Number(value || 0);
        });
    }

    function drawStatusDonut() {
        var canvas = document.getElementById('adStatusChart');
        if (!canvas || !window.Chart) {
            return;
        }

        var status = payload.status || {};
        var values = numberSeries(status.values);
        var labels = Array.isArray(status.labels) ? status.labels : [];
        var colors = Array.isArray(status.colors) ? status.colors : [];

        var hasData = values.some(function (value) {
            return value > 0;
        });

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: hasData ? labels : ['No data'],
                datasets: [
                    {
                        data: hasData ? values : [1],
                        backgroundColor: hasData ? colors : ['#e8eef5'],
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
                        enabled: hasData
                    }
                }
            }
        });
    }

    function drawRevenueBars() {
        var canvas = document.getElementById('adRevenueChart');
        if (!canvas || !window.Chart) {
            return;
        }

        var revenue = payload.revenue || {};
        var labels = Array.isArray(revenue.labels) ? revenue.labels.slice() : [];
        var serviceValues = numberSeries(revenue.service);
        var retailValues = numberSeries(revenue.retail);

        if (labels.length === 0 || serviceValues.length === 0) {
            labels = ['No data'];
            serviceValues.length = 0;
            serviceValues.push(0);
            retailValues.length = 0;
            retailValues.push(0);
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Service Revenue',
                        data: serviceValues,
                        backgroundColor: 'rgba(61, 189, 236, 0.82)',
                        borderColor: '#3DBDEC',
                        borderWidth: 1,
                        borderRadius: 5,
                        maxBarThickness: 34
                    },
                    {
                        label: 'Retail Revenue',
                        data: retailValues,
                        backgroundColor: 'rgba(201, 210, 221, 0.88)',
                        borderColor: '#cfd7e1',
                        borderWidth: 1,
                        borderRadius: 5,
                        maxBarThickness: 34
                    }
                ]
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
                        ticks: {
                            color: '#7890a6',
                            font: {
                                size: 11
                            },
                            callback: function (value) {
                                var numeric = Number(value || 0);
                                if (numeric >= 1000000) {
                                    return (numeric / 1000000).toFixed(1) + 'M';
                                }
                                if (numeric >= 1000) {
                                    return (numeric / 1000).toFixed(0) + 'K';
                                }
                                return numeric;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#7890a6',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    drawRevenueBars();
    drawStatusDonut();
})();
