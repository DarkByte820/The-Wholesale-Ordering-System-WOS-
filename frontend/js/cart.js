function getCart() {
    return JSON.parse(localStorage.getItem('gwc_cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('gwc_cart', JSON.stringify(cart));
    updateCartCount();
}

function addToCart(productId, name, price, quantity) {
    quantity = quantity || 1;
    var cart = getCart();

    var existing = null;
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].id == productId) {
            existing = cart[i];
            break;
        }
    }

    if (existing) {
        existing.quantity += quantity;
    } else {
        cart.push({
            id: productId,
            name: name,
            price: parseFloat(price),
            quantity: quantity
        });
    }

    saveCart(cart);
    showAlert('Added to cart!', 'success');
}

function removeFromCart(index) {
    var cart = getCart();
    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        saveCart(cart);
        if (typeof displayCart === 'function') displayCart();
        showAlert('Item removed from cart', 'success');
    }
}

function updateCartItemQuantity(index, quantity) {
    var cart = getCart();
    if (cart[index]) {
        if (quantity <= 0) {
            cart.splice(index, 1);
        } else {
            cart[index].quantity = quantity;
        }
        saveCart(cart);
    }
}

function getCartTotal() {
    var cart = getCart();
    return cart.reduce(function (sum, item) {
        return sum + (item.price * item.quantity);
    }, 0);
}

function getCartItemCount() {
    var cart = getCart();
    return cart.reduce(function (sum, item) {
        return sum + item.quantity;
    }, 0);
}

function clearCart() {
    localStorage.removeItem('gwc_cart');
    updateCartCount();
}
