function showAlert(message, type) {
    showToast(message, type);
}

function isLoggedIn() {
    return !!localStorage.getItem('token');
}

function getCurrentUser() {
    try {
        return JSON.parse(localStorage.getItem('user') || 'null');
    } catch (e) {
        return null;
    }
}

function updateCartCount() {
    var badge = document.getElementById('cartCount');
    if (!badge) return;

    if (isLoggedIn()) {
        API.getCart().then(function (res) {
            if (res.success && res.data) {
                var items = Array.isArray(res.data) ? res.data : (res.data.items || []);
                var count = items.reduce(function (sum, item) { return sum + (item.Quantity || item.quantity || 0); }, 0);
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        }).catch(function () {
            var cart = JSON.parse(localStorage.getItem('gwc_cart') || '[]');
            var count = cart.reduce(function (sum, item) { return sum + (item.quantity || 0); }, 0);
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    } else {
        var cart = JSON.parse(localStorage.getItem('gwc_cart') || '[]');
        var count = cart.reduce(function (sum, item) { return sum + (item.quantity || 0); }, 0);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateWishlistCount() {
    var badge = document.getElementById('wishlistCount');
    if (!badge) return;
    var wishlist = JSON.parse(localStorage.getItem('gwc_wishlist') || '[]');
    badge.textContent = wishlist.length;
    badge.style.display = wishlist.length > 0 ? 'flex' : 'none';
}

function showLoading(containerId) {
    var el = document.getElementById(containerId);
    if (el) {
        el.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading...</p></div>';
    }
}

function hideLoading(containerId) {
    var el = document.getElementById(containerId);
    if (el) {
        var spinner = el.querySelector('.loading');
        if (spinner) spinner.remove();
    }
}

function updateHeaderAuth() {
    var accountText = document.getElementById('accountText');
    if (!accountText) return;
    if (isLoggedIn()) {
        var user = getCurrentUser();
        accountText.textContent = user ? (user.Name || 'Account') : 'Account';
    } else {
        accountText.textContent = 'Login';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    updateWishlistCount();
    updateHeaderAuth();
});
