function getWishlist() {
    return JSON.parse(localStorage.getItem('gwc_wishlist') || '[]');
}

function saveWishlist(wishlist) {
    localStorage.setItem('gwc_wishlist', JSON.stringify(wishlist));
    updateWishlistCount();
}

function toggleWishlist(id, name, price, category) {
    var wishlist = getWishlist();
    var idx = -1;
    for (var i = 0; i < wishlist.length; i++) {
        if (wishlist[i].id == id) {
            idx = i;
            break;
        }
    }

    if (idx >= 0) {
        wishlist.splice(idx, 1);
        saveWishlist(wishlist);
        showAlert('Removed from wishlist', 'success');
        return false;
    } else {
        wishlist.push({
            id: id,
            name: name || 'Product',
            price: parseFloat(price) || 0,
            category: category || ''
        });
        saveWishlist(wishlist);
        showAlert('Added to wishlist!', 'success');
        return true;
    }
}

function removeFromWishlist(id) {
    var wishlist = getWishlist();
    var filtered = wishlist.filter(function (item) { return item.id != id; });
    saveWishlist(filtered);
    showAlert('Removed from wishlist', 'success');
}

function isInWishlist(id) {
    var wishlist = getWishlist();
    for (var i = 0; i < wishlist.length; i++) {
        if (wishlist[i].id == id) return true;
    }
    return false;
}
