import Chart from 'chart.js/auto';

const css = getComputedStyle(document.documentElement);
const token = (name, fallback) => css.getPropertyValue(name).trim() || fallback;

// One hue (emerald) for magnitude. Grid and axes stay recessive.
const ink = token('--color-gray-900', '#10241c');
const muted = token('--color-gray-500', '#62736a');
const grid = token('--color-gray-200', '#dce4df');
const mark = token('--color-emerald-600', '#047857');
const markSoft = 'rgba(4, 120, 87, 0.12)';

Chart.defaults.font.family = token('--font-sans', 'system-ui, sans-serif');
Chart.defaults.font.size = 12;
Chart.defaults.color = muted;

const baht = (v) => '฿' + Number(v).toLocaleString('en-US', { maximumFractionDigits: 0 });
const bahtShort = (v) => '฿' + Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(v);

const tooltip = {
    backgroundColor: ink,
    titleColor: '#fff',
    bodyColor: '#fff',
    padding: 10,
    cornerRadius: 6,
    displayColors: false,
    callbacks: { label: (ctx) => baht(ctx.chart.options.indexAxis === 'y' ? ctx.parsed.x : ctx.parsed.y) },
};

const quietAxis = { grid: { display: false }, border: { color: grid } };
const moneyAxis = (axis) => ({
    beginAtZero: true,
    border: { display: false },
    grid: { color: grid },
    ticks: { callback: (v) => bahtShort(v), maxTicksLimit: 5 },
    ...(axis ? { axis } : {}),
});

const builders = {
    line: (labels, values) => ({
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: mark,
                backgroundColor: markSoft,
                borderWidth: 2,
                fill: true,
                cubicInterpolationMode: 'monotone',
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: mark,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
            }],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip },
            scales: { x: { ...quietAxis, ticks: { maxTicksLimit: 8, maxRotation: 0 } }, y: moneyAxis() },
        },
    }),
    bar: (labels, values) => ({
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: mark,
                hoverBackgroundColor: token('--color-emerald-700', '#065f46'),
                borderRadius: { topLeft: 4, topRight: 4 },
                borderSkipped: false,
                maxBarThickness: 32,
            }],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip },
            scales: { x: { ...quietAxis, ticks: { maxRotation: 0, autoSkipPadding: 12 } }, y: moneyAxis() },
        },
    }),
    hbar: (labels, values) => ({
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: mark,
                hoverBackgroundColor: token('--color-emerald-700', '#065f46'),
                borderRadius: { topRight: 4, bottomRight: 4 },
                borderSkipped: false,
                maxBarThickness: 22,
            }],
        },
        options: {
            indexAxis: 'y',
            interaction: { mode: 'index', intersect: false, axis: 'y' },
            plugins: { legend: { display: false }, tooltip },
            scales: {
                x: { beginAtZero: true, border: { display: false }, grid: { color: grid }, ticks: { callback: (v) => bahtShort(v), maxTicksLimit: 5 } },
                y: { ...quietAxis, grid: { display: false } },
            },
        },
    }),
};

document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
    const { type, labels, values } = JSON.parse(canvas.dataset.chart);
    const config = builders[type](labels, values);
    config.options = { responsive: true, maintainAspectRatio: false, animation: { duration: 400 }, ...config.options };
    new Chart(canvas, config);
});
