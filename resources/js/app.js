import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

const charts = new Map();
let queuedFrame = null;

const palette = () => {
    const dark = document.documentElement.classList.contains('dark');

    return {
        text: dark ? '#cbd5e1' : '#475569',
        grid: dark ? 'rgba(148, 163, 184, 0.16)' : 'rgba(148, 163, 184, 0.2)',
        border: dark ? '#0f172a' : '#ffffff',
    };
};

const buildOptions = (type) => {
    const colors = palette();
    const cartesian = type === 'bar' || type === 'line';

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    color: colors.text,
                    boxWidth: 12,
                    boxHeight: 12,
                    usePointStyle: true,
                    padding: 18,
                },
            },
            tooltip: {
                backgroundColor: '#0f172a',
                titleColor: '#f8fafc',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 12,
                displayColors: true,
            },
        },
        scales: cartesian ? {
            x: {
                ticks: {
                    color: colors.text,
                },
                grid: {
                    display: false,
                    drawBorder: false,
                },
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: colors.text,
                },
                grid: {
                    color: colors.grid,
                    drawBorder: false,
                },
            },
        } : undefined,
    };
};

const renderChart = (element) => {
    const canvas = element.querySelector('canvas');

    if (!canvas) {
        return;
    }

    const rawConfig = element.dataset.chartConfig || '{}';
    const chartId = element.dataset.chartId || (window.crypto?.randomUUID?.() || `chart-${Math.random().toString(36).slice(2)}`);

    if (element.dataset.chartRenderedConfig === rawConfig && charts.has(chartId)) {
        return;
    }

    const config = JSON.parse(rawConfig);

    if (charts.has(chartId)) {
        charts.get(chartId)?.destroy();
    }

    const chart = new Chart(canvas, {
        type: config.type || 'bar',
        data: config.data || { labels: [], datasets: [] },
        options: {
            ...buildOptions(config.type || 'bar'),
            ...(config.options || {}),
        },
    });

    charts.set(chartId, chart);
    element.dataset.chartRenderedConfig = rawConfig;
};

const initCharts = () => {
    document.querySelectorAll('[data-chart-root]').forEach(renderChart);
};

const scheduleChartsInit = () => {
    if (queuedFrame !== null) {
        cancelAnimationFrame(queuedFrame);
    }

    queuedFrame = requestAnimationFrame(() => {
        initCharts();
        queuedFrame = null;
    });
};

document.addEventListener('DOMContentLoaded', scheduleChartsInit);
document.addEventListener('livewire:navigated', scheduleChartsInit);
document.addEventListener('livewire:update', scheduleChartsInit);

const observer = new MutationObserver(() => {
    scheduleChartsInit();
});

observer.observe(document.documentElement, {
    childList: true,
    subtree: true,
});
