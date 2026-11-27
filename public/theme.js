(() => {
    let theme = 'dark';
    try { theme = localStorage.getItem('kazuhadev-theme') || 'dark'; } catch {}
    if (theme !== 'light' && theme !== 'dark') theme = 'dark';
    document.documentElement.dataset.theme = theme;
})();