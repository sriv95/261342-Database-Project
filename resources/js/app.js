// Chart.js is only needed on the admin dashboard, so load it on demand.
if (document.querySelector('canvas[data-chart]')) {
    import('./charts');
}
