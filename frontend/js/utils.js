function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return 'GHS ' + num.toFixed(2);
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDateTime(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function truncateText(text, maxLen) {
    if (!text) return '';
    if (text.length <= maxLen) return text;
    return text.substring(0, maxLen) + '...';
}

function generateStars(rating) {
    const r = Math.round(rating || 0);
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= r ? '\u2605' : '\u2606';
    }
    return stars;
}

function getDiscountPercent(original, discounted) {
    if (!original || !discounted || original <= discounted) return 0;
    return Math.round((1 - discounted / original) * 100);
}

function parseQueryString() {
    const params = new URLSearchParams(window.location.search);
    const result = {};
    for (const [key, value] of params) {
        result[key] = value;
    }
    return result;
}

function getQueryParam(key) {
    return new URLSearchParams(window.location.search).get(key);
}

function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function skeletonLoader(count) {
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `
            <div class="product-card skeleton-card">
                <div class="skeleton skeleton-image"></div>
                <div class="skeleton-card-body">
                    <div class="skeleton skeleton-text skeleton-text-lg"></div>
                    <div class="skeleton skeleton-text skeleton-text-sm"></div>
                    <div class="skeleton skeleton-text skeleton-text-md"></div>
                    <div class="skeleton skeleton-text skeleton-text-sm"></div>
                </div>
            </div>`;
    }
    return html;
}

function showToast(message, type) {
    type = type || 'info';
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    toast.innerHTML = '<span>' + message + '</span><button class="toast-close" aria-label="Close">&times;</button>';
    document.body.appendChild(toast);

    toast.querySelector('.toast-close').addEventListener('click', function () {
        toast.remove();
    });

    setTimeout(function () {
        if (toast.parentNode) toast.remove();
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
